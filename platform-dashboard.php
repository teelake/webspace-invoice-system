<?php
$currentPage = 'platform';
$pageTitle = 'Platform';
$pageSubtitle = 'Operator overview';
require_once __DIR__ . '/includes/layout.php';

$pdo = getDB();
$userCount = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$invoiceCount = (int)$pdo->query('SELECT COUNT(*) FROM invoices')->fetchColumn();
?>
<div class="welcome-block">
    <h2>Platform operator</h2>
    <p>You are signed in as a platform administrator. Invoice, client, and company settings are for tenant accounts only.</p>
</div>
<div class="stats-grid">
    <div class="stat-card stat-card-primary">
        <div class="stat-content">
            <span class="stat-value"><?= $userCount ?></span>
            <span class="stat-label">Registered users</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-content">
            <span class="stat-value"><?= $invoiceCount ?></span>
            <span class="stat-label">Invoices (all accounts)</span>
        </div>
    </div>
</div>
<div class="content-card" style="padding: 1.25rem; margin-top: 1rem;">
    <p class="text-muted" style="margin:0">Use <strong>Profile</strong> to update your account. Add organization-scoped tools here as your SaaS model grows.</p>
</div>
<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
