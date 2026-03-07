<?php
/**
 * Helper functions
 */

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function getCompanySettings() {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM company_settings WHERE id = 1");
    return $stmt->fetch() ?: [];
}

function getNextInvoiceNumber() {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT invoice_prefix, invoice_next_number FROM company_settings WHERE id = 1");
    $row = $stmt->fetch();
    $num = $row['invoice_next_number'];
    $prefix = $row['invoice_prefix'] ?? 'INV';
    $formatted = $prefix . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    
    $pdo->prepare("UPDATE company_settings SET invoice_next_number = invoice_next_number + 1 WHERE id = 1")->execute();
    return $formatted;
}

function sendMail($to, $subject, $body) {
    $headers = "From: " . SITE_EMAIL . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    return @mail($to, $subject, $body, $headers);
}
