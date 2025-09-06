<?php
/**
 * Debt Collection Email Handler
 * Handles sending personalized email reminders to debtors
 * 
 * @author Legal Partners
 * @version 1.0
 */

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type for JSON responses
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
$db_config = [
    'host' => 'localhost',
    'dbname' => 'debt_collection',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset' => 'utf8mb4'
];

// Email configuration
$email_config = [
    'smtp_host' => 'smtp.gmail.com', // or your SMTP server
    'smtp_port' => 587,
    'smtp_username' => 'your_email@legalpartners.co.za',
    'smtp_password' => 'your_app_password',
    'from_email' => 'noreply@legalpartners.co.za',
    'from_name' => 'Legal Partners Debt Collection',
    'reply_to' => 'collections@legalpartners.co.za'
];

// Security configuration
$security_config = [
    'api_key' => 'your_secure_api_key_here',
    'rate_limit' => 10, // emails per minute per IP
    'max_retries' => 3,
    'token_expiry' => 3600 // 1 hour
];

/**
 * Database connection class
 */
class Database {
    private $pdo;
    
    public function __construct($config) {
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

/**
 * Email handler class
 */
class EmailHandler {
    private $db;
    private $email_config;
    private $security_config;
    
    public function __construct($db, $email_config, $security_config) {
        $this->db = $db;
        $this->email_config = $email_config;
        $this->security_config = $security_config;
    }
    
    /**
     * Send email reminder to debtor
     */
    public function sendReminder($debtor_id, $email_type = 'reminder') {
        try {
            // Validate API key
            if (!$this->validateApiKey()) {
                return $this->errorResponse('Invalid API key', 401);
            }
            
            // Check rate limiting
            if (!$this->checkRateLimit()) {
                return $this->errorResponse('Rate limit exceeded', 429);
            }
            
            // Get debtor information
            $debtor = $this->getDebtorInfo($debtor_id);
            if (!$debtor) {
                return $this->errorResponse('Debtor not found', 404);
            }
            
            // Generate secure payment link
            $payment_link = $this->generateSecurePaymentLink($debtor_id);
            
            // Create email content
            $email_content = $this->createEmailContent($debtor, $email_type, $payment_link);
            
            // Send email
            $result = $this->sendEmail($debtor['email'], $email_content);
            
            if ($result['success']) {
                // Log email sent
                $this->logEmailSent($debtor_id, $email_type, $payment_link);
                
                return $this->successResponse([
                    'message' => 'Email sent successfully',
                    'debtor_id' => $debtor_id,
                    'email' => $debtor['email'],
                    'payment_link' => $payment_link
                ]);
            } else {
                return $this->errorResponse('Failed to send email: ' . $result['error'], 500);
            }
            
        } catch (Exception $e) {
            return $this->errorResponse('Server error: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get debtor information from database
     */
    private function getDebtorInfo($debtor_id) {
        $stmt = $this->db->prepare("
            SELECT 
                id, name, email, phone, case_ref, amount_due, 
                original_creditor, debt_summary, status, last_payment
            FROM debtors 
            WHERE id = ? AND status != 'deleted'
        ");
        $stmt->execute([$debtor_id]);
        return $stmt->fetch();
    }
    
    /**
     * Generate secure payment link
     */
    private function generateSecurePaymentLink($debtor_id) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + $this->security_config['token_expiry'];
        $signature = hash_hmac('sha256', $debtor_id . $token . $expiry, $this->security_config['api_key']);
        
        // Store token in database
        $stmt = $this->db->prepare("
            INSERT INTO payment_tokens (debtor_id, token, expires_at, signature, created_at) 
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            token = VALUES(token), 
            expires_at = VALUES(expires_at), 
            signature = VALUES(signature),
            created_at = NOW()
        ");
        $stmt->execute([$debtor_id, $token, date('Y-m-d H:i:s', $expiry), $signature]);
        
        $base_url = $this->getBaseUrl();
        return $base_url . "/payment.php?debtor_id={$debtor_id}&token={$token}&exp={$expiry}&sig={$signature}";
    }
    
    /**
     * Create email content based on debtor and email type
     */
    private function createEmailContent($debtor, $email_type, $payment_link) {
        $creditor = $this->extractCreditorFromSummary($debtor['debt_summary']);
        
        switch ($email_type) {
            case 'reminder':
                return $this->createReminderEmail($debtor, $creditor, $payment_link);
            case 'final_notice':
                return $this->createFinalNoticeEmail($debtor, $creditor, $payment_link);
            case 'arrangement':
                return $this->createArrangementEmail($debtor, $creditor, $payment_link);
            default:
                return $this->createReminderEmail($debtor, $creditor, $payment_link);
        }
    }
    
    /**
     * Create reminder email content
     */
    private function createReminderEmail($debtor, $creditor, $payment_link) {
        $subject = "Payment Reminder - Case Reference: {$debtor['case_ref']}";
        
        $html = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Payment Reminder</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #667eea; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .amount { font-size: 24px; font-weight: bold; color: #e74c3c; }
                .button { display: inline-block; background: #27ae60; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                .security { background: #e8f4fd; padding: 15px; border-left: 4px solid #3498db; margin: 20px 0; }
                .footer { text-align: center; font-size: 12px; color: #666; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Legal Partners Debt Collection</h2>
                    <p>Payment Reminder</p>
                </div>
                
                <div class='content'>
                    <h3>Outstanding Debt Payment Required</h3>
                    
                    <div style='background: #ffe6e6; border-left: 4px solid #e74c3c; padding: 15px; margin: 20px 0;'>
                        <p><strong>Dear {$debtor['name']},</strong></p>
                        <p>This is a formal notice regarding your outstanding debt. Immediate payment is required to avoid further legal action.</p>
                    </div>
                    
                    <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;'>
                        <div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>
                            <h4>Account Details</h4>
                            <p><strong>Debtor Name:</strong> {$debtor['name']}</p>
                            <p><strong>Case Reference:</strong> {$debtor['case_ref']}</p>
                            <p><strong>Original Creditor:</strong> {$creditor}</p>
                        </div>
                        
                        <div style='background: #fff3cd; padding: 15px; border-radius: 5px; border: 1px solid #ffeaa7;'>
                            <h4>Amount Due</h4>
                            <div class='amount'>R" . number_format($debtor['amount_due'], 2) . "</div>
                            <p style='font-size: 14px; color: #666;'>Due Date: Overdue</p>
                        </div>
                    </div>
                    
                    <div style='background: #e8f4fd; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                        <h4>🚀 New: Pay Online Instantly!</h4>
                        <p>We've made it easier for you to settle your debt. No more bank transfers or waiting periods - pay securely online in just a few clicks.</p>
                        
                        <a href='{$payment_link}' class='button'>💳 Pay Now - R" . number_format($debtor['amount_due'], 2) . "</a>
                    </div>
                    
                    <div class='security'>
                        <h4>🔒 How to Verify This Email is Legitimate:</h4>
                        <ul>
                            <li>Check the sender's email address ends with @legalpartners.co.za</li>
                            <li>Verify our website: https://legalpartners.co.za</li>
                            <li>Call us directly: 011 123 4567</li>
                            <li>Never pay to personal accounts or unknown platforms</li>
                        </ul>
                    </div>
                    
                    <p><strong>Important:</strong> This payment link is unique to you and expires in 24 hours for security reasons.</p>
                    
                    <p>If you have any questions or need to discuss payment arrangements, please contact us immediately.</p>
                    
                    <p>Best regards,<br>
                    Legal Partners Debt Collection Team</p>
                </div>
                
                <div class='footer'>
                    <p>Legal Partners | Debt Collection Services</p>
                    <p>Email: collections@legalpartners.co.za | Phone: 011 123 4567</p>
                    <p>This email was sent to {$debtor['email']} regarding case {$debtor['case_ref']}</p>
                </div>
            </div>
        </body>
        </html>";
        
        return [
            'subject' => $subject,
            'html' => $html,
            'text' => strip_tags($html)
        ];
    }
    
    /**
     * Extract creditor name from debt summary
     */
    private function extractCreditorFromSummary($summary) {
        $creditor_map = [
            'Vodacom' => 'Vodacom',
            'Standard Bank' => 'Standard Bank',
            'FNB' => 'FNB',
            'Edgars' => 'Edgars',
            'WesBank' => 'WesBank',
            'City of Johannesburg' => 'City of Johannesburg',
            'Discovery Health' => 'Discovery Health',
            'Virgin Active' => 'Virgin Active'
        ];
        
        foreach ($creditor_map as $key => $value) {
            if (strpos($summary, $key) !== false) {
                return $value;
            }
        }
        
        return 'Original Creditor';
    }
    
    /**
     * Send email using PHPMailer
     */
    private function sendEmail($to_email, $content) {
        try {
            // Include PHPMailer (you'll need to install it via Composer)
            require_once 'vendor/autoload.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->email_config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->email_config['smtp_username'];
            $mail->Password = $this->email_config['smtp_password'];
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->email_config['smtp_port'];
            
            // Recipients
            $mail->setFrom($this->email_config['from_email'], $this->email_config['from_name']);
            $mail->addAddress($to_email);
            $mail->addReplyTo($this->email_config['reply_to']);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $content['subject'];
            $mail->Body = $content['html'];
            $mail->AltBody = $content['text'];
            
            // Security headers
            $mail->addCustomHeader('X-Mailer', 'Legal Partners Debt Collection System');
            $mail->addCustomHeader('X-Priority', '1');
            $mail->addCustomHeader('X-MSMail-Priority', 'High');
            
            $mail->send();
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Log email sent to database
     */
    private function logEmailSent($debtor_id, $email_type, $payment_link) {
        $stmt = $this->db->prepare("
            INSERT INTO email_logs (debtor_id, email_type, payment_link, sent_at, ip_address) 
            VALUES (?, ?, ?, NOW(), ?)
        ");
        $stmt->execute([$debtor_id, $email_type, $payment_link, $_SERVER['REMOTE_ADDR']]);
    }
    
    /**
     * Validate API key
     */
    private function validateApiKey() {
        $api_key = $_SERVER['HTTP_X_API_KEY'] ?? $_POST['api_key'] ?? '';
        return hash_equals($this->security_config['api_key'], $api_key);
    }
    
    /**
     * Check rate limiting
     */
    private function checkRateLimit() {
        $ip = $_SERVER['REMOTE_ADDR'];
        $minute_ago = date('Y-m-d H:i:s', time() - 60);
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM email_logs 
            WHERE ip_address = ? AND sent_at > ?
        ");
        $stmt->execute([$ip, $minute_ago]);
        $result = $stmt->fetch();
        
        return $result['count'] < $this->security_config['rate_limit'];
    }
    
    /**
     * Get base URL
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['SCRIPT_NAME']);
        return $protocol . '://' . $host . $path;
    }
    
    /**
     * Success response
     */
    private function successResponse($data) {
        http_response_code(200);
        return json_encode(['success' => true, 'data' => $data]);
    }
    
    /**
     * Error response
     */
    private function errorResponse($message, $code = 400) {
        http_response_code($code);
        return json_encode(['success' => false, 'error' => $message]);
    }
}

// Main execution
try {
    // Initialize database connection
    $database = new Database($db_config);
    $db = $database->getConnection();
    
    // Initialize email handler
    $emailHandler = new EmailHandler($db, $email_config, $security_config);
    
    // Handle different request methods
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $debtor_id = $input['debtor_id'] ?? null;
        $email_type = $input['email_type'] ?? 'reminder';
        
        if (!$debtor_id) {
            echo $emailHandler->errorResponse('Debtor ID is required', 400);
            exit;
        }
        
        $result = $emailHandler->sendReminder($debtor_id, $email_type);
        echo $result;
        
    } else {
        echo $emailHandler->errorResponse('Method not allowed', 405);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>
