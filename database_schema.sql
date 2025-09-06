-- Debt Collection Database Schema
-- Create this database and tables for the email handler to work

CREATE DATABASE IF NOT EXISTS debt_collection;
USE debt_collection;

-- Debtors table
CREATE TABLE debtors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20),
    case_ref VARCHAR(50) NOT NULL UNIQUE,
    amount_due DECIMAL(10,2) NOT NULL,
    original_creditor VARCHAR(255),
    debt_summary TEXT,
    status ENUM('unpaid', 'partial', 'paid', 'arrangement', 'deleted') DEFAULT 'unpaid',
    last_payment DATE,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_case_ref (case_ref),
    INDEX idx_status (status)
);

-- Payment tokens table for secure links
CREATE TABLE payment_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    debtor_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    signature VARCHAR(255) NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (debtor_id) REFERENCES debtors(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_debtor_id (debtor_id),
    INDEX idx_expires_at (expires_at)
);

-- Email logs table
CREATE TABLE email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    debtor_id INT NOT NULL,
    email_type ENUM('reminder', 'final_notice', 'arrangement', 'payment_confirmation') NOT NULL,
    payment_link TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    status ENUM('sent', 'failed', 'bounced') DEFAULT 'sent',
    error_message TEXT NULL,
    FOREIGN KEY (debtor_id) REFERENCES debtors(id) ON DELETE CASCADE,
    INDEX idx_debtor_id (debtor_id),
    INDEX idx_sent_at (sent_at),
    INDEX idx_ip_address (ip_address)
);

-- Payment history table
CREATE TABLE payment_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    debtor_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('credit_card', 'eft', 'cash', 'arrangement') NOT NULL,
    transaction_id VARCHAR(255),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (debtor_id) REFERENCES debtors(id) ON DELETE CASCADE,
    INDEX idx_debtor_id (debtor_id),
    INDEX idx_payment_date (payment_date),
    INDEX idx_status (status)
);

-- Security logs table
CREATE TABLE security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    debtor_id INT NULL,
    action VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (debtor_id) REFERENCES debtors(id) ON DELETE SET NULL,
    INDEX idx_debtor_id (debtor_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Insert sample data
INSERT INTO debtors (name, email, phone, case_ref, amount_due, original_creditor, debt_summary, status, last_payment, address) VALUES
('Lindiwe Khumalo', 'lindiwe.k@example.com', '071 222 3333', 'LP-2024-005', 2015.75, 'WesBank', 'Vehicle finance with WesBank. Car loan payments missed due to job loss, partial payments resumed after finding new employment.', 'arrangement', '2024-08-15', '21 Church St, Pretoria, 0002'),
('Sipho Mthembu', 'sipho.mthembu@example.com', '082 123 4567', 'LP-2024-001', 171.88, 'Vodacom', 'Outstanding mobile phone contract with Vodacom. Contract terminated early due to non-payment of monthly installments.', 'paid', '2024-09-04', '12 Main Rd, Randburg, 2194'),
('Sarah Smith', 'sarah.smith@example.com', '083 987 6543', 'LP-2024-002', 845.32, 'Standard Bank', 'Credit card debt with Standard Bank. Multiple missed payments on retail purchases and cash advances.', 'arrangement', NULL, '45 Oak Ave, Sandton, 2196'),
('Mike Johnson', 'mike.johnson@example.com', '081 555 1212', 'LP-2024-003', 1234.56, 'FNB', 'Personal loan with FNB. Loan taken for home improvements, partial payments made but account fell into arrears.', 'partial', '2024-08-28', '7 Pine St, Midrand, 1685'),
('Peter Adams', 'peter.adams@example.com', '082 111 2222', 'LP-2024-004', 422.10, 'Edgars', 'Store account with Edgars. Clothing and household items purchased on credit, account suspended due to non-payment.', 'unpaid', NULL, '9 Willow Ln, Roodepoort, 1724'),
('Thabo Nene', 'thabo.nene@example.com', '072 444 5555', 'LP-2024-006', 95.00, 'City of Johannesburg', 'Municipal services account with City of Johannesburg. Outstanding electricity and water charges from previous residence.', 'unpaid', NULL, '3 Chief Rd, Soweto, 1868'),
('Aisha Patel', 'aisha.patel@example.com', '079 666 7777', 'LP-2024-007', 650.00, 'Discovery Health', 'Medical aid arrears with Discovery Health. Premiums unpaid for 3 months due to financial difficulties, now resolved.', 'paid', '2024-09-04', '88 Market St, Johannesburg, 2001'),
('Kevin Brown', 'kevin.brown@example.com', '074 888 9999', 'LP-2024-008', 312.40, 'Virgin Active', 'Gym membership with Virgin Active. Annual membership fees unpaid, partial payment arrangement in place.', 'partial', '2024-09-02', '14 Cedar Rd, Fourways, 2055');

-- Create indexes for better performance
CREATE INDEX idx_debtors_status_amount ON debtors(status, amount_due);
CREATE INDEX idx_email_logs_debtor_sent ON email_logs(debtor_id, sent_at);
CREATE INDEX idx_payment_tokens_expires ON payment_tokens(expires_at, used_at);

-- Create a view for active debtors
CREATE VIEW active_debtors AS
SELECT 
    id, name, email, phone, case_ref, amount_due, 
    original_creditor, debt_summary, status, last_payment, address,
    created_at, updated_at
FROM debtors 
WHERE status != 'deleted';

-- Create a view for payment statistics
CREATE VIEW payment_stats AS
SELECT 
    d.id,
    d.name,
    d.case_ref,
    d.amount_due,
    COALESCE(SUM(ph.amount), 0) as total_paid,
    (d.amount_due - COALESCE(SUM(ph.amount), 0)) as remaining_balance,
    COUNT(ph.id) as payment_count,
    MAX(ph.payment_date) as last_payment_date
FROM debtors d
LEFT JOIN payment_history ph ON d.id = ph.debtor_id AND ph.status = 'completed'
GROUP BY d.id, d.name, d.case_ref, d.amount_due;
