-- Platform operator flag: users with is_system_admin = 1 use the operator UI only (no tenant invoice/client features).
ALTER TABLE users
    ADD COLUMN is_system_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER name;
