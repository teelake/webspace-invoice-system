<?php
$currentPage = 'landing';
$pageTitle = 'Landing page';
$pageSubtitle = 'Marketing site — hero, pricing, FAQs, logos, testimonials';
$extraHead = '<link href="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.snow.min.css" rel="stylesheet">';
$extraScripts = [
    'https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js',
    'assets/js/platform-landing.js',
];
require_once __DIR__ . '/includes/layout.php';
?>
<p id="landLoadErr" class="alert alert-error" style="margin-bottom:1rem;display:none" role="alert"></p>

<div class="content-card" style="padding:1.25rem">
    <p class="text-muted" style="margin-bottom:1rem;font-size:0.9rem">
        <strong>Trusted logos</strong> suits customer or brand marks (social proof). Use <strong>Partners</strong> only if you mean formal business partners — the section title is editable below (e.g. rename to “Partners”).
    </p>

    <div class="admin-tabs-nav" id="landTabs">
        <button type="button" data-tab="hero" class="active">Hero &amp; sections</button>
        <button type="button" data-tab="plans">Plans</button>
        <button type="button" data-tab="faq">FAQs</button>
        <button type="button" data-tab="logos">Trusted logos</button>
        <button type="button" data-tab="tests">Testimonials</button>
    </div>

    <div class="admin-tab-panel active" data-tab-panel="hero">
        <div class="landing-field-grid">
            <div class="form-group"><label>Eyebrow</label><input type="text" id="f_eyebrow"></div>
            <div class="form-group"><label>Headline (green part)</label><input type="text" id="f_title_g"></div>
            <div class="form-group"><label>Headline (dark part)</label><input type="text" id="f_title_d"></div>
            <div class="form-group"><label>Microcopy under buttons</label><input type="text" id="f_micro"></div>
            <div class="form-group"><label>Primary CTA label</label><input type="text" id="f_pcta"></div>
            <div class="form-group"><label>Primary CTA URL</label><input type="text" id="f_purl" placeholder="#pricing or https://…"></div>
            <div class="form-group"><label>Secondary CTA label</label><input type="text" id="f_scta"></div>
        </div>
        <div class="form-group">
            <label>Hero subtitle</label>
            <div class="landing-quill-wrap"><div id="editorHeroSub"></div></div>
        </div>
        <div class="form-group">
            <label>Extra content block (optional, between hero and logos)</label>
            <div class="landing-quill-wrap"><div id="editorContent"></div></div>
        </div>
        <hr style="margin:1.25rem 0;border:none;border-top:1px solid var(--border)">
        <p class="text-muted" style="margin-bottom:0.75rem;font-size:0.85rem">Section headings on the public page</p>
        <div class="landing-field-grid">
            <div class="form-group"><label>Pricing section title</label><input type="text" id="f_sec_plans"></div>
            <div class="form-group"><label>FAQ section title</label><input type="text" id="f_sec_faq"></div>
            <div class="form-group"><label>Trusted logos title</label><input type="text" id="f_sec_trust" placeholder="Trusted by… or Partners"></div>
            <div class="form-group"><label>Testimonials title</label><input type="text" id="f_sec_test"></div>
        </div>
        <button type="button" class="btn btn-primary" id="btnSaveHero">Save hero &amp; sections</button>
    </div>

    <div class="admin-tab-panel" data-tab-panel="plans">
        <p class="text-muted" style="margin-bottom:1rem;font-size:0.9rem">Each plan shows its feature list on the public pricing cards.</p>
        <button type="button" class="btn btn-secondary" id="btnAddPlan" style="margin-bottom:1rem">+ Add plan</button>
        <div id="plansMount"></div>
        <button type="button" class="btn btn-primary" id="btnSavePlans">Save plans</button>
    </div>

    <div class="admin-tab-panel" data-tab-panel="faq">
        <button type="button" class="btn btn-secondary" id="btnAddFaq" style="margin-bottom:1rem">+ Add FAQ</button>
        <div id="faqMount"></div>
        <button type="button" class="btn btn-primary" id="btnSaveFaqs">Save FAQs</button>
    </div>

    <div class="admin-tab-panel" data-tab-panel="logos">
        <button type="button" class="btn btn-secondary" id="btnAddLogo" style="margin-bottom:1rem">+ Add logo</button>
        <div id="logoMount"></div>
        <button type="button" class="btn btn-primary" id="btnSaveLogos">Save trusted logos</button>
    </div>

    <div class="admin-tab-panel" data-tab-panel="tests">
        <button type="button" class="btn btn-secondary" id="btnAddTest" style="margin-bottom:1rem">+ Add testimonial</button>
        <div id="testMount"></div>
        <button type="button" class="btn btn-primary" id="btnSaveTests">Save testimonials</button>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout-end.php'; ?>
