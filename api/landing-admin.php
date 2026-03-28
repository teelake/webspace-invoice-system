<?php
require_once __DIR__ . '/init-platform.php';
require_once __DIR__ . '/../includes/landing-data.php';

$pdo = getDB();
if (!landingTablesExist($pdo)) {
    jsonResponse(['error' => 'Landing tables missing. Run database migration 006_landing_cms.sql'], 503);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    jsonResponse(getLandingAdminData());
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) {
    jsonResponse(['error' => 'Invalid JSON'], 400);
}

$section = $body['section'] ?? '';
$payload = $body['payload'] ?? null;

try {
    if ($section === 'settings' && is_array($payload)) {
        $heroSub = sanitizeRichHtml($payload['hero_subtitle_html'] ?? '');
        $contentRich = sanitizeRichHtml($payload['content_rich_html'] ?? '');
        $sql = 'UPDATE landing_settings SET
            hero_eyebrow = ?, hero_title_green = ?, hero_title_dark = ?,
            hero_subtitle_html = ?, hero_microcopy = ?,
            hero_primary_cta_label = ?, hero_primary_cta_url = ?, hero_secondary_cta_label = ?,
            content_rich_html = ?,
            section_plans_title = ?, section_faq_title = ?, section_trusted_title = ?, section_testimonials_title = ?
            WHERE id = 1';
        $pdo->prepare($sql)->execute([
            substr(trim($payload['hero_eyebrow'] ?? ''), 0, 255),
            substr(trim($payload['hero_title_green'] ?? ''), 0, 255),
            substr(trim($payload['hero_title_dark'] ?? ''), 0, 255),
            $heroSub,
            substr(trim($payload['hero_microcopy'] ?? ''), 0, 500),
            substr(trim($payload['hero_primary_cta_label'] ?? ''), 0, 100),
            substr(trim($payload['hero_primary_cta_url'] ?? ''), 0, 500),
            substr(trim($payload['hero_secondary_cta_label'] ?? ''), 0, 100),
            $contentRich,
            substr(trim($payload['section_plans_title'] ?? ''), 0, 255),
            substr(trim($payload['section_faq_title'] ?? ''), 0, 255),
            substr(trim($payload['section_trusted_title'] ?? ''), 0, 255),
            substr(trim($payload['section_testimonials_title'] ?? ''), 0, 255),
        ]);
        jsonResponse(['ok' => true]);
    }

    if ($section === 'plans' && is_array($payload)) {
        $pdo->beginTransaction();
        $pdo->exec('DELETE FROM landing_plan_features');
        $pdo->exec('DELETE FROM landing_plans');
        $insP = $pdo->prepare('INSERT INTO landing_plans (name, badge_label, price_line, description_html, cta_label, cta_url, sort_order, is_featured, active) VALUES (?,?,?,?,?,?,?,?,?)');
        $insF = $pdo->prepare('INSERT INTO landing_plan_features (plan_id, feature_text, sort_order) VALUES (?,?,?)');
        foreach ($payload as $i => $plan) {
            if (!is_array($plan)) {
                continue;
            }
            $name = trim($plan['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $insP->execute([
                substr($name, 0, 255),
                $plan['badge_label'] !== null && $plan['badge_label'] !== '' ? substr(trim($plan['badge_label']), 0, 100) : null,
                substr(trim($plan['price_line'] ?? ''), 0, 255),
                sanitizeRichHtml($plan['description_html'] ?? ''),
                substr(trim($plan['cta_label'] ?? 'Get started'), 0, 100),
                substr(trim($plan['cta_url'] ?? '#'), 0, 500),
                (int)($plan['sort_order'] ?? $i),
                !empty($plan['is_featured']) ? 1 : 0,
                isset($plan['active']) && !$plan['active'] ? 0 : 1,
            ]);
            $pid = (int)$pdo->lastInsertId();
            $feats = $plan['features'] ?? [];
            if (is_array($feats)) {
                foreach ($feats as $fi => $ft) {
                    $t = trim(is_string($ft) ? $ft : '');
                    if ($t === '') {
                        continue;
                    }
                    $insF->execute([$pid, substr($t, 0, 500), $fi]);
                }
            }
        }
        $pdo->commit();
        jsonResponse(['ok' => true]);
    }

    if ($section === 'faqs' && is_array($payload)) {
        $pdo->beginTransaction();
        $pdo->exec('DELETE FROM landing_faqs');
        $ins = $pdo->prepare('INSERT INTO landing_faqs (question, answer_html, sort_order, active) VALUES (?,?,?,?)');
        foreach ($payload as $i => $row) {
            if (!is_array($row)) {
                continue;
            }
            $q = trim($row['question'] ?? '');
            if ($q === '') {
                continue;
            }
            $ins->execute([
                substr($q, 0, 500),
                sanitizeRichHtml($row['answer_html'] ?? ''),
                (int)($row['sort_order'] ?? $i),
                isset($row['active']) && !$row['active'] ? 0 : 1,
            ]);
        }
        $pdo->commit();
        jsonResponse(['ok' => true]);
    }

    if ($section === 'trusted_logos' && is_array($payload)) {
        $pdo->beginTransaction();
        $pdo->exec('DELETE FROM landing_trusted_logos');
        $ins = $pdo->prepare('INSERT INTO landing_trusted_logos (name, logo_url, website_url, sort_order, active) VALUES (?,?,?,?,?)');
        foreach ($payload as $i => $row) {
            if (!is_array($row)) {
                continue;
            }
            $url = trim($row['logo_url'] ?? '');
            if ($url === '') {
                continue;
            }
            $ins->execute([
                substr(trim($row['name'] ?? ''), 0, 255),
                substr($url, 0, 500),
                $row['website_url'] !== null && trim((string)$row['website_url']) !== '' ? substr(trim($row['website_url']), 0, 500) : null,
                (int)($row['sort_order'] ?? $i),
                isset($row['active']) && !$row['active'] ? 0 : 1,
            ]);
        }
        $pdo->commit();
        jsonResponse(['ok' => true]);
    }

    if ($section === 'testimonials' && is_array($payload)) {
        $pdo->beginTransaction();
        $pdo->exec('DELETE FROM landing_testimonials');
        $ins = $pdo->prepare('INSERT INTO landing_testimonials (quote_html, author_name, author_role, author_image_url, sort_order, active) VALUES (?,?,?,?,?,?)');
        foreach ($payload as $i => $row) {
            if (!is_array($row)) {
                continue;
            }
            $quote = sanitizeRichHtml($row['quote_html'] ?? '');
            if (trim(strip_tags($quote)) === '') {
                continue;
            }
            $author = trim($row['author_name'] ?? '');
            if ($author === '') {
                continue;
            }
            $img = trim($row['author_image_url'] ?? '');
            $ins->execute([
                $quote,
                substr($author, 0, 255),
                substr(trim($row['author_role'] ?? ''), 0, 255),
                $img !== '' ? substr($img, 0, 500) : null,
                (int)($row['sort_order'] ?? $i),
                isset($row['active']) && !$row['active'] ? 0 : 1,
            ]);
        }
        $pdo->commit();
        jsonResponse(['ok' => true]);
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(['error' => 'Save failed'], 500);
}

jsonResponse(['error' => 'Unknown section'], 400);
