<?php

function landingTablesExist(PDO $pdo) {
    try {
        $pdo->query('SELECT 1 FROM landing_settings LIMIT 1');
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function defaultLandingSettings() {
    return [
        'hero_eyebrow' => 'NIGERIA · SMES · ENTERPRISES',
        'hero_title_green' => 'NRS-compliant',
        'hero_title_dark' => 'invoice software',
        'hero_subtitle_html' => '<p>Create compliant invoices, get paid faster, and give your brand a premium edge—whether you\'re a solo freelancer or a growing company.</p>',
        'hero_microcopy' => 'Finish setup after you sign up—get working in minutes.',
        'hero_primary_cta_label' => 'Start free',
        'hero_primary_cta_url' => '#pricing',
        'hero_secondary_cta_label' => 'Log in',
        'content_rich_html' => '',
        'section_plans_title' => 'Simple pricing',
        'section_faq_title' => 'Frequently asked questions',
        'section_trusted_title' => 'Trusted by teams like yours',
        'section_testimonials_title' => 'What customers say',
    ];
}

function ensureLandingDemoContent(PDO $pdo) {
    $n = (int)$pdo->query('SELECT COUNT(*) FROM landing_plans')->fetchColumn();
    if ($n === 0) {
        $pdo->prepare('INSERT INTO landing_plans (name, badge_label, price_line, description_html, sort_order, is_featured, active) VALUES (?,?,?,?,?,?,?)')
            ->execute(['Starter', null, '₦0 / month', '<p>Perfect for freelancers and sole traders.</p>', 0, 0, 1]);
        $id1 = (int)$pdo->lastInsertId();
        $feats = ['NRS-ready invoice layouts', 'Client management', 'Email invoices'];
        $st = $pdo->prepare('INSERT INTO landing_plan_features (plan_id, feature_text, sort_order) VALUES (?,?,?)');
        foreach ($feats as $i => $t) {
            $st->execute([$id1, $t, $i]);
        }
        $pdo->prepare('INSERT INTO landing_plans (name, badge_label, price_line, description_html, sort_order, is_featured, active) VALUES (?,?,?,?,?,?,?)')
            ->execute(['Business', 'Popular', '₦15,000 / month', '<p>Everything you need as you scale.</p>', 1, 1, 1]);
        $id2 = (int)$pdo->lastInsertId();
        $feats2 = ['Unlimited invoices', 'Installment tracking', 'Custom templates & branding', 'Priority support'];
        foreach ($feats2 as $i => $t) {
            $st->execute([$id2, $t, $i]);
        }
    }
    $fq = (int)$pdo->query('SELECT COUNT(*) FROM landing_faqs')->fetchColumn();
    if ($fq === 0) {
        $pdo->prepare('INSERT INTO landing_faqs (question, answer_html, sort_order, active) VALUES (?,?,?,?)')->execute([
            'Is this NRS-compliant?',
            '<p>We designed templates and fields to align with Nigerian invoicing practice; always confirm with your accountant for your sector.</p>',
            0, 1,
        ]);
        $pdo->prepare('INSERT INTO landing_faqs (question, answer_html, sort_order, active) VALUES (?,?,?,?)')->execute([
            'Can I change plans later?',
            '<p>Yes — upgrade or downgrade when your business needs change.</p>',
            1, 1,
        ]);
    }
}

/**
 * @return array{settings: array, plans: array, faqs: array, trusted_logos: array, testimonials: array}
 */
function getLandingPageData() {
    $defaults = defaultLandingSettings();
    $out = [
        'settings' => $defaults,
        'plans' => [],
        'faqs' => [],
        'trusted_logos' => [],
        'testimonials' => [],
    ];
    try {
        $pdo = getDB();
    } catch (Throwable $e) {
        return $out;
    }
    if (!landingTablesExist($pdo)) {
        return $out;
    }
    ensureLandingDemoContent($pdo);

    $row = $pdo->query('SELECT * FROM landing_settings WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        foreach ($defaults as $k => $v) {
            if (array_key_exists($k, $row) && $row[$k] !== null && $row[$k] !== '') {
                $out['settings'][$k] = $row[$k];
            }
        }
    }
    if (trim(strip_tags($out['settings']['hero_subtitle_html'] ?? '')) === '') {
        $out['settings']['hero_subtitle_html'] = $defaults['hero_subtitle_html'];
    }

    $plans = $pdo->query('SELECT * FROM landing_plans WHERE active = 1 ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($plans as &$p) {
        $st = $pdo->prepare('SELECT feature_text FROM landing_plan_features WHERE plan_id = ? ORDER BY sort_order ASC, id ASC');
        $st->execute([$p['id']]);
        $p['features'] = array_column($st->fetchAll(PDO::FETCH_ASSOC), 'feature_text');
    }
    unset($p);
    $out['plans'] = $plans;

    $out['faqs'] = $pdo->query('SELECT * FROM landing_faqs WHERE active = 1 ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
    $out['trusted_logos'] = $pdo->query('SELECT * FROM landing_trusted_logos WHERE active = 1 ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
    $out['testimonials'] = $pdo->query('SELECT * FROM landing_testimonials WHERE active = 1 ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);

    return $out;
}

function getLandingAdminData() {
    $out = [
        'settings' => defaultLandingSettings(),
        'plans' => [],
        'faqs' => [],
        'trusted_logos' => [],
        'testimonials' => [],
    ];
    try {
        $pdo = getDB();
    } catch (Throwable $e) {
        return $out;
    }
    if (!landingTablesExist($pdo)) {
        return $out;
    }
    ensureLandingDemoContent($pdo);

    $row = $pdo->query('SELECT * FROM landing_settings WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        unset($row['id'], $row['updated_at']);
        $out['settings'] = array_merge(defaultLandingSettings(), $row);
    }

    $plans = $pdo->query('SELECT * FROM landing_plans ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($plans as &$p) {
        $st = $pdo->prepare('SELECT feature_text FROM landing_plan_features WHERE plan_id = ? ORDER BY sort_order ASC, id ASC');
        $st->execute([$p['id']]);
        $p['features'] = array_column($st->fetchAll(PDO::FETCH_ASSOC), 'feature_text');
    }
    unset($p);
    $out['plans'] = $plans;
    $out['faqs'] = $pdo->query('SELECT * FROM landing_faqs ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
    $out['trusted_logos'] = $pdo->query('SELECT * FROM landing_trusted_logos ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
    $out['testimonials'] = $pdo->query('SELECT * FROM landing_testimonials ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);

    return $out;
}
