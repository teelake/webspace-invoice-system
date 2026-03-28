-- Public landing page content (managed by platform admins)

CREATE TABLE IF NOT EXISTS landing_settings (
    id INT PRIMARY KEY DEFAULT 1,
    hero_eyebrow VARCHAR(255) DEFAULT 'NIGERIA · SMES · ENTERPRISES',
    hero_title_green VARCHAR(255) DEFAULT 'NRS-compliant',
    hero_title_dark VARCHAR(255) DEFAULT 'invoice software',
    hero_subtitle_html MEDIUMTEXT,
    hero_microcopy VARCHAR(500) DEFAULT 'Finish setup after you sign up—get working in minutes.',
    hero_primary_cta_label VARCHAR(100) DEFAULT 'Start free',
    hero_primary_cta_url VARCHAR(500) DEFAULT '#pricing',
    hero_secondary_cta_label VARCHAR(100) DEFAULT 'Log in',
    content_rich_html MEDIUMTEXT,
    section_plans_title VARCHAR(255) DEFAULT 'Simple pricing',
    section_faq_title VARCHAR(255) DEFAULT 'Frequently asked questions',
    section_trusted_title VARCHAR(255) DEFAULT 'Trusted by teams like yours',
    section_testimonials_title VARCHAR(255) DEFAULT 'What customers say',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT IGNORE INTO landing_settings (id) VALUES (1);

CREATE TABLE IF NOT EXISTS landing_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    badge_label VARCHAR(100) DEFAULT NULL,
    price_line VARCHAR(255) DEFAULT '',
    description_html MEDIUMTEXT,
    cta_label VARCHAR(100) DEFAULT 'Get started',
    cta_url VARCHAR(500) DEFAULT '#',
    sort_order INT NOT NULL DEFAULT 0,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS landing_plan_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_id INT NOT NULL,
    feature_text VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (plan_id) REFERENCES landing_plans(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS landing_faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(500) NOT NULL,
    answer_html MEDIUMTEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS landing_trusted_logos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL DEFAULT '',
    logo_url VARCHAR(500) NOT NULL,
    website_url VARCHAR(500) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS landing_testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quote_html MEDIUMTEXT NOT NULL,
    author_name VARCHAR(255) NOT NULL,
    author_role VARCHAR(255) DEFAULT '',
    author_image_url VARCHAR(500) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    active TINYINT(1) NOT NULL DEFAULT 1
);
