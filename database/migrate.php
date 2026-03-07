<?php
/**
 * Database migration runner
 * Run via browser: /database/migrate.php or CLI: php database/migrate.php
 * Delete this file after running for security.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

$pdo = getDB();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("CREATE TABLE IF NOT EXISTS _migrations (name VARCHAR(100) PRIMARY KEY, run_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

$migrationsDir = __DIR__ . '/migrations';
$files = glob($migrationsDir . '/*.sql');
sort($files);

$run = [];
$errors = [];

foreach ($files as $file) {
    $name = basename($file, '.sql');
    $stmt = $pdo->prepare("SELECT 1 FROM _migrations WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->fetch()) continue;

    $sql = file_get_contents($file);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $ok = true;

    foreach ($statements as $stmtSql) {
        if (empty($stmtSql) || strpos($stmtSql, '--') === 0) continue;
        try {
            $pdo->exec($stmtSql);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                // Column already exists, migration partially applied
                continue;
            }
            if (strpos($e->getMessage(), 'Unknown column') !== false && strpos($stmtSql, 'UPDATE') !== false) {
                // No sent/overdue - skip UPDATE
                continue;
            }
            $errors[] = "$name: " . $e->getMessage();
            $ok = false;
        }
    }

    if ($ok) {
        $pdo->prepare("INSERT IGNORE INTO _migrations (name) VALUES (?)")->execute([$name]);
        $run[] = "$name: OK";
    }
}

// Ensure status enum includes 'unpaid' (for DBs created before migration 002)
$col = $pdo->query("SHOW COLUMNS FROM invoices WHERE Field = 'status'")->fetch(PDO::FETCH_ASSOC);
if ($col && strpos($col['Type'], 'unpaid') === false) {
    try {
        @$pdo->exec("UPDATE invoices SET status = 'unpaid' WHERE status IN ('sent', 'overdue')");
        $pdo->exec("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'unpaid', 'paid', 'cancelled') DEFAULT 'draft'");
        $pdo->prepare("INSERT IGNORE INTO _migrations (name) VALUES ('002_invoice_status_unpaid_paid')")->execute();
        $run[] = "002_invoice_status_unpaid_paid: OK";
    } catch (PDOException $e) {
        $errors[] = "002: " . $e->getMessage();
    }
}

header('Content-Type: text/plain; charset=utf-8');
echo "Migrations run:\n" . (empty($run) ? "(none pending)\n" : implode("\n", $run) . "\n");
if ($errors) echo "\nErrors:\n" . implode("\n", $errors) . "\n";
echo "\nDone.";
