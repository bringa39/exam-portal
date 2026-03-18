<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$db->exec("UPDATE visitors SET is_online = 0 WHERE last_activity < datetime('now', '-10 seconds') AND is_online = 1");

$visitorTotal = $db->query("SELECT COUNT(*) as cnt FROM visitors")->fetch()['cnt'];
$visitorOnline = $db->query("SELECT COUNT(*) as cnt FROM visitors WHERE is_online = 1")->fetch()['cnt'];
$visitorRegistered = $db->query("SELECT COUNT(*) as cnt FROM visitors WHERE student_id IS NOT NULL")->fetch()['cnt'];
$visitorDisconnected = $db->query("SELECT COUNT(*) as cnt FROM visitors WHERE is_online = 0")->fetch()['cnt'];
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
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 16px;
        }

        /* === Widget === */
        .vw {
            background: var(--card);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 2px 8px rgba(0,0,0,.03);
            overflow: hidden;
            border-left: 4px solid var(--success);
            transition: opacity .3s;
        }
        .vw.offline { border-left-color: #cbd5e1; opacity: .5; }
        .vw.offline:hover { opacity: .75; }

        /* Header row */
        .vw-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .vw-head-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .vw-dot {
            width: 8px; height: 8px; border-radius: 50%;
            flex-shrink: 0;
        }
        .vw-dot.on { background: #16a34a; animation: pulse 2s infinite; }
        .vw-dot.off { background: #cbd5e1; }
        @keyframes pulse {
            0%,100% { box-shadow: 0 0 0 0 rgba(22,163,74,.35); }
            50% { box-shadow: 0 0 0 5px rgba(22,163,74,0); }
        }
        .vw-ip {
            font-family: 'SF Mono', 'Consolas', monospace;
            font-size: .82rem;
            color: var(--text);
            font-weight: 600;
        }
        .vw-flag { font-size: 1.1rem; line-height: 1; }
        .vw-head-right {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .vw-time {
            font-size: .72rem;
            color: var(--text-light);
        }

        /* Badges */
        .badge-sm {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: .7rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .b-online { background: #dcfce7; color: #15803d; }
        .b-offline { background: #f1f5f9; color: #94a3b8; }
        .b-viewing { background: #dbeafe; color: #1d4ed8; }
        .b-filling { background: #fef3c7; color: #b45309; }
        .b-reading { background: #ede9fe; color: #7c3aed; }
        .b-registered { background: #dcfce7; color: #15803d; }
        .b-action { background: #fef2f2; color: #dc2626; animation: actionPulse 1.5s infinite; }
        @keyframes actionPulse {
            0%,100% { opacity: 1; }
            50% { opacity: .6; }
        }

        /* Identity */
        .vw-identity {
            padding: 8px 14px 0;
        }
        .vw-name { font-weight: 700; font-size: .92rem; }
        .vw-email { font-size: .78rem; color: var(--text-light); }

        /* Fingerprint section */
        .vw-fp {
            padding: 10px 14px;
        }
        .vw-fp-title {
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--text-light);
            margin-bottom: 6px;
            font-weight: 600;
        }
        .vw-fp-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
        .vw-fp-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: .73rem;
            color: var(--text);
            white-space: nowrap;
        }
        .vw-fp-tag .lbl {
            color: var(--text-light);
            font-weight: 500;
        }

        /* Actions */
        .vw-actions {
            display: flex;
            gap: 6px;
            padding: 0 14px 12px;
            flex-wrap: wrap;
        }
        .vw-btn {
            padding: 4px 10px;
            border: 1.5px solid var(--border);
            border-radius: 6px;
            background: #fff;
            font-size: .72rem;
            font-weight: 600;
            color: var(--text-light);
            cursor: pointer;
            transition: all .15s;
            font-family: inherit;
        }
        .vw-btn:hover:not(:disabled) {
            border-color: var(--primary);
            color: var(--primary);
            background: #eff6ff;
        }
        .vw-btn:disabled { opacity: .4; cursor: default; }
        .vw-btn-danger:hover:not(:disabled) { border-color: var(--danger); color: var(--danger); background: #fef2f2; }
        .vw-btn-success:hover:not(:disabled) { border-color: var(--success); color: var(--success); background: #f0fdf4; }

        /* Empty */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
            grid-column: 1 / -1;
        }
        .empty-state h3 { color: var(--text); margin-bottom: 4px; font-size: 1rem; }

        @media (max-width: 768px) {
            .visitors-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .admin-main { padding: 16px; }
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
            <div class="stat-card"><div class="label">Disconnected</div><div class="value" style="color:var(--text-light)" id="stat-disconnected"><?= $visitorDisconnected ?></div></div>
        </div>

        <div class="visitors-grid" id="visitors-grid">
            <div class="empty-state">Loading...</div>
        </div>
    </main>
</div>

<script>
function esc(str) { const d = document.createElement('div'); d.textContent = str||''; return d.innerHTML; }

function countryFlag(code) {
    if (!code || code.length !== 2) return '';
    const offset = 0x1F1E6;
    return String.fromCodePoint(
        code.charCodeAt(0) - 65 + offset,
        code.charCodeAt(1) - 65 + offset
    );
}

function timeSince(dateStr) {
    if (!dateStr) return '--';
    const diff = Math.floor((new Date() - new Date(dateStr + 'Z')) / 1000);
    if (diff < 10) return 'now';
    if (diff < 60) return diff + 's';
    if (diff < 3600) return Math.floor(diff/60) + 'm';
    if (diff < 86400) return Math.floor(diff/3600) + 'h';
    return Math.floor(diff/86400) + 'd';
}

function actBadge(v) {
    if (!v.is_online) return '<span class="badge-sm b-offline">Disconnected</span>';
    const m = {
        'viewing':          ['b-viewing', 'Viewing'],
        'filling_form':     ['b-filling', 'Filling Form'],
        'reading_policies': ['b-reading', 'Reading Policies'],
        'registered':       ['b-registered', 'Registered']
    };
    const [c, t] = m[v.status] || ['b-viewing', 'Viewing'];
    return `<span class="badge-sm ${c}">${t}</span>`;
}

// Track existing cards — only update what changes, never move them
const cardMap = {};
let firstLoad = true;

function buildCardHTML(v) {
    const online = !!v.is_online;
    const loc = [v.city, v.region, v.country].filter(Boolean).join(', ') || 'Unknown';
    const flag = countryFlag((v.country_code || '').toUpperCase());
    const hasId = v.name && v.surname;

    return `
        <div class="vw-head">
            <div class="vw-head-left">
                <span class="vw-dot ${online ? 'on' : 'off'}"></span>
                ${flag ? `<span class="vw-flag">${flag}</span>` : ''}
                <span class="vw-ip">${esc(v.ip_address)}</span>
            </div>
            <div class="vw-head-right">
                <span data-u="badge">${actBadge(v)}</span>
                <span class="vw-time" data-u="time">${timeSince(v.last_activity)}</span>
            </div>
        </div>
        ${hasId ? `<div class="vw-identity"><div class="vw-name">${esc(v.name)} ${esc(v.surname)}</div><div class="vw-email">${esc(v.email)}</div></div>` : ''}
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
            <button class="vw-btn" disabled>Redirect</button>
            <button class="vw-btn" disabled>Message</button>
            <button class="vw-btn vw-btn-danger" disabled>Kick</button>
            <button class="vw-btn vw-btn-success" disabled>Approve</button>
        </div>`;
}

function createOrUpdateCard(v, grid) {
    const id = v.id;
    const online = !!v.is_online;
    let el = cardMap[id];

    if (!el) {
        // New card — create and append once, never move it again
        el = document.createElement('div');
        el.dataset.vid = id;
        el.className = `vw ${online ? '' : 'offline'}`;
        el.innerHTML = buildCardHTML(v);
        cardMap[id] = el;
        grid.appendChild(el);
        return;
    }

    // Existing card — only update the tiny parts that change
    // 1. Online/offline class
    const newClass = `vw ${online ? '' : 'offline'}`;
    if (el.className !== newClass) el.className = newClass;

    // 2. Status dot
    const dot = el.querySelector('.vw-dot');
    if (dot) {
        const dotClass = online ? 'vw-dot on' : 'vw-dot off';
        if (dot.className !== dotClass) dot.className = dotClass;
    }

    // 3. Activity badge
    const badgeSpan = el.querySelector('[data-u="badge"]');
    if (badgeSpan) {
        const newBadge = actBadge(v);
        if (badgeSpan.innerHTML !== newBadge) badgeSpan.innerHTML = newBadge;
    }

    // 4. Time
    const timeSpan = el.querySelector('[data-u="time"]');
    if (timeSpan) timeSpan.textContent = timeSince(v.last_activity);

    // 5. Identity (might appear after registration)
    const hasId = v.name && v.surname;
    const identEl = el.querySelector('.vw-identity');
    if (hasId && !identEl) {
        const head = el.querySelector('.vw-head');
        if (head) {
            const idDiv = document.createElement('div');
            idDiv.className = 'vw-identity';
            idDiv.innerHTML = `<div class="vw-name">${esc(v.name)} ${esc(v.surname)}</div><div class="vw-email">${esc(v.email)}</div>`;
            head.after(idDiv);
        }
    }
}

async function refreshDashboard() {
    try {
        const resp = await fetch('api/dashboard.php');
        const data = await resp.json();

        document.getElementById('stat-total').textContent = data.stats.visitorTotal || 0;
        document.getElementById('stat-online').textContent = data.stats.visitorOnline || 0;
        document.getElementById('stat-registered').textContent = data.stats.visitorRegistered || 0;
        document.getElementById('stat-disconnected').textContent = data.stats.visitorDisconnected || 0;

        const grid = document.getElementById('visitors-grid');

        if (!data.visitors || !data.visitors.length) {
            if (!grid.querySelector('.empty-state')) {
                grid.innerHTML = '<div class="empty-state"><h3>No visitors yet</h3><p>Visitors appear the moment someone opens the exam page.</p></div>';
            }
            return;
        }

        // Clear empty state on first real data
        const emptyState = grid.querySelector('.empty-state');
        if (emptyState) emptyState.remove();

        // Update or create cards — no reordering, no innerHTML replace
        const incomingIds = new Set();
        data.visitors.forEach(v => {
            incomingIds.add(v.id);
            createOrUpdateCard(v, grid);
        });

        // Remove cards that no longer exist in the data
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
