-- Webspace Invoice System - Database Schema
-- Run this to set up your MySQL database

CREATE DATABASE IF NOT EXISTS webspace_invoice;
USE webspace_invoice;

-- Users (single user for now)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_system_admin TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Company settings (configurable from backend)
CREATE TABLE company_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) DEFAULT 'My Company',
    logo_url VARCHAR(500) DEFAULT NULL,
    address TEXT,
    phone VARCHAR(50),
    email VARCHAR(255),
    website VARCHAR(255),
    currency VARCHAR(10) DEFAULT 'NGN',
    bank_name VARCHAR(255),
    bank_account_name VARCHAR(255),
    bank_account_number VARCHAR(100),
    tax_label VARCHAR(50) DEFAULT 'VAT',
    tax_rate DECIMAL(5,2) DEFAULT 7.50,
    invoice_prefix VARCHAR(20) DEFAULT 'INV',
    invoice_next_number INT DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Payment terms (dynamic/custom)
CREATE TABLE payment_terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    days INT DEFAULT 0,
    description TEXT,
    is_default TINYINT(1) DEFAULT 0
);

-- Clients
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    company_name VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Invoices
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    client_id INT NOT NULL,
    status ENUM('draft', 'unpaid', 'paid', 'cancelled') DEFAULT 'draft',
    payment_type ENUM('full', 'installment') DEFAULT 'full',
    payment_terms_id INT,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    notes TEXT,
    terms_conditions TEXT,
    template_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    FOREIGN KEY (payment_terms_id) REFERENCES payment_terms(id) ON DELETE SET NULL
);

-- Invoice line items (services)
CREATE TABLE invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    description TEXT NOT NULL,
    quantity DECIMAL(10,2) DEFAULT 1,
    unit_price DECIMAL(15,2) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

-- Invoice payments (for installments)
CREATE TABLE invoice_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    payment_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

-- Invoice templates (flexible)
CREATE TABLE invoice_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    config JSON,
    is_default TINYINT(1) DEFAULT 0
);

-- Password reset tokens
CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default data
INSERT INTO company_settings (id) VALUES (1);

INSERT INTO payment_terms (name, days, description, is_default) VALUES
('Due on Receipt', 0, 'Payment due immediately', 1),
('Net 15', 15, 'Payment due within 15 days', 0),
('Net 30', 30, 'Payment due within 30 days', 0),
('Net 60', 60, 'Payment due within 60 days', 0);

INSERT INTO invoice_templates (name, slug, config, is_default) VALUES
('Professional', 'professional', '{"layout":"two-column","accentColor":"#2563eb","fontFamily":"system-ui"}', 1),
('Minimal', 'minimal', '{"layout":"single-column","accentColor":"#000000","fontFamily":"Georgia"}', 0),
('Modern', 'modern', '{"layout":"two-column","accentColor":"#059669","fontFamily":"sans-serif"}', 0);

-- Create default user: admin@example.com / password123 (change after first login!)
INSERT INTO users (email, password, name) VALUES 
('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin');
