-- Add company_name to clients (run this if you already have the database)
ALTER TABLE clients ADD COLUMN company_name VARCHAR(255) DEFAULT NULL AFTER name;
