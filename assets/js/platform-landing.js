(function () {
    const base = (document.querySelector('meta[name="api-base"]') || {}).content || '';
    const qToolbar = [['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['link'], ['clean']];

    let data = null;
    let quillHero = null;
    let quillContent = null;
    const faqQuills = [];
    const testQuills = [];
    const planQuills = [];

    function toast(msg, type) {
        if (typeof showToast === 'function') showToast(msg, type || 'success');
        else alert(msg);
    }

    function tabSwitch(name) {
        document.querySelectorAll('.admin-tab-panel').forEach(function (p) {
            p.classList.toggle('active', p.getAttribute('data-tab-panel') === name);
        });
        document.querySelectorAll('.admin-tabs-nav button').forEach(function (b) {
            b.classList.toggle('active', b.getAttribute('data-tab') === name);
        });
    }

    function makeQuill(el) {
        if (!el || typeof Quill === 'undefined') return null;
        return new Quill(el, { theme: 'snow', modules: { toolbar: qToolbar } });
    }

    function loadSettingsForm() {
        const s = data.settings;
        document.getElementById('f_eyebrow').value = s.hero_eyebrow || '';
        document.getElementById('f_title_g').value = s.hero_title_green || '';
        document.getElementById('f_title_d').value = s.hero_title_dark || '';
        document.getElementById('f_micro').value = s.hero_microcopy || '';
        document.getElementById('f_pcta').value = s.hero_primary_cta_label || '';
        document.getElementById('f_purl').value = s.hero_primary_cta_url || '';
        document.getElementById('f_scta').value = s.hero_secondary_cta_label || '';
        document.getElementById('f_sec_plans').value = s.section_plans_title || '';
        document.getElementById('f_sec_faq').value = s.section_faq_title || '';
        document.getElementById('f_sec_trust').value = s.section_trusted_title || '';
        document.getElementById('f_sec_test').value = s.section_testimonials_title || '';

        if (!quillHero) quillHero = makeQuill(document.getElementById('editorHeroSub'));
        if (!quillContent) quillContent = makeQuill(document.getElementById('editorContent'));
        if (quillHero) quillHero.root.innerHTML = s.hero_subtitle_html || '';
        if (quillContent) quillContent.root.innerHTML = s.content_rich_html || '';
    }

    function renderPlans() {
        const wrap = document.getElementById('plansMount');
        wrap.innerHTML = '';
        planQuills.length = 0;
        (data.plans || []).forEach(function (p, i) {
            const card = document.createElement('div');
            card.className = 'landing-repeat-card';
            card.innerHTML =
                '<h4>Plan ' + (i + 1) + '</h4>' +
                '<div class="landing-field-grid">' +
                '<div class="form-group"><label>Name</label><input type="text" class="inp-name" value="' + escAttr(p.name || '') + '"></div>' +
                '<div class="form-group"><label>Badge (e.g. Popular)</label><input type="text" class="inp-badge" value="' + escAttr(p.badge_label || '') + '"></div>' +
                '<div class="form-group"><label>Price line</label><input type="text" class="inp-price" value="' + escAttr(p.price_line || '') + '"></div>' +
                '<div class="form-group"><label>CTA label</label><input type="text" class="inp-cta" value="' + escAttr(p.cta_label || '') + '"></div>' +
                '<div class="form-group"><label>CTA URL</label><input type="text" class="inp-curl" value="' + escAttr(p.cta_url || '') + '"></div>' +
                '<div class="form-group"><label>Sort order</label><input type="number" class="inp-sort" value="' + (p.sort_order != null ? p.sort_order : i) + '"></div>' +
                '</div>' +
                '<div class="form-group"><label>Description</label><div class="landing-quill-wrap"><div class="editor-plan-desc" data-plan-desc></div></div></div>' +
                '<div class="form-group"><label>Features (one per line)</label><textarea class="inp-feats" rows="5"></textarea></div>' +
                '<label class="form-group" style="display:flex;align-items:center;gap:0.5rem;"><input type="checkbox" class="inp-feat"' + (p.is_featured == 1 ? ' checked' : '') + '> Featured plan</label> ' +
                '<label class="form-group" style="display:flex;align-items:center;gap:0.5rem;"><input type="checkbox" class="inp-act"' + (p.active != 0 ? ' checked' : '') + '> Active</label>';
            wrap.appendChild(card);
            card.querySelector('.inp-feats').value = (p.features || []).join('\n');
            const ed = card.querySelector('.editor-plan-desc');
            const q = makeQuill(ed);
            if (q) q.root.innerHTML = p.description_html || '';
            planQuills.push(q);
        });
    }

    function renderFaqs() {
        const wrap = document.getElementById('faqMount');
        wrap.innerHTML = '';
        faqQuills.length = 0;
        (data.faqs || []).forEach(function (f, i) {
            const card = document.createElement('div');
            card.className = 'landing-repeat-card';
            card.innerHTML =
                '<h4>FAQ ' + (i + 1) + '</h4>' +
                '<div class="form-group"><label>Question</label><input type="text" class="fq-q" value="' + escAttr(f.question || '') + '"></div>' +
                '<div class="form-group"><label>Answer</label><div class="landing-quill-wrap"><div class="ed-faq"></div></div></div>' +
                '<label class="form-group" style="display:flex;align-items:center;gap:0.5rem;"><input type="checkbox" class="fq-a"' + (f.active != 0 ? ' checked' : '') + '> Active</label>';
            wrap.appendChild(card);
            const q = makeQuill(card.querySelector('.ed-faq'));
            if (q) q.root.innerHTML = f.answer_html || '';
            faqQuills.push(q);
        });
    }

    function renderLogos() {
        const wrap = document.getElementById('logoMount');
        wrap.innerHTML = '';
        (data.trusted_logos || []).forEach(function (L, i) {
            const card = document.createElement('div');
            card.className = 'landing-repeat-card';
            card.innerHTML =
                '<h4>Logo ' + (i + 1) + '</h4>' +
                '<div class="landing-field-grid">' +
                '<div class="form-group"><label>Name / alt text</label><input type="text" class="lg-name" value="' + escAttr(L.name || '') + '"></div>' +
                '<div class="form-group"><label>Logo image URL</label><input type="text" class="lg-url" value="' + escAttr(L.logo_url || '') + '"></div>' +
                '<div class="form-group"><label>Website URL (optional)</label><input type="text" class="lg-web" value="' + escAttr(L.website_url || '') + '"></div>' +
                '<div class="form-group"><label>Sort</label><input type="number" class="lg-sort" value="' + (L.sort_order != null ? L.sort_order : i) + '"></div>' +
                '</div>' +
                '<label class="form-group" style="display:flex;align-items:center;gap:0.5rem;"><input type="checkbox" class="lg-act"' + (L.active != 0 ? ' checked' : '') + '> Active</label>';
            wrap.appendChild(card);
        });
    }

    function renderTests() {
        const wrap = document.getElementById('testMount');
        wrap.innerHTML = '';
        testQuills.length = 0;
        (data.testimonials || []).forEach(function (t, i) {
            const card = document.createElement('div');
            card.className = 'landing-repeat-card';
            card.innerHTML =
                '<h4>Testimonial ' + (i + 1) + '</h4>' +
                '<div class="form-group"><label>Quote</label><div class="landing-quill-wrap"><div class="ed-test"></div></div></div>' +
                '<div class="landing-field-grid">' +
                '<div class="form-group"><label>Author name</label><input type="text" class="ts-name" value="' + escAttr(t.author_name || '') + '"></div>' +
                '<div class="form-group"><label>Role / company</label><input type="text" class="ts-role" value="' + escAttr(t.author_role || '') + '"></div>' +
                '<div class="form-group"><label>Photo URL</label><input type="text" class="ts-img" value="' + escAttr(t.author_image_url || '') + '"></div>' +
                '<div class="form-group"><label>Sort</label><input type="number" class="ts-sort" value="' + (t.sort_order != null ? t.sort_order : i) + '"></div>' +
                '</div>' +
                '<label class="form-group" style="display:flex;align-items:center;gap:0.5rem;"><input type="checkbox" class="ts-act"' + (t.active != 0 ? ' checked' : '') + '> Active</label>';
            wrap.appendChild(card);
            const q = makeQuill(card.querySelector('.ed-test'));
            if (q) q.root.innerHTML = t.quote_html || '';
            testQuills.push(q);
        });
    }

    function escAttr(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    async function postSection(section, payload) {
        const res = await fetch(base + '/landing-admin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ section: section, payload: payload }),
        });
        const body = await res.json().catch(function () {
            return {};
        });
        if (!res.ok) throw new Error(body.error || 'Save failed');
    }

    function collectPlans() {
        const cards = document.querySelectorAll('#plansMount .landing-repeat-card');
        const out = [];
        cards.forEach(function (card, i) {
            const feats = (card.querySelector('.inp-feats').value || '')
                .split('\n')
                .map(function (x) {
                    return x.trim();
                })
                .filter(Boolean);
            const q = planQuills[i];
            out.push({
                name: card.querySelector('.inp-name').value.trim(),
                badge_label: card.querySelector('.inp-badge').value.trim() || null,
                price_line: card.querySelector('.inp-price').value.trim(),
                description_html: q ? q.root.innerHTML : '',
                cta_label: card.querySelector('.inp-cta').value.trim(),
                cta_url: card.querySelector('.inp-curl').value.trim(),
                sort_order: parseInt(card.querySelector('.inp-sort').value, 10) || i,
                is_featured: card.querySelector('.inp-feat').checked,
                active: card.querySelector('.inp-act').checked,
                features: feats,
            });
        });
        return out;
    }

    function collectFaqs() {
        const cards = document.querySelectorAll('#faqMount .landing-repeat-card');
        const out = [];
        cards.forEach(function (card, i) {
            out.push({
                question: card.querySelector('.fq-q').value.trim(),
                answer_html: faqQuills[i] ? faqQuills[i].root.innerHTML : '',
                sort_order: i,
                active: card.querySelector('.fq-a').checked,
            });
        });
        return out;
    }

    function collectLogos() {
        const cards = document.querySelectorAll('#logoMount .landing-repeat-card');
        const out = [];
        cards.forEach(function (card, i) {
            out.push({
                name: card.querySelector('.lg-name').value.trim(),
                logo_url: card.querySelector('.lg-url').value.trim(),
                website_url: card.querySelector('.lg-web').value.trim() || null,
                sort_order: parseInt(card.querySelector('.lg-sort').value, 10) || i,
                active: card.querySelector('.lg-act').checked,
            });
        });
        return out;
    }

    function collectTests() {
        const cards = document.querySelectorAll('#testMount .landing-repeat-card');
        const out = [];
        cards.forEach(function (card, i) {
            out.push({
                quote_html: testQuills[i] ? testQuills[i].root.innerHTML : '',
                author_name: card.querySelector('.ts-name').value.trim(),
                author_role: card.querySelector('.ts-role').value.trim(),
                author_image_url: card.querySelector('.ts-img').value.trim() || null,
                sort_order: parseInt(card.querySelector('.ts-sort').value, 10) || i,
                active: card.querySelector('.ts-act').checked,
            });
        });
        return out;
    }

    document.getElementById('landTabs').addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-tab]');
        if (!btn) return;
        tabSwitch(btn.getAttribute('data-tab'));
    });

    document.getElementById('btnSaveHero').addEventListener('click', async function () {
        try {
            await postSection('settings', {
                hero_eyebrow: document.getElementById('f_eyebrow').value,
                hero_title_green: document.getElementById('f_title_g').value,
                hero_title_dark: document.getElementById('f_title_d').value,
                hero_subtitle_html: quillHero ? quillHero.root.innerHTML : '',
                hero_microcopy: document.getElementById('f_micro').value,
                hero_primary_cta_label: document.getElementById('f_pcta').value,
                hero_primary_cta_url: document.getElementById('f_purl').value,
                hero_secondary_cta_label: document.getElementById('f_scta').value,
                content_rich_html: quillContent ? quillContent.root.innerHTML : '',
                section_plans_title: document.getElementById('f_sec_plans').value,
                section_faq_title: document.getElementById('f_sec_faq').value,
                section_trusted_title: document.getElementById('f_sec_trust').value,
                section_testimonials_title: document.getElementById('f_sec_test').value,
            });
            toast('Hero & sections saved');
        } catch (err) {
            toast(err.message, 'error');
        }
    });

    document.getElementById('btnSavePlans').addEventListener('click', async function () {
        try {
            await postSection('plans', collectPlans());
            toast('Plans saved');
            const r = await fetch(base + '/landing-admin.php');
            data = await r.json();
            renderPlans();
        } catch (err) {
            toast(err.message, 'error');
        }
    });

    document.getElementById('btnAddPlan').addEventListener('click', function () {
        if (!data.plans) data.plans = [];
        data.plans.push({
            name: 'New plan',
            badge_label: null,
            price_line: '',
            description_html: '<p></p>',
            cta_label: 'Get started',
            cta_url: '#',
            sort_order: data.plans.length,
            is_featured: 0,
            active: 1,
            features: [],
        });
        renderPlans();
    });

    document.getElementById('btnSaveFaqs').addEventListener('click', async function () {
        try {
            await postSection('faqs', collectFaqs());
            toast('FAQs saved');
            const r = await fetch(base + '/landing-admin.php');
            data = await r.json();
            renderFaqs();
        } catch (err) {
            toast(err.message, 'error');
        }
    });

    document.getElementById('btnAddFaq').addEventListener('click', function () {
        if (!data.faqs) data.faqs = [];
        data.faqs.push({ question: '', answer_html: '<p></p>', sort_order: data.faqs.length, active: 1 });
        renderFaqs();
    });

    document.getElementById('btnSaveLogos').addEventListener('click', async function () {
        try {
            await postSection('trusted_logos', collectLogos());
            toast('Trusted logos saved');
            const r = await fetch(base + '/landing-admin.php');
            data = await r.json();
            renderLogos();
        } catch (err) {
            toast(err.message, 'error');
        }
    });

    document.getElementById('btnAddLogo').addEventListener('click', function () {
        if (!data.trusted_logos) data.trusted_logos = [];
        data.trusted_logos.push({ name: '', logo_url: '', website_url: null, sort_order: data.trusted_logos.length, active: 1 });
        renderLogos();
    });

    document.getElementById('btnSaveTests').addEventListener('click', async function () {
        try {
            await postSection('testimonials', collectTests());
            toast('Testimonials saved');
            const r = await fetch(base + '/landing-admin.php');
            data = await r.json();
            renderTests();
        } catch (err) {
            toast(err.message, 'error');
        }
    });

    document.getElementById('btnAddTest').addEventListener('click', function () {
        if (!data.testimonials) data.testimonials = [];
        data.testimonials.push({
            quote_html: '<p></p>',
            author_name: '',
            author_role: '',
            author_image_url: null,
            sort_order: data.testimonials.length,
            active: 1,
        });
        renderTests();
    });

    document.addEventListener('DOMContentLoaded', async function () {
        const errEl = document.getElementById('landLoadErr');
        function showErr(msg) {
            errEl.textContent = msg;
            errEl.style.display = 'block';
        }
        try {
            const res = await fetch(base + '/landing-admin.php');
            if (!res.ok) {
                showErr('Could not load landing data. Run migration 006_landing_cms.sql and ensure you are a platform admin.');
                return;
            }
            errEl.style.display = 'none';
            errEl.textContent = '';
            data = await res.json();
            loadSettingsForm();
            renderPlans();
            renderFaqs();
            renderLogos();
            renderTests();
        } catch (e) {
            showErr(e.message || 'Load failed');
        }
    });
})();
