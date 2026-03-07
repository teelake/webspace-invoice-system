-- Simplify invoice status: unpaid (covers sent + overdue) and paid
-- Run this if you already have the database with old statuses

-- 1. Migrate existing data: sent and overdue -> unpaid
UPDATE invoices SET status = 'unpaid' WHERE status IN ('sent', 'overdue');

-- 2. Update enum to new values
ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'unpaid', 'paid', 'cancelled') DEFAULT 'draft';
