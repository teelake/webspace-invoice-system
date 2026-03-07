# Webspace Invoice System

A fully customizable invoice generation and management system built with HTML, CSS, JavaScript, PHP, and MySQL.

## Features

- **Company settings** – Configurable from backend (name, logo, address, tax rate, currency, invoice prefix)
- **Client management** – Add, edit, delete clients
- **Invoices** – Create invoices with services, full or installment payments
- **Payment terms** – Dynamic/custom terms (Due on Receipt, Net 15, Net 30, etc.)
- **Status tracking** – Draft, Sent, Paid, Overdue, Cancelled
- **Flexible templates** – Professional, Minimal, Modern (accent colors, layout)
- **PDF export** – Print to PDF via browser
- **Email** – Send invoices by email
- **Auth** – Login, forgot password, reset password

## Requirements

- PHP 7.4+
- MySQL 5.7+
- Web server (Apache/Nginx)

## Setup

1. **Clone/copy** the project to your web root (e.g. `htdocs/webspace-invoice-system`).

2. **Create the database:**
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   Or run `database/schema.sql` in phpMyAdmin.

3. **Configure database** – Copy `config/database.example.php` to `config/database.php` and set your credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'webspace_invoice');
   define('DB_USER', 'your_user');
   define('DB_PASS', 'your_password');
   ```

4. **Set APP_URL** in `config/app.php`:
   ```php
   define('APP_URL', 'http://localhost/webspace-invoice-system');
   ```

5. **Default login:**
   - Email: `admin@example.com`
   - Password: `password`
   - **Change this after first login!**

6. Open `http://localhost/webspace-invoice-system` in your browser.

## Email (Forgot Password / Send Invoice)

Uses PHP `mail()`. For production, configure your server's mail or use SMTP (e.g. PHPMailer).

## Project Structure

```
├── api/              # REST API endpoints
├── assets/
│   ├── css/
│   └── js/
├── config/
├── database/
├── includes/
├── index.php         # Login
├── dashboard.php
├── invoices.php
├── invoice-edit.php
├── invoice-view.php
├── invoice-pdf.php
├── clients.php
├── settings.php
├── forgot-password.php
├── reset-password.php
└── logout.php
```

## License

MIT
