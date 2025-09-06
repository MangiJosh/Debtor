<?php
/**
 * Dummy Email Sender
 * Sends a test email to verify email functionality
 */

// Enable error reporting
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

// Email configuration (using Gmail SMTP)
$email_config = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'your_email@gmail.com', // Replace with your Gmail
    'smtp_password' => 'your_app_password', // Replace with your Gmail App Password
    'from_email' => 'noreply@legalpartners.co.za',
    'from_name' => 'Legal Partners Debt Collection'
];

// Test email data
$test_email = 'tshimangadzot@gmail.com';
$test_debtor = [
    'name' => 'Tshimanga Dzot',
    'case_ref' => 'LP-2024-TEST',
    'amount' => 'R1,500.00',
    'email' => $test_email
];

/**
 * Send test email using PHPMailer
 */
function sendTestEmail($to_email, $debtor_data, $email_config) {
    try {
        // Check if PHPMailer is available
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            // Fallback to basic mail() function
            return sendBasicEmail($to_email, $debtor_data);
        }
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $email_config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $email_config['smtp_username'];
        $mail->Password = $email_config['smtp_password'];
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $email_config['smtp_port'];
        
        // Recipients
        $mail->setFrom($email_config['from_email'], $email_config['from_name']);
        $mail->addAddress($to_email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Test Email - Payment Reminder - Case Reference: {$debtor_data['case_ref']}";
        
        $html_content = createTestEmailHTML($debtor_data);
        $mail->Body = $html_content;
        $mail->AltBody = strip_tags($html_content);
        
        $mail->send();
        
        return ['success' => true, 'message' => 'Test email sent successfully using PHPMailer'];
        
    } catch (Exception $e) {
        // Fallback to basic mail function
        return sendBasicEmail($to_email, $debtor_data);
    }
}

/**
 * Fallback: Send basic email using PHP mail() function
 */
function sendBasicEmail($to_email, $debtor_data) {
    $subject = "Test Email - Payment Reminder - Case Reference: {$debtor_data['case_ref']}";
    $message = createTestEmailText($debtor_data);
    $headers = [
        'From: Legal Partners Debt Collection <noreply@legalpartners.co.za>',
        'Reply-To: collections@legalpartners.co.za',
        'Content-Type: text/html; charset=UTF-8',
        'X-Mailer: Legal Partners Debt Collection System'
    ];
    
    $result = mail($to_email, $subject, $message, implode("\r\n", $headers));
    
    if ($result) {
        return ['success' => true, 'message' => 'Test email sent successfully using PHP mail() function'];
    } else {
        return ['success' => false, 'error' => 'Failed to send email using PHP mail() function'];
    }
}

/**
 * Create HTML email content
 */
