<?php
/**
 * Mock Email API for testing purposes
 * This simulates the email_handler.php response for development
 */

// Set content type for JSON responses
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simulate some processing time
sleep(1);

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

// Mock debtor data
$mock_debtors = [
    1 => ['name' => 'Sipho Mthembu', 'email' => 'sipho.mthembu@example.com', 'amount' => 'R171.88'],
    2 => ['name' => 'Sarah Smith', 'email' => 'sarah.smith@example.com', 'amount' => 'R845.32'],
    3 => ['name' => 'Mike Johnson', 'email' => 'mike.johnson@example.com', 'amount' => 'R1,234.56'],
    4 => ['name' => 'Lindiwe Khumalo', 'email' => 'lindiwe.khumalo@example.com', 'amount' => 'R2,100.00'],
    5 => ['name' => 'Thabo Mokoena', 'email' => 'thabo.mokoena@example.com', 'amount' => 'R567.89'],
    6 => ['name' => 'Nomsa Dlamini', 'email' => 'nomsa.dlamini@example.com', 'amount' => 'R890.12'],
    7 => ['name' => 'John Doe', 'email' => 'john.doe@example.com', 'amount' => 'R1,500.00'],
    8 => ['name' => 'Jane Smith', 'email' => 'jane.smith@example.com', 'amount' => 'R312.40']
];

$debtor_id = $input['debtor_id'] ?? null;
$email_type = $input['email_type'] ?? 'reminder';

// Simulate different scenarios for testing
$scenario = $_GET['scenario'] ?? 'success';

switch ($scenario) {
    case 'success':
        if ($debtor_id && isset($mock_debtors[$debtor_id])) {
            $debtor = $mock_debtors[$debtor_id];
            $payment_link = "https://mangijosh.github.io/Debtor/#pay-{$debtor_id}";
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'message' => 'Email sent successfully',
                    'debtor_id' => $debtor_id,
                    'email' => $debtor['email'],
                    'payment_link' => $payment_link
                ]
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Debtor not found'
            ]);
        }
        break;
        
    case 'error':
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'SMTP server connection failed'
        ]);
        break;
        
    case 'timeout':
        // Simulate a timeout by sleeping longer
        sleep(35);
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => [
                'message' => 'Email sent successfully (delayed)',
                'debtor_id' => $debtor_id,
                'email' => $mock_debtors[$debtor_id]['email'] ?? 'test@example.com',
                'payment_link' => "https://mangijosh.github.io/Debtor/#pay-{$debtor_id}"
            ]
        ]);
        break;
        
    case 'network_error':
        // Simulate network error by not responding
        exit();
        break;
        
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid scenario'
        ]);
}
?>
