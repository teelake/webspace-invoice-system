-- Add bank account fields to company_settings for invoice display
ALTER TABLE company_settings
    ADD COLUMN bank_name VARCHAR(255) DEFAULT NULL AFTER currency,
    ADD COLUMN bank_account_name VARCHAR(255) DEFAULT NULL AFTER bank_name,
    ADD COLUMN bank_account_number VARCHAR(100) DEFAULT NULL AFTER bank_account_name;
