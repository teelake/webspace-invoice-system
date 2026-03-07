<?php
require_once __DIR__ . '/init.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare("
            SELECT i.*, c.name as client_name, c.company_name as client_company_name, c.email as client_email, c.phone as client_phone, c.address as client_address,
                   pt.name as payment_terms_name, pt.days as payment_terms_days
            FROM invoices i
            LEFT JOIN clients c ON i.client_id = c.id
            LEFT JOIN payment_terms pt ON i.payment_terms_id = pt.id
            WHERE i.id = ?
        ");
        $stmt->execute([$id]);
        $inv = $stmt->fetch();
        if (!$inv) jsonResponse(['error' => 'Not found'], 404);
        $tplStmt = $pdo->prepare("SELECT config FROM invoice_templates WHERE id = ?");
        $tplStmt->execute([$inv['template_id'] ?? 1]);
        $tpl = $tplStmt->fetch();
        $inv['template_config'] = $tpl ? json_decode($tpl['config'] ?? '{}', true) : [];
        $stmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY sort_order, id");
        $stmt->execute([$id]);
        $inv['items'] = $stmt->fetchAll();
        $stmt = $pdo->prepare("SELECT * FROM invoice_payments WHERE invoice_id = ? ORDER BY payment_date");
        $stmt->execute([$id]);
        $inv['payments'] = $stmt->fetchAll();
        jsonResponse($inv);
    }
    $stmt = $pdo->query("
        SELECT i.id, i.invoice_number, i.status, i.payment_type, i.issue_date, i.due_date, i.created_at,
               c.name as client_name, c.company_name as client_company_name,
               (SELECT COALESCE(SUM(amount), 0) FROM invoice_items WHERE invoice_id = i.id) as subtotal,
               (SELECT COALESCE(SUM(amount), 0) FROM invoice_payments WHERE invoice_id = i.id) as paid_amount
        FROM invoices i
        LEFT JOIN clients c ON i.client_id = c.id
        ORDER BY i.created_at DESC
    ");
    $list = $stmt->fetchAll();
    $company = getCompanySettings();
    $taxRate = (float)($company['tax_rate'] ?? 0);
    foreach ($list as &$row) {
        $row['subtotal'] = (float)$row['subtotal'];
        $row['paid_amount'] = (float)$row['paid_amount'];
        $row['tax'] = $row['subtotal'] * ($taxRate / 100);
        $row['total'] = $row['subtotal'] + $row['tax'];
        $row['balance'] = $row['total'] - $row['paid_amount'];
    }
    jsonResponse($list);
}

$validStatuses = ['draft', 'unpaid', 'paid', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $status = trim($input['status'] ?? $_GET['status'] ?? '');
    $status = in_array($status, $validStatuses) ? $status : 'draft';
    $clientId = (int)($input['client_id'] ?? 0);
    if (!$clientId) jsonResponse(['error' => 'Client required'], 400);
    $items = $input['items'] ?? [];
    if (empty($items)) jsonResponse(['error' => 'At least one item required'], 400);

    $invoiceNumber = $input['invoice_number'] ?? getNextInvoiceNumber();
    $issueDate = $input['issue_date'] ?? date('Y-m-d');
    $termsId = !empty($input['payment_terms_id']) ? (int)$input['payment_terms_id'] : null;
    $days = 0;
    if ($termsId) {
        $stmt = $pdo->prepare("SELECT days FROM payment_terms WHERE id = ?");
        $stmt->execute([$termsId]);
        $days = (int)($stmt->fetch()['days'] ?? 0);
    }
    $dueDate = $input['due_date'] ?? date('Y-m-d', strtotime("+$days days"));

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("
            INSERT INTO invoices (invoice_number, client_id, status, payment_type, payment_terms_id, issue_date, due_date, notes, terms_conditions, template_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $invoiceNumber,
            $clientId,
            $status,
            $input['payment_type'] ?? 'full',
            $termsId,
            $issueDate,
            $dueDate,
            $input['notes'] ?? '',
            $input['terms_conditions'] ?? '',
            (int)($input['template_id'] ?? 1)
        ]);
        $invoiceId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, amount, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($items as $i => $item) {
            $qty = (float)($item['quantity'] ?? 1);
            $price = (float)($item['unit_price'] ?? 0);
            $amount = $qty * $price;
            $stmt->execute([
                $invoiceId,
                $item['description'] ?? '',
                $qty,
                $price,
                $amount,
                $i
            ]);
        }
        $payments = $input['payments'] ?? [];
        if (!empty($payments)) {
            $stmt = $pdo->prepare("INSERT INTO invoice_payments (invoice_id, amount, payment_date, notes) VALUES (?, ?, ?, ?)");
            foreach ($payments as $p) {
                $stmt->execute([$invoiceId, (float)$p['amount'], $p['payment_date'] ?? date('Y-m-d'), $p['notes'] ?? '']);
            }
        }
        $pdo->commit();
        $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
        $stmt->execute([$invoiceId]);
        jsonResponse($stmt->fetch(), 201);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['error' => $e->getMessage()], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $id = (int)($input['id'] ?? $_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'ID required'], 400);

    $termsId = !empty($input['payment_terms_id']) ? (int)$input['payment_terms_id'] : null;
    $status = trim($input['status'] ?? $_GET['status'] ?? '');
    $status = in_array($status, $validStatuses) ? $status : 'draft';

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("
            UPDATE invoices SET
                client_id=?, status=?, payment_type=?, payment_terms_id=?, issue_date=?, due_date=?,
                notes=?, terms_conditions=?, template_id=?
            WHERE id=?
        ");
        $stmt->execute([
            (int)$input['client_id'],
            $status,
            $input['payment_type'] ?? 'full',
            $termsId,
            $input['issue_date'] ?? date('Y-m-d'),
            $input['due_date'] ?? date('Y-m-d'),
            $input['notes'] ?? '',
            $input['terms_conditions'] ?? '',
            (int)($input['template_id'] ?? 1),
            $id
        ]);
        if (isset($input['items'])) {
            $pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([$id]);
            $stmt = $pdo->prepare("INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, amount, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($input['items'] as $i => $item) {
                $qty = (float)($item['quantity'] ?? 1);
                $price = (float)($item['unit_price'] ?? 0);
                $amount = $qty * $price;
                $stmt->execute([$id, $item['description'] ?? '', $qty, $price, $amount, $i]);
            }
        }
        $pdo->commit();
        $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse($stmt->fetch());
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['error' => $e->getMessage()], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['error' => 'ID required'], 400);
    $pdo->prepare("DELETE FROM invoices WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true]);
}
