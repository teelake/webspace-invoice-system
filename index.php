<?php
require_once __DIR__ . '/config/app.php';

$dbOk = file_exists(__DIR__ . '/config/database.php');
if ($dbOk) {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/includes/functions.php';
    require_once __DIR__ . '/includes/landing-data.php';
    if (!empty($_SESSION['user_id'])) {
        require_once __DIR__ . '/includes/auth.php';
        redirectAfterLogin();
    }
    $LD = getLandingPageData();
} else {
    header('Location: ' . APP_URL . '/setup');
    exit;
}

$S = $LD['settings'];
$loginUrl = APP_URL . '/login';
$secondaryUrl = $loginUrl;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME) ?> — <?= htmlspecialchars($S['hero_title_green'] . ' ' . $S['hero_title_dark']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/landing.css">
</head>
<body class="landing-body">
<header class="landing-header">
    <a href="<?= APP_URL ?>/" class="landing-logo"><img src="<?= APP_URL ?>/assets/images/logo.png" alt=""></a>
    <nav class="landing-header-nav">
        <a href="#pricing">Pricing</a>
        <a href="#faq">FAQ</a>
        <a href="<?= htmlspecialchars($loginUrl) ?>" class="landing-header-login">Log in</a>
    </nav>
</header>

<section class="landing-hero">
    <div class="landing-hero-inner">
        <div class="landing-hero-copy">
            <p class="landing-eyebrow"><?= htmlspecialchars($S['hero_eyebrow']) ?></p>
            <h1 class="landing-headline">
                <span class="landing-headline-green"><?= htmlspecialchars($S['hero_title_green']) ?></span>
                <span class="landing-headline-dark"><?= htmlspecialchars($S['hero_title_dark']) ?></span>
            </h1>
            <div class="landing-subtitle landing-prose"><?= sanitizeRichHtml($S['hero_subtitle_html'] ?? '') ?></div>
            <div class="landing-cta-row">
                <a class="landing-btn landing-btn-primary" href="<?= htmlspecialchars($S['hero_primary_cta_url']) ?>"><?= htmlspecialchars($S['hero_primary_cta_label']) ?></a>
                <a class="landing-btn landing-btn-outline" href="<?= htmlspecialchars($secondaryUrl) ?>"><?= htmlspecialchars($S['hero_secondary_cta_label']) ?></a>
            </div>
            <p class="landing-microcopy"><?= htmlspecialchars($S['hero_microcopy']) ?></p>
        </div>
        <div class="landing-hero-visual" aria-hidden="true">
            <div class="landing-illustration">
                <div class="landing-illustration-glow"></div>
                <div class="landing-invoice-card">
                    <div class="landing-invoice-card-top">
                        <span class="landing-invoice-logo"></span>
                        <span class="landing-invoice-badge">Paid</span>
                    </div>
                    <div class="landing-invoice-lines">
                        <span class="landing-line landing-line-1"></span>
                        <span class="landing-line landing-line-2"></span>
                        <span class="landing-line landing-line-3"></span>
                    </div>
                    <span class="landing-invoice-total"></span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (trim(strip_tags(sanitizeRichHtml($S['content_rich_html'] ?? ''))) !== ''): ?>
<section class="landing-section landing-rich-block">
    <div class="landing-container landing-prose"><?= sanitizeRichHtml($S['content_rich_html'] ?? '') ?></div>
</section>
<?php endif; ?>

<section class="landing-section landing-trusted" id="trusted">
    <div class="landing-container">
        <h2 class="landing-section-title"><?= htmlspecialchars($S['section_trusted_title']) ?></h2>
        <?php if (empty($LD['trusted_logos'])): ?>
        <p class="landing-muted landing-center">Logos appear here once added in the platform admin.</p>
        <?php else: ?>
        <ul class="landing-logo-row">
            <?php foreach ($LD['trusted_logos'] as $logo): ?>
            <li>
                <?php if (!empty($logo['website_url'])): ?>
                <a href="<?= htmlspecialchars($logo['website_url']) ?>" target="_blank" rel="noopener noreferrer" class="landing-trusted-link">
                    <img src="<?= htmlspecialchars($logo['logo_url']) ?>" alt="<?= htmlspecialchars($logo['name'] ?: 'Partner') ?>" loading="lazy">
                </a>
                <?php else: ?>
                <span class="landing-trusted-link"><img src="<?= htmlspecialchars($logo['logo_url']) ?>" alt="<?= htmlspecialchars($logo['name'] ?: 'Partner') ?>" loading="lazy"></span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</section>

<section class="landing-section landing-pricing" id="pricing">
    <div class="landing-container">
        <h2 class="landing-section-title"><?= htmlspecialchars($S['section_plans_title']) ?></h2>
        <div class="landing-plans-grid">
            <?php foreach ($LD['plans'] as $plan): ?>
            <article class="landing-plan-card<?= !empty($plan['is_featured']) ? ' landing-plan-featured' : '' ?>">
                <?php if (!empty($plan['badge_label'])): ?>
                <span class="landing-plan-badge"><?= htmlspecialchars($plan['badge_label']) ?></span>
                <?php endif; ?>
                <h3 class="landing-plan-name"><?= htmlspecialchars($plan['name']) ?></h3>
                <p class="landing-plan-price"><?= htmlspecialchars($plan['price_line']) ?></p>
                <div class="landing-plan-desc landing-prose"><?= sanitizeRichHtml($plan['description_html'] ?? '') ?></div>
                <ul class="landing-plan-features">
                    <?php foreach ($plan['features'] ?? [] as $f): ?>
                    <li><?= htmlspecialchars($f) ?></li>
                    <?php endforeach; ?>
                </ul>
                <a class="landing-btn landing-btn-primary landing-plan-cta" href="<?= htmlspecialchars($plan['cta_url'] ?: '#') ?>"><?= htmlspecialchars($plan['cta_label'] ?: 'Get started') ?></a>
            </article>
            <?php endforeach; ?>
        </div>
        <?php if (empty($LD['plans'])): ?>
        <p class="landing-muted landing-center">Pricing plans will appear here when configured in the admin.</p>
        <?php endif; ?>
    </div>
</section>

<section class="landing-section landing-testimonials" id="testimonials">
    <div class="landing-container">
        <h2 class="landing-section-title"><?= htmlspecialchars($S['section_testimonials_title']) ?></h2>
        <?php if (empty($LD['testimonials'])): ?>
        <p class="landing-muted landing-center">Testimonials will appear here once added in the admin.</p>
        <?php else: ?>
        <div class="landing-testimonial-grid">
            <?php foreach ($LD['testimonials'] as $t): ?>
            <blockquote class="landing-testimonial-card">
                <div class="landing-quote landing-prose"><?= sanitizeRichHtml($t['quote_html'] ?? '') ?></div>
                <footer class="landing-testimonial-meta">
                    <?php if (!empty($t['author_image_url'])): ?>
                    <img src="<?= htmlspecialchars($t['author_image_url']) ?>" alt="" class="landing-testimonial-avatar" loading="lazy">
                    <?php endif; ?>
                    <div>
                        <cite><?= htmlspecialchars($t['author_name']) ?></cite>
                        <?php if (!empty($t['author_role'])): ?><span><?= htmlspecialchars($t['author_role']) ?></span><?php endif; ?>
                    </div>
                </footer>
            </blockquote>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="landing-section landing-faq" id="faq">
    <div class="landing-container landing-faq-narrow">
        <h2 class="landing-section-title"><?= htmlspecialchars($S['section_faq_title']) ?></h2>
        <?php if (empty($LD['faqs'])): ?>
        <p class="landing-muted landing-center">FAQs will appear here when added in the admin.</p>
        <?php else: ?>
        <div class="landing-faq-list">
            <?php foreach ($LD['faqs'] as $i => $faq): ?>
            <details class="landing-faq-item"<?= $i === 0 ? ' open' : '' ?>>
                <summary><?= htmlspecialchars($faq['question']) ?></summary>
                <div class="landing-faq-answer landing-prose"><?= sanitizeRichHtml($faq['answer_html'] ?? '') ?></div>
            </details>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<footer class="landing-footer">
    <div class="landing-container landing-footer-inner">
        <span>&copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?></span>
        <a href="<?= htmlspecialchars($loginUrl) ?>">Log in</a>
    </div>
</footer>
</body>
</html>
