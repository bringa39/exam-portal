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
        .badge-status {
            display: inline-block; padding: 3px 10px; border-radius: 20px;
            font-size: .76rem; font-weight: 600; white-space: nowrap;
        }
        .badge-viewing { background: #dbeafe; color: #1d4ed8; }
        .badge-filling { background: #fef3c7; color: #b45309; }
        .badge-reading { background: #ede9fe; color: #7c3aed; }
        .badge-registered { background: #dcfce7; color: #15803d; }
        .badge-disconnected { background: #f1f5f9; color: #64748b; }
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

        <div class="table-card">
            <div class="table-header">
                <h2>Page Visitors</h2>
                <button class="btn btn-sm btn-outline" onclick="refreshDashboard()">Refresh</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Activity</th>
                        <th>IP Address</th>
                        <th>Location</th>
                        <th>Browser / Device</th>
                        <th>OS</th>
                        <th>Screen</th>
                        <th>Timezone</th>
                        <th>First Seen</th>
                        <th>Last Active</th>
                    </tr>
                </thead>
                <tbody id="visitors-table-body">
                    <tr><td colspan="10" style="text-align:center;color:var(--text-light)">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
function esc(str) { const d = document.createElement('div'); d.textContent = str||''; return d.innerHTML; }

function statusBadge(v) {
    if (!v.is_online) return '<span class="badge-status badge-disconnected">Disconnected</span>';
    return '<span class="badge badge-online">Online</span>';
}

function activityBadge(v) {
    if (!v.is_online) return '<span class="badge-status badge-disconnected">--</span>';
    const s = v.status || 'viewing';
    const map = {
        'viewing': ['badge-viewing', 'Viewing Page'],
        'filling_form': ['badge-filling', 'Filling Form'],
        'reading_policies': ['badge-reading', 'Reading Policies'],
        'registered': ['badge-registered', 'Registered']
    };
    const [cls, txt] = map[s] || ['badge-viewing', 'Viewing Page'];
    return `<span class="badge-status ${cls}">${txt}</span>`;
}

function timeSince(dateStr) {
    if (!dateStr) return '--';
    const now = new Date();
    const then = new Date(dateStr + 'Z');
    const diff = Math.floor((now - then) / 1000);
    if (diff < 10) return 'just now';
    if (diff < 60) return diff + 's ago';
    if (diff < 3600) return Math.floor(diff/60) + 'm ago';
    if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
    return Math.floor(diff/86400) + 'd ago';
}

async function refreshDashboard() {
    try {
        const resp = await fetch('api/dashboard.php');
        const data = await resp.json();

        document.getElementById('stat-total').textContent = data.stats.visitorTotal || 0;
        document.getElementById('stat-online').textContent = data.stats.visitorOnline || 0;
        document.getElementById('stat-registered').textContent = data.stats.visitorRegistered || 0;
        document.getElementById('stat-disconnected').textContent = data.stats.visitorDisconnected || 0;

        const vtbody = document.getElementById('visitors-table-body');
        if (!data.visitors || !data.visitors.length) {
            vtbody.innerHTML = '<tr><td colspan="10" style="text-align:center;color:var(--text-light)">No visitors yet.</td></tr>';
        } else {
            vtbody.innerHTML = data.visitors.map(v => {
                const location = [v.city, v.region, v.country].filter(Boolean).join(', ') || 'Unknown';
                return `<tr style="${!v.is_online ? 'opacity:.55' : ''}">
                    <td>${statusBadge(v)}</td>
                    <td>${activityBadge(v)}</td>
                    <td><code>${esc(v.ip_address)}</code></td>
                    <td>${esc(location)}</td>
                    <td>${esc(v.browser)} / ${esc(v.device)}</td>
                    <td>${esc(v.os)}</td>
                    <td>${esc(v.screen_resolution)}</td>
                    <td>${esc(v.timezone)}</td>
                    <td>${esc(v.created_at)}</td>
                    <td>${timeSince(v.last_activity)}</td>
                </tr>`;
            }).join('');
        }
    } catch(e) { console.error('Refresh failed', e); }
}

refreshDashboard();
setInterval(refreshDashboard, 3000);
</script>
</body>
</html>
