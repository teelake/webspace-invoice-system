<?php
require_once __DIR__ . '/init-tenant.php';
require_once __DIR__ . '/../config/app.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

if (empty($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    $msg = 'No file uploaded';
    if (!empty($_FILES['logo']['error'])) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
            UPLOAD_ERR_FORM_SIZE => 'File too large',
            UPLOAD_ERR_PARTIAL => 'File partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file selected',
            UPLOAD_ERR_NO_TMP_DIR => 'Server config error',
            UPLOAD_ERR_CANT_WRITE => 'Server cannot save file',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by extension'
        ];
        $msg = $errors[$_FILES['logo']['error']] ?? 'Upload failed';
    }
    jsonResponse(['error' => $msg], 400);
}

$file = $_FILES['logo'];
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowed)) {
    jsonResponse(['error' => 'Invalid file type. Use PNG, JPG, GIF or WebP.'], 400);
}

$maxSize = 2 * 1024 * 1024; // 2MB
if ($file['size'] > $maxSize) {
    jsonResponse(['error' => 'File too large. Max 2MB.'], 400);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) ?: 'png';
if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
    $ext = 'png';
}

$uploadDir = dirname(__DIR__) . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = 'company-logo.' . $ext;
$targetPath = $uploadDir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    jsonResponse(['error' => 'Failed to save file'], 500);
}

// Build URL from current request so it works in any environment (localhost, staging, production)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = $_SERVER['SCRIPT_NAME'] ?? '/api/company-logo.php';
$appRoot = rtrim(dirname(dirname($script)), '/');
$baseUrl = $scheme . '://' . $host . $appRoot;
// Add cache-busting so browser fetches new logo after upload
$logoUrl = $baseUrl . '/uploads/' . $filename . '?v=' . time();

$stmt = $pdo->prepare("UPDATE company_settings SET logo_url = ? WHERE id = 1");
$stmt->execute([$logoUrl]);

jsonResponse(['logo_url' => $logoUrl, 'success' => true]);
