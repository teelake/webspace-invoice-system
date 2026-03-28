<?php
require_once __DIR__ . '/init-tenant.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $settings = getCompanySettings();
    // Resolve relative logo URL to full URL so it works in all contexts (invoice, PDF, email)
    if (!empty($settings['logo_url']) && !preg_match('#^https?://#', $settings['logo_url'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = $_SERVER['SCRIPT_NAME'] ?? '/api/company.php';
        // API is in api/ subdir, so go up one level to get app root
        $appRoot = rtrim(dirname(dirname($script)), '/');
        $settings['logo_url'] = $scheme . '://' . $host . $appRoot . '/' . ltrim($settings['logo_url'], '/');
    }
    jsonResponse($settings);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $fields = ['company_name', 'logo_url', 'address', 'phone', 'email', 'website', 'currency', 'bank_name', 'bank_account_name', 'bank_account_number', 'tax_label', 'tax_rate', 'invoice_prefix', 'invoice_next_number'];
    $updates = [];
    $params = [];
    foreach ($fields as $f) {
        if (isset($input[$f])) {
            if ($f === 'tax_rate' || $f === 'invoice_next_number') {
                $updates[] = "$f = ?";
                $params[] = $input[$f];
            } else {
                $updates[] = "$f = ?";
                $params[] = $input[$f];
            }
        }
    }
    if (empty($updates)) {
        jsonResponse(['error' => 'No valid fields'], 400);
    }
    $params[] = 1;
    $sql = "UPDATE company_settings SET " . implode(', ', $updates) . " WHERE id = ?";
    $pdo->prepare($sql)->execute($params);
    jsonResponse(getCompanySettings());
}
