<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$db->exec("UPDATE visitors SET is_online = 0 WHERE last_activity < datetime('now', '-15 seconds') AND is_online = 1");

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
        /* === Visitor Widgets === */
        .visitors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
        }
        .visitor-widget {
            background: var(--card);
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 14px rgba(0,0,0,.04);
            overflow: hidden;
            transition: box-shadow .2s, transform .15s;
            position: relative;
        }
        .visitor-widget:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,.1);
            transform: translateY(-2px);
        }
        .visitor-widget.offline {
            opacity: .55;
        }
        .visitor-widget.offline:hover {
            opacity: .8;
        }

        /* Status bar at top */
        .vw-status-bar {
            height: 4px;
            width: 100%;
        }
        .vw-status-bar.online { background: var(--success); }
        .vw-status-bar.offline { background: var(--border); }

        /* Header */
        .vw-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 16px 18px 0;
        }
        .vw-status-group {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .vw-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: .74rem;
            font-weight: 600;
        }
        .vw-badge-online { background: #dcfce7; color: #15803d; }
        .vw-badge-offline { background: #f1f5f9; color: #94a3b8; }
        .vw-badge-viewing { background: #dbeafe; color: #1d4ed8; }
        .vw-badge-filling { background: #fef3c7; color: #b45309; }
        .vw-badge-reading { background: #ede9fe; color: #7c3aed; }
        .vw-badge-registered { background: #dcfce7; color: #15803d; }
        .vw-dot {
            width: 7px; height: 7px; border-radius: 50%; display: inline-block;
        }
        .vw-dot.online { background: #16a34a; animation: pulse 2s infinite; }
        .vw-dot.offline { background: #94a3b8; }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(22,163,74,.4); }
            50% { box-shadow: 0 0 0 6px rgba(22,163,74,0); }
        }

        .vw-time {
            font-size: .75rem;
            color: var(--text-light);
            text-align: right;
            line-height: 1.4;
            flex-shrink: 0;
        }

        /* Identity (shown when registered) */
        .vw-identity {
            padding: 12px 18px 0;
        }
        .vw-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text);
        }
        .vw-email {
            font-size: .82rem;
            color: var(--text-light);
        }

        /* Data grid */
        .vw-data {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            padding: 14px 18px;
        }
        .vw-field {
            padding: 7px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .vw-field:nth-last-child(-n+2) { border-bottom: none; }
        .vw-field-label {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--text-light);
            margin-bottom: 2px;
        }
        .vw-field-value {
            font-size: .84rem;
            font-weight: 500;
            color: var(--text);
            word-break: break-all;
        }
        .vw-field-value code {
            background: #f1f5f9;
            padding: 1px 6px;
            border-radius: 4px;
            font-size: .8rem;
        }

        /* Actions */
        .vw-actions {
            display: flex;
            gap: 8px;
            padding: 0 18px 16px;
            flex-wrap: wrap;
        }
        .vw-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            background: #fff;
            font-size: .78rem;
            font-weight: 600;
            color: var(--text-light);
            cursor: pointer;
            transition: all .15s;
            font-family: inherit;
        }
        .vw-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: #f0f5ff;
        }
        .vw-btn-danger:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: #fef2f2;
        }
        .vw-btn-success:hover {
            border-color: var(--success);
            color: var(--success);
            background: #f0fdf4;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }
        .empty-state .icon { font-size: 2.5rem; margin-bottom: 12px; }
        .empty-state h3 { color: var(--text); margin-bottom: 4px; }

        /* Responsive */
        @media (max-width: 768px) {
            .visitors-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .admin-main { padding: 16px; }
            .vw-data { grid-template-columns: 1fr; }
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
            <span style="font-size:.82rem;color:var(--text-light)">Auto-refresh: every 3s</span>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><div class="label">Total Visitors</div><div class="value" id="stat-total"><?= $visitorTotal ?></div></div>
            <div class="stat-card"><div class="label">Online Now</div><div class="value online" id="stat-online"><?= $visitorOnline ?></div></div>
            <div class="stat-card"><div class="label">Registered</div><div class="value" style="color:var(--primary)" id="stat-registered"><?= $visitorRegistered ?></div></div>
            <div class="stat-card"><div class="label">Disconnected</div><div class="value" style="color:var(--text-light)" id="stat-disconnected"><?= $visitorDisconnected ?></div></div>
        </div>

        <div class="visitors-grid" id="visitors-grid">
            <div class="empty-state" style="grid-column:1/-1">
                <div class="icon">&#8987;</div>
                <h3>Loading...</h3>
            </div>
        </div>
    </main>
</div>

<script>
function esc(str) { const d = document.createElement('div'); d.textContent = str||''; return d.innerHTML; }

function timeSince(dateStr) {
    if (!dateStr) return '--';
    const diff = Math.floor((new Date() - new Date(dateStr + 'Z')) / 1000);
    if (diff < 10) return 'just now';
    if (diff < 60) return diff + 's ago';
    if (diff < 3600) return Math.floor(diff/60) + 'm ago';
    if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
    return Math.floor(diff/86400) + 'd ago';
}

function activityBadge(v) {
    if (!v.is_online) return '';
    const s = v.status || 'viewing';
    const map = {
        'viewing':          ['vw-badge-viewing',    'Viewing Page'],
        'filling_form':     ['vw-badge-filling',    'Filling Form'],
        'reading_policies': ['vw-badge-reading',    'Reading Policies'],
        'registered':       ['vw-badge-registered', 'Registered']
    };
    const [cls, txt] = map[s] || ['vw-badge-viewing', 'Viewing Page'];
    return `<span class="vw-badge ${cls}">${txt}</span>`;
}

function buildWidget(v) {
    const online = !!v.is_online;
    const location = [v.city, v.region, v.country].filter(Boolean).join(', ') || 'Unknown';
    const hasIdentity = v.name && v.surname;

    return `
    <div class="visitor-widget ${online ? '' : 'offline'}">
        <div class="vw-status-bar ${online ? 'online' : 'offline'}"></div>
        <div class="vw-header">
            <div class="vw-status-group">
                <span class="vw-badge ${online ? 'vw-badge-online' : 'vw-badge-offline'}">
                    <span class="vw-dot ${online ? 'online' : 'offline'}"></span>
                    ${online ? 'Online' : 'Disconnected'}
                </span>
                ${activityBadge(v)}
            </div>
            <div class="vw-time">
                <div>${timeSince(v.last_activity)}</div>
            </div>
        </div>

        ${hasIdentity ? `
        <div class="vw-identity">
            <div class="vw-name">${esc(v.name)} ${esc(v.surname)}</div>
            <div class="vw-email">${esc(v.email)}</div>
        </div>` : ''}

        <div class="vw-data">
            <div class="vw-field">
                <div class="vw-field-label">IP Address</div>
                <div class="vw-field-value"><code>${esc(v.ip_address)}</code></div>
            </div>
            <div class="vw-field">
                <div class="vw-field-label">Location</div>
                <div class="vw-field-value">${esc(location)}</div>
            </div>
            <div class="vw-field">
                <div class="vw-field-label">Browser</div>
                <div class="vw-field-value">${esc(v.browser)}</div>
            </div>
            <div class="vw-field">
                <div class="vw-field-label">Device</div>
                <div class="vw-field-value">${esc(v.device)}</div>
            </div>
            <div class="vw-field">
                <div class="vw-field-label">OS</div>
                <div class="vw-field-value">${esc(v.os)}</div>
            </div>
            <div class="vw-field">
                <div class="vw-field-label">Screen</div>
                <div class="vw-field-value">${esc(v.screen_resolution)}</div>
            </div>
            <div class="vw-field">
                <div class="vw-field-label">Timezone</div>
                <div class="vw-field-value">${esc(v.timezone)}</div>
            </div>
            <div class="vw-field">
                <div class="vw-field-label">First Seen</div>
                <div class="vw-field-value">${timeSince(v.created_at)}</div>
            </div>
        </div>

        <div class="vw-actions">
            <button class="vw-btn" title="Coming soon" disabled>Redirect</button>
            <button class="vw-btn" title="Coming soon" disabled>Message</button>
            <button class="vw-btn vw-btn-danger" title="Coming soon" disabled>Kick</button>
            <button class="vw-btn vw-btn-success" title="Coming soon" disabled>Approve</button>
        </div>
    </div>`;
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
            grid.innerHTML = `
                <div class="empty-state" style="grid-column:1/-1">
                    <div class="icon">&#128101;</div>
                    <h3>No visitors yet</h3>
                    <p>Visitors will appear here the moment someone opens the exam page.</p>
                </div>`;
        } else {
            grid.innerHTML = data.visitors.map(buildWidget).join('');
        }
    } catch(e) { console.error('Refresh failed', e); }
}

refreshDashboard();
setInterval(refreshDashboard, 3000);
</script>
</body>
</html>
