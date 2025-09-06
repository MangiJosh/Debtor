<?php
/**
 * Configuration file for Debt Collection Email Handler
 * Copy this file to config_local.php and update with your settings
 */

return [
    // Database configuration
    'database' => [
        'host' => 'localhost',
        'dbname' => 'debt_collection',
        'username' => 'your_username',
        'password' => 'your_password',
        'charset' => 'utf8mb4'
    ],
    
    // Email configuration
    'email' => [
        'smtp_host' => 'smtp.gmail.com', // or your SMTP server
        'smtp_port' => 587,
        'smtp_username' => 'your_email@legalpartners.co.za',
        'smtp_password' => 'your_app_password', // Use App Password for Gmail
        'from_email' => 'noreply@legalpartners.co.za',
        'from_name' => 'Legal Partners Debt Collection',
        'reply_to' => 'collections@legalpartners.co.za',
        'bounce_email' => 'bounces@legalpartners.co.za'
    ],
    
    // Security configuration
    'security' => [
        'api_key' => 'your_secure_api_key_here_' . bin2hex(random_bytes(16)),
        'rate_limit' => 10, // emails per minute per IP
        'max_retries' => 3,
        'token_expiry' => 3600, // 1 hour in seconds
        'password_min_length' => 8,
        'session_timeout' => 1800 // 30 minutes
    ],
    
    // Application configuration
    'app' => [
        'name' => 'Legal Partners Debt Collection',
        'version' => '1.0.0',
        'timezone' => 'Africa/Johannesburg',
        'base_url' => 'https://legalpartners.co.za',
        'support_email' => 'support@legalpartners.co.za',
        'support_phone' => '011 123 4567'
    ],
    
    // Email templates configuration
    'templates' => [
        'reminder' => [
            'subject_prefix' => 'Payment Reminder',
            'priority' => 'high',
            'retry_attempts' => 3
        ],
        'final_notice' => [
            'subject_prefix' => 'Final Notice - Legal Action Required',
            'priority' => 'urgent',
            'retry_attempts' => 5
        ],
        'arrangement' => [
            'subject_prefix' => 'Payment Arrangement Confirmation',
            'priority' => 'normal',
            'retry_attempts' => 2
        ],
        'payment_confirmation' => [
            'subject_prefix' => 'Payment Received - Thank You',
            'priority' => 'normal',
            'retry_attempts' => 1
        ]
    ],
    
    // Logging configuration
    'logging' => [
        'enabled' => true,
        'level' => 'INFO', // DEBUG, INFO, WARNING, ERROR
        'file_path' => 'logs/email_handler.log',
        'max_file_size' => 10485760, // 10MB
        'max_files' => 5
    ],
    
    // Rate limiting configuration
    'rate_limiting' => [
        'enabled' => true,
        'window_size' => 60, // seconds
        'max_requests' => 10, // per window
        'ban_duration' => 3600, // 1 hour
        'whitelist_ips' => [
            '127.0.0.1',
            '::1'
        ]
    ],
    
    // Payment configuration
    'payment' => [
        'gateway_url' => 'https://api.paymentgateway.com',
        'merchant_id' => 'your_merchant_id',
        'api_key' => 'your_payment_api_key',
        'webhook_secret' => 'your_webhook_secret',
        'currency' => 'ZAR',
        'supported_methods' => ['credit_card', 'eft', 'mobile_payment']
    ],
    
    // Legal compliance
    'compliance' => [
        'popia_compliant' => true,
        'data_retention_days' => 2555, // 7 years
        'audit_log_enabled' => true,
        'encryption_enabled' => true,
        'backup_enabled' => true
    ]
];
?>
