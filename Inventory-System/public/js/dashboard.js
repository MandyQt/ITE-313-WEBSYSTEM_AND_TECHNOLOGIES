/**
 * Part 7 Step 3: AJAX polling for the dashboard.
 * Module pattern: everything lives inside DashboardPoller, nothing leaks to window.
 */
const DashboardPoller = (function () {
    const root = document.getElementById('realtime-dashboard');
    if (!root) return {};

    const cfg = {
        statsUrl: root.dataset.statsUrl,
        lowStockUrl: root.dataset.lowStockUrl,
        recentUrl: root.dataset.recentUrl,
        interval: parseInt(root.dataset.interval, 10) || 30000,
    };

    const el = {
        total: document.getElementById('stat-total-products'),
        low: document.getElementById('stat-low-stock'),
        out: document.getElementById('stat-out-of-stock'),
        value: document.getElementById('stat-total-value'),
        lowList: document.getElementById('low-stock-list'),
        recentBody: document.getElementById('recent-transactions-body'),
        lastUpdated: document.getElementById('last-updated'),
        error: document.getElementById('poll-error'),
    };

    // ---------- helpers ----------
    const escapeHtml = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => (
        { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
    ));

    const fmtMoney = (n) => Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    async function getJson(url) {
        const res = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!res.ok) throw new Error(`HTTP ${res.status} for ${url}`);
        return res.json();
    }

    /** Count from the current number to the new one over ~600ms, with a quick highlight. */
    function animateNumber(node, to, { money = false } = {}) {
        if (!node) return;
        const from = parseFloat(node.dataset.value ?? node.textContent.replace(/[^0-9.-]/g, '')) || 0;
        node.dataset.value = to;
        if (from === to) { node.textContent = money ? fmtMoney(to) : to; return; }

        const start = performance.now(), duration = 600;
        node.classList.add('text-decoration-underline');
        function frame(now) {
            const t = Math.min((now - start) / duration, 1);
            const v = from + (to - from) * t;
            node.textContent = money ? fmtMoney(v) : Math.round(v);
            if (t < 1) requestAnimationFrame(frame);
            else node.classList.remove('text-decoration-underline');
        }
        requestAnimationFrame(frame);
    }

    // ---------- renderers ----------
    function renderStats(s) {
        animateNumber(el.total, s.total_products);
        animateNumber(el.low, s.low_stock_count);
        animateNumber(el.out, s.out_of_stock_count);
        animateNumber(el.value, s.total_value, { money: true });
        // Let other scripts (e.g. the Chart.js doughnut) react to fresh stats.
        document.dispatchEvent(new CustomEvent('dashboard:stats', { detail: s }));
    }

    function renderLowStock(products) {
        if (!products.length) {
            el.lowList.innerHTML = '<li class="list-group-item text-success">All products are sufficiently stocked 🎉</li>';
            return;
        }
        el.lowList.innerHTML = products.map((p) => `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <a href="${escapeHtml(p.url)}" class="fw-semibold text-decoration-none">${escapeHtml(p.name)}</a>
                    <div class="small text-muted">${escapeHtml(p.sku)} · reorder at ${p.reorder_level}</div>
                </div>
                <span class="badge text-bg-${escapeHtml(p.status_color)}">${p.quantity} left · ${escapeHtml(p.status)}</span>
            </li>`).join('');
    }

    function renderRecent(items) {
        if (!items.length) {
            el.recentBody.innerHTML = '<tr><td colspan="5" class="text-muted">No transactions yet.</td></tr>';
            return;
        }
        const badge = { stock_in: 'success', stock_out: 'danger', adjustment: 'info' };
        el.recentBody.innerHTML = items.map((t) => {
            const sign = t.quantity > 0 ? '+' : '';
            return `<tr>
                <td class="text-nowrap">${escapeHtml(t.created_human)}</td>
                <td><a href="${escapeHtml(t.product?.url ?? '#')}">${escapeHtml(t.product?.name ?? '—')}</a></td>
                <td><span class="badge text-bg-${badge[t.type] ?? 'secondary'}">${escapeHtml(t.type.replace('_', ' '))}</span></td>
                <td class="text-end ${t.quantity > 0 ? 'text-success' : 'text-danger'}">${sign}${t.quantity}</td>
                <td>${escapeHtml(t.user?.name ?? '—')}</td>
            </tr>`;
        }).join('');
    }

    // ---------- polling ----------
    async function refresh() {
        try {
            const [stats, low, recent] = await Promise.all([
                getJson(cfg.statsUrl),
                getJson(cfg.lowStockUrl),
                getJson(cfg.recentUrl),
            ]);
            renderStats(stats.data);
            renderLowStock(low.data);
            renderRecent(recent.data);
            el.lastUpdated.textContent = new Date().toLocaleTimeString();
            el.error.classList.add('d-none');
        } catch (err) {
            console.error('Dashboard refresh failed:', err);
            el.error.textContent = 'Connection problem (' + err.message + ') – retrying…';
            el.error.classList.remove('d-none');   // keep old data on screen, try again next tick
        }
    }

    function start() {
        refresh();
        setInterval(refresh, cfg.interval);
        // Refresh immediately when the user returns to the tab.
        document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
    }

    // Start now if the page is already parsed, otherwise wait for it.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
    return { refresh };
})();
