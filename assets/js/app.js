// Webspace Invoice System - Shared JS
const API_BASE = (document.querySelector('meta[name="api-base"]')?.content || window.location.origin + '/webspace-invoice-system') + '/api';

function formatMoney(n, currency = 'NGN') {
    if (n == null || isNaN(n)) return '-';
    return currency + ' ' + Number(n).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('en-NG', { year: 'numeric', month: 'short', day: 'numeric' });
}

async function api(method, path, data = null) {
    const opts = { method, headers: {} };
    if (data && (method === 'POST' || method === 'PUT')) {
        opts.headers['Content-Type'] = 'application/json';
        opts.body = JSON.stringify(data);
    }
    const res = await fetch((path.startsWith('http') ? path : API_BASE + path), opts);
    const json = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(json.error || 'Request failed');
    return json;
}

// Sidebar toggle for mobile
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
    }
});
