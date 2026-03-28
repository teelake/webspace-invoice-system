-- If you can sign in but always land on the tenant dashboard (not Platform),
-- your account row likely has is_system_admin = 0. Run ONE of the following
-- in MySQL / phpMyAdmin, then sign out and sign in again.

-- Default seed user from schema.sql:
UPDATE users SET is_system_admin = 1 WHERE email = 'admin@example.com' LIMIT 1;

-- Or promote by your actual admin email:
-- UPDATE users SET is_system_admin = 1 WHERE email = 'you@example.com' LIMIT 1;
