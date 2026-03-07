<?php
require_once __DIR__ . '/init.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT * FROM invoice_templates ORDER BY is_default DESC");
    $templates = $stmt->fetchAll();
    foreach ($templates as &$t) {
        $t['config'] = json_decode($t['config'] ?? '{}', true);
    }
    jsonResponse($templates);
}
