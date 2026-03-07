<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
requireLogin();
$user = getCurrentUser();
$currentPage = $currentPage ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body class="app-body">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="logo">
                <img src="<?= APP_URL ?>/assets/images/logo.png" alt="Webspace" class="logo-img">
                <span class="logo-text">Invoice</span>
            </a>
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <span class="nav-icon">📊</span>
                <span class="nav-label">Dashboard</span>
            </a>
            <a href="invoices.php" class="nav-item <?= $currentPage === 'invoices' ? 'active' : '' ?>">
                <span class="nav-icon">📄</span>
                <span class="nav-label">Invoices</span>
            </a>
            <a href="clients.php" class="nav-item <?= $currentPage === 'clients' ? 'active' : '' ?>">
                <span class="nav-icon">👥</span>
                <span class="nav-label">Clients</span>
            </a>
            <a href="settings.php" class="nav-item <?= $currentPage === 'settings' ? 'active' : '' ?>">
                <span class="nav-icon">⚙️</span>
                <span class="nav-label">Settings</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
            <a href="logout.php" class="nav-item">Logout</a>
        </div>
    </aside>
    <main class="main-content">
        <header class="top-bar">
            <h1 class="page-title"><?= $pageTitle ?? 'Dashboard' ?></h1>
        </header>
        <div class="page-content">