function createTestEmailHTML($debtor) {
    $payment_link = "https://mangijosh.github.io/Debtor/#pay-test";
    
    return "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Test Payment Reminder</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #667eea; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { padding: 30px; background: #f9f9f9; border-radius: 0 0 8px 8px; }
            .amount { font-size: 28px; font-weight: bold; color: #e74c3c; text-align: center; margin: 20px 0; }
            .button { display: inline-block; background: #27ae60; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
            .security { background: #e8f4fd; padding: 20px; border-left: 4px solid #3498db; margin: 20px 0; border-radius: 4px; }
            .footer { text-align: center; font-size: 12px; color: #666; margin-top: 30px; }
            .test-notice { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 4px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Legal Partners Debt Collection</h2>
                <p>Test Payment Reminder</p>
            </div>
            
            <div class='content'>
                <div class='test-notice'>
                    <strong>🧪 TEST EMAIL:</strong> This is a test email to verify email functionality. No actual payment is required.
                </div>
                
                <h3>Outstanding Debt Payment Required</h3>
                
                <div style='background: #ffe6e6; border-left: 4px solid #e74c3c; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                    <p><strong>Dear {$debtor['name']},</strong></p>
                    <p>This is a test reminder regarding your outstanding debt. This is for testing purposes only.</p>
                </div>
                
                <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;'>
                    <div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>
                        <h4>Account Details</h4>
                        <p><strong>Debtor Name:</strong> {$debtor['name']}</p>
                        <p><strong>Case Reference:</strong> {$debtor['case_ref']}</p>
                        <p><strong>Email:</strong> {$debtor['email']}</p>
                    </div>
                    
                    <div style='background: #fff3cd; padding: 15px; border-radius: 5px; border: 1px solid #ffeaa7;'>
                        <h4>Test Amount</h4>
                        <div class='amount'>{$debtor['amount']}</div>
                        <p style='font-size: 14px; color: #666;'>Test Case - No Payment Required</p>
                    </div>
                </div>
                
                <div style='background: #e8f4fd; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: center;'>
                    <h4>🚀 Test Payment Link</h4>
                    <p>This is a test payment link for demonstration purposes only.</p>
                    
                    <a href='{$payment_link}' class='button'>💳 Test Payment Link - {$debtor['amount']}</a>
                </div>
                
                <div class='security'>
                    <h4>🔒 Email Verification</h4>
                    <ul>
                        <li>This is a test email from Legal Partners Debt Collection System</li>
                        <li>Email sent to: {$debtor['email']}</li>
                        <li>Test case reference: {$debtor['case_ref']}</li>
                        <li>No actual payment is required for this test</li>
                    </ul>
                </div>
                
                <p><strong>Note:</strong> This is a test email to verify email functionality. No actual debt collection is taking place.</p>
                
                <p>Best regards,<br>
                Legal Partners Debt Collection Team<br>
                <em>Test Email System</em></p>
            </div>
            
            <div class='footer'>
                <p>Legal Partners | Debt Collection Services</p>
                <p>Email: collections@legalpartners.co.za | Phone: 011 123 4567</p>
                <p>This test email was sent to {$debtor['email']} regarding test case {$debtor['case_ref']}</p>
            </div>
        </div>
    </body>
    </html>";
}

/**
 * Create plain text email content
 */
function createTestEmailText($debtor) {
    $payment_link = "https://mangijosh.github.io/Debtor/#pay-test";
    
    return "
TEST EMAIL - Legal Partners Debt Collection

Dear {$debtor['name']},

This is a test email to verify email functionality. No actual payment is required.

Account Details:
- Debtor Name: {$debtor['name']}
- Case Reference: {$debtor['case_ref']}
- Email: {$debtor['email']}
- Test Amount: {$debtor['amount']}

Test Payment Link: {$payment_link}

Note: This is a test email for verification purposes only. No actual debt collection is taking place.

Best regards,
Legal Partners Debt Collection Team
Test Email System

---
Legal Partners | Debt Collection Services
Email: collections@legalpartners.co.za | Phone: 011 123 4567
This test email was sent to {$debtor['email']} regarding test case {$debtor['case_ref']}
";
}

// Main execution
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $email_type = $input['email_type'] ?? 'test';
        
        $result = sendTestEmail($test_email, $test_debtor, $email_config);
        
        if ($result['success']) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'email_sent_to' => $test_email,
                    'debtor_name' => $test_debtor['name'],
                    'case_ref' => $test_debtor['case_ref'],
                    'test_amount' => $test_debtor['amount']
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $result['error']
            ]);
        }
        
    } else {
        // GET request - show test page
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Dummy Email Test</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .button { background: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; margin: 10px 5px; }
                .button:hover { background: #0056b3; }
                .result { margin: 20px 0; padding: 15px; border-radius: 5px; }
                .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
                .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
                .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🧪 Dummy Email Test</h1>
                <p>This tool sends a test email to <strong>tshimangadzot@gmail.com</strong> to verify email functionality.</p>
                
                <div class="info">
                    <h3>Test Details:</h3>
                    <ul>
                        <li><strong>Recipient:</strong> tshimangadzot@gmail.com</li>
                        <li><strong>Test Debtor:</strong> Tshimanga Dzot</li>
                        <li><strong>Case Reference:</strong> LP-2024-TEST</li>
                        <li><strong>Test Amount:</strong> R1,500.00</li>
                    </ul>
                </div>
                
                <button class="button" onclick="sendTestEmail()">📧 Send Test Email</button>
                <button class="button" onclick="location.reload()">🔄 Refresh</button>
                
                <div id="result"></div>
            </div>
            
            <script>
                async function sendTestEmail() {
                    const resultDiv = document.getElementById('result');
                    resultDiv.innerHTML = '<div class="info">Sending test email...</div>';
                    
                    try {
                        const response = await fetch('send_dummy_email.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                email_type: 'test'
                            })
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            resultDiv.innerHTML = `
                                <div class="success">
                                    <h3>✅ Test Email Sent Successfully!</h3>
                                    <p><strong>Message:</strong> ${result.message}</p>
                                    <p><strong>Sent to:</strong> ${result.data.email_sent_to}</p>
                                    <p><strong>Debtor:</strong> ${result.data.debtor_name}</p>
                                    <p><strong>Case Ref:</strong> ${result.data.case_ref}</p>
                                </div>
                            `;
                        } else {
                            resultDiv.innerHTML = `
                                <div class="error">
                                    <h3>❌ Error Sending Email</h3>
                                    <p><strong>Error:</strong> ${result.error}</p>
                                </div>
                            `;
                        }
                    } catch (error) {
                        resultDiv.innerHTML = `
                            <div class="error">
                                <h3>❌ Network Error</h3>
                                <p><strong>Error:</strong> ${error.message}</p>
                            </div>
                        `;
                    }
                }
            </script>
        </body>
        </html>
        <?php
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
