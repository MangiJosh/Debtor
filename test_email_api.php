<?php
/**
 * Test client for Debt Collection Email Handler API
 * Use this to test the email sending functionality
 */

// Configuration
$api_url = 'http://localhost/email_handler.php';
$api_key = 'your_secure_api_key_here'; // Must match the one in email_handler.php

// Test data
$test_debtors = [
    [
        'debtor_id' => 1,
        'email_type' => 'reminder',
        'name' => 'Lindiwe Khumalo'
    ],
    [
        'debtor_id' => 2,
        'email_type' => 'final_notice',
        'name' => 'Sarah Smith'
    ],
    [
        'debtor_id' => 3,
        'email_type' => 'arrangement',
        'name' => 'Mike Johnson'
    ]
];

/**
 * Send test email
 */
function sendTestEmail($debtor_id, $email_type, $api_url, $api_key) {
    $data = [
        'debtor_id' => $debtor_id,
        'email_type' => $email_type
    ];
    
    $options = [
        'http' => [
            'header' => [
                "Content-Type: application/json",
                "X-API-Key: $api_key"
            ],
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($api_url, false, $context);
    
    if ($result === FALSE) {
        return ['success' => false, 'error' => 'Failed to send request'];
    }
    
    return json_decode($result, true);
}

/**
 * Test API endpoint
 */
function testApiEndpoint($api_url) {
    $options = [
        'http' => [
            'method' => 'GET'
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($api_url, false, $context);
    
    return $result !== FALSE;
}

// HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Handler API Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        button:hover { background: #0056b3; }
        .result { margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 3px; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Debt Collection Email Handler - API Test</h1>
        
        <div class="test-section info">
            <h3>API Configuration</h3>
            <p><strong>API URL:</strong> <?php echo htmlspecialchars($api_url); ?></p>
            <p><strong>API Key:</strong> <?php echo htmlspecialchars(substr($api_key, 0, 10) . '...'); ?></p>
        </div>
        
        <?php
        // Test API endpoint availability
        echo '<div class="test-section">';
        echo '<h3>API Endpoint Test</h3>';
        
        if (testApiEndpoint($api_url)) {
            echo '<div class="result success">✅ API endpoint is accessible</div>';
        } else {
            echo '<div class="result error">❌ API endpoint is not accessible</div>';
        }
        echo '</div>';
        
        // Test individual emails
        echo '<div class="test-section">';
        echo '<h3>Send Test Emails</h3>';
        echo '<p>Click the buttons below to send test emails to different debtors:</p>';
        
        foreach ($test_debtors as $debtor) {
            echo '<button onclick="sendEmail(' . $debtor['debtor_id'] . ', \'' . $debtor['email_type'] . '\')">';
            echo 'Send ' . ucfirst($debtor['email_type']) . ' to ' . $debtor['name'];
            echo '</button>';
        }
        
        echo '<div id="results"></div>';
        echo '</div>';
        
        // Show recent test results
        echo '<div class="test-section">';
        echo '<h3>Recent Test Results</h3>';
        
        if (isset($_GET['test']) && isset($_GET['debtor_id']) && isset($_GET['email_type'])) {
            $debtor_id = (int)$_GET['debtor_id'];
            $email_type = $_GET['email_type'];
            
            echo '<div class="result info">';
            echo '<strong>Testing:</strong> Sending ' . $email_type . ' email to debtor ID ' . $debtor_id;
            echo '</div>';
            
            $result = sendTestEmail($debtor_id, $email_type, $api_url, $api_key);
            
            if ($result['success']) {
                echo '<div class="result success">';
                echo '<strong>✅ Success:</strong> ' . $result['data']['message'] . '<br>';
                echo '<strong>Debtor ID:</strong> ' . $result['data']['debtor_id'] . '<br>';
                echo '<strong>Email:</strong> ' . $result['data']['email'] . '<br>';
                echo '<strong>Payment Link:</strong> <a href="' . $result['data']['payment_link'] . '" target="_blank">' . $result['data']['payment_link'] . '</a>';
                echo '</div>';
            } else {
                echo '<div class="result error">';
                echo '<strong>❌ Error:</strong> ' . $result['error'];
                echo '</div>';
            }
        }
        
        echo '</div>';
        ?>
        
        <div class="test-section info">
            <h3>Setup Instructions</h3>
            <ol>
                <li>Create the database using <code>database_schema.sql</code></li>
                <li>Update <code>config.php</code> with your database and email settings</li>
                <li>Install PHPMailer: <code>composer require phpmailer/phpmailer</code></li>
                <li>Update the API key in both <code>email_handler.php</code> and this test file</li>
                <li>Ensure your web server can send emails (SMTP configuration)</li>
            </ol>
        </div>
        
        <div class="test-section">
            <h3>API Documentation</h3>
            <h4>Endpoint: POST /email_handler.php</h4>
            <h5>Headers:</h5>
            <pre>Content-Type: application/json
X-API-Key: your_secure_api_key_here</pre>
            
            <h5>Request Body:</h5>
            <pre>{
    "debtor_id": 1,
    "email_type": "reminder"
}</pre>
            
            <h5>Response (Success):</h5>
            <pre>{
    "success": true,
    "data": {
        "message": "Email sent successfully",
        "debtor_id": 1,
        "email": "debtor@example.com",
        "payment_link": "https://legalpartners.co.za/payment.php?debtor_id=1&token=..."
    }
}</pre>
            
            <h5>Response (Error):</h5>
            <pre>{
    "success": false,
    "error": "Error message here"
}</pre>
        </div>
    </div>
    
    <script>
        function sendEmail(debtorId, emailType) {
            const url = new URL(window.location);
            url.searchParams.set('test', '1');
            url.searchParams.set('debtor_id', debtorId);
            url.searchParams.set('email_type', emailType);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>
