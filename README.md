# Debt Collection Email Handler

A secure PHP backend system for sending personalized debt collection email reminders to debtors. This system integrates with the HTML frontend to provide a complete debt collection solution.

## Features

- **Secure Email Sending**: PHPMailer integration with SMTP support
- **Dynamic Content**: Personalized emails based on debtor data
- **Security Tokens**: Unique, time-limited payment links
- **Rate Limiting**: Prevents spam and abuse
- **API Authentication**: Secure API key validation
- **Database Integration**: MySQL/MariaDB support
- **Compliance Ready**: POPIA and legal compliance features
- **Audit Logging**: Complete activity tracking

## Installation

### 1. Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or MariaDB 10.2+
- Composer
- Web server (Apache/Nginx)
- SMTP server access

### 2. Setup Database

```sql
-- Create database and import schema
mysql -u root -p < database_schema.sql
```

### 3. Install Dependencies

```bash
composer install
```

### 4. Configuration

1. Copy the configuration file:
```bash
cp config.php config_local.php
```

2. Update `config_local.php` with your settings:
```php
'database' => [
    'host' => 'localhost',
    'dbname' => 'debt_collection',
    'username' => 'your_username',
    'password' => 'your_password'
],
'email' => [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_username' => 'your_email@legalpartners.co.za',
    'smtp_password' => 'your_app_password'
],
'security' => [
    'api_key' => 'your_secure_api_key_here'
]
```

### 5. Update API Key

Update the API key in both `email_handler.php` and `test_email_api.php` to match your configuration.

## Usage

### API Endpoint

**POST** `/email_handler.php`

#### Headers
```
Content-Type: application/json
X-API-Key: your_secure_api_key_here
```

#### Request Body
```json
{
    "debtor_id": 1,
    "email_type": "reminder"
}
```

#### Response (Success)
```json
{
    "success": true,
    "data": {
        "message": "Email sent successfully",
        "debtor_id": 1,
        "email": "debtor@example.com",
        "payment_link": "https://legalpartners.co.za/payment.php?debtor_id=1&token=..."
    }
}
```

#### Response (Error)
```json
{
    "success": false,
    "error": "Error message here"
}
```

### Email Types

- **reminder**: Standard payment reminder
- **final_notice**: Final notice before legal action
- **arrangement**: Payment arrangement confirmation
- **payment_confirmation**: Payment received confirmation

### Testing

1. Open `test_email_api.php` in your browser
2. Click the test buttons to send emails
3. Check the results and payment links

## Security Features

### Authentication
- API key validation
- Rate limiting (10 emails per minute per IP)
- IP whitelisting support

### Email Security
- SPF, DKIM, DMARC support
- Unique payment tokens with expiration
- HMAC signature verification
- Secure payment links

### Data Protection
- POPIA compliance
- Encrypted sensitive data
- Audit logging
- Data retention policies

## Database Schema

### Tables
- **debtors**: Debtor information and case details
- **payment_tokens**: Secure payment link tokens
- **email_logs**: Email sending history and tracking
- **payment_history**: Payment transaction records
- **security_logs**: Security and audit events

### Views
- **active_debtors**: Filtered view of active debtors
- **payment_stats**: Payment statistics and balances

## Integration with Frontend

### JavaScript Integration
```javascript
// Send email reminder
async function sendEmailReminder(debtorId) {
    const response = await fetch('/email_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-API-Key': 'your_secure_api_key_here'
        },
        body: JSON.stringify({
            debtor_id: debtorId,
            email_type: 'reminder'
        })
    });
    
    const result = await response.json();
    if (result.success) {
        console.log('Email sent:', result.data.payment_link);
    } else {
        console.error('Error:', result.error);
    }
}
```

### HTML Form Integration
```html
<form id="emailForm">
    <input type="hidden" name="debtor_id" value="1">
    <select name="email_type">
        <option value="reminder">Payment Reminder</option>
        <option value="final_notice">Final Notice</option>
        <option value="arrangement">Payment Arrangement</option>
    </select>
    <button type="submit">Send Email</button>
</form>
```

## Email Templates

The system includes professional email templates with:

- **Dynamic Content**: Personalized debtor information
- **Security Indicators**: Trust badges and verification
- **Payment Links**: Secure, time-limited payment URLs
- **Legal Compliance**: Required legal disclaimers
- **Mobile Responsive**: Optimized for all devices

## Monitoring and Logging

### Email Logs
- Send status tracking
- Error logging
- Performance metrics
- Bounce handling

### Security Logs
- API access logs
- Failed authentication attempts
- Suspicious activity detection
- Rate limiting events

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials
   - Ensure database exists
   - Verify MySQL service is running

2. **Email Sending Failed**
   - Verify SMTP credentials
   - Check firewall settings
   - Test SMTP connection

3. **API Key Invalid**
   - Ensure API key matches in both files
   - Check header format
   - Verify case sensitivity

4. **Rate Limiting**
   - Check IP whitelist
   - Adjust rate limit settings
   - Monitor security logs

### Debug Mode

Enable debug mode in `config_local.php`:
```php
'logging' => [
    'enabled' => true,
    'level' => 'DEBUG'
]
```

## Support

For technical support or questions:
- Email: support@legalpartners.co.za
- Phone: 011 123 4567
- Documentation: [Internal Wiki]

## License

Proprietary - Legal Partners Debt Collection Services

## Version History

- **v1.0.0**: Initial release with core functionality
- **v1.1.0**: Added security enhancements and compliance features
- **v1.2.0**: Improved email templates and mobile optimization
