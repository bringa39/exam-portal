<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$db->exec("UPDATE students SET is_online = 0 WHERE last_activity < datetime('now', '-15 seconds') AND is_online = 1");

$totalStudents = $db->query("SELECT COUNT(*) as cnt FROM students")->fetch()['cnt'];
$onlineStudents = $db->query("SELECT COUNT(*) as cnt FROM students WHERE is_online = 1")->fetch()['cnt'];
$totalEvents = $db->query("SELECT COUNT(*) as cnt FROM activity_log")->fetch()['cnt'];
$flaggedEvents = $db->query("SELECT COUNT(*) as cnt FROM activity_log WHERE action IN ('tab_hidden','copy_attempt','paste_attempt','right_click')")->fetch()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Exam Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .actions-cell { display: flex; gap: 6px; flex-wrap: wrap; }
        .flag-count { color: var(--danger); font-weight: 600; }
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
            <div class="stat-card"><div class="label">Total Students</div><div class="value" id="stat-total"><?= $totalStudents ?></div></div>
            <div class="stat-card"><div class="label">Online Now</div><div class="value online" id="stat-online"><?= $onlineStudents ?></div></div>
            <div class="stat-card"><div class="label">Total Events</div><div class="value" id="stat-events"><?= $totalEvents ?></div></div>
            <div class="stat-card"><div class="label">Flagged Events</div><div class="value" style="color:var(--danger)" id="stat-flagged"><?= $flaggedEvents ?></div></div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2>Connected Students</h2>
                <button class="btn btn-sm btn-outline" onclick="refreshDashboard()">Refresh</button>
            </div>
            <table>
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Status</th><th>Device</th><th>IP Address</th><th>Current Page</th><th>Flags</th><th>Actions</th></tr>
                </thead>
                <tbody id="students-table-body">
                    <tr><td colspan="8" style="text-align:center;color:var(--text-light)">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Detail Modal -->
<div class="modal-overlay" id="detailModal">
    <div class="modal">
        <h2 id="modal-title">Student Details</h2>
        <div id="modal-body"></div>
        <div style="margin-top:20px"><button class="btn btn-sm btn-outline" onclick="document.getElementById('detailModal').classList.remove('active')">Close</button></div>
    </div>
</div>

<!-- Redirect Modal -->
<div class="modal-overlay" id="redirectModal">
    <div class="modal">
        <h2>Redirect Student</h2>
        <input type="hidden" id="redirect-student-id">
        <div class="form-group">
            <label>Target URL</label>
            <input type="text" id="redirect-url" placeholder="https://example.com/exam-page">
        </div>
        <div style="display:flex;gap:8px">
            <button class="btn btn-sm btn-primary" onclick="sendRedirect()">Send Redirect</button>
            <button class="btn btn-sm btn-outline" onclick="document.getElementById('redirectModal').classList.remove('active')">Cancel</button>
        </div>
    </div>
</div>

<script>
function esc(str) { const d = document.createElement('div'); d.textContent = str||''; return d.innerHTML; }

async function refreshDashboard() {
    try {
        const resp = await fetch('api/dashboard.php');
        const data = await resp.json();

        document.getElementById('stat-total').textContent = data.stats.total;
        document.getElementById('stat-online').textContent = data.stats.online;
        document.getElementById('stat-events').textContent = data.stats.events;
        document.getElementById('stat-flagged').textContent = data.stats.flagged;

        const tbody = document.getElementById('students-table-body');
        if (!data.students.length) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--text-light)">No students registered yet.</td></tr>';
            return;
        }
        tbody.innerHTML = data.students.map(s => `
            <tr>
                <td><strong>${esc(s.name)} ${esc(s.surname)}</strong></td>
                <td>${esc(s.email)}</td>
                <td><span class="badge ${s.is_online?'badge-online':'badge-offline'}">${s.is_online?'Online':'Offline'}</span></td>
                <td>${esc(s.browser)} / ${esc(s.device)}</td>
                <td><code>${esc(s.ip_address)}</code></td>
                <td>${esc(s.current_page)}</td>
                <td class="flag-count">${s.flags}</td>
                <td class="actions-cell">
                    <button class="btn btn-sm btn-outline" onclick="viewStudent(${s.id})">Details</button>
                    <button class="btn btn-sm btn-primary" onclick="openRedirect(${s.id})">Redirect</button>
                </td>
            </tr>`).join('');
    } catch(e) { console.error('Refresh failed', e); }
}

async function viewStudent(id) {
    const resp = await fetch('api/student-detail.php?id=' + id);
    const data = await resp.json();
    const s = data.student, logs = data.logs;
    document.getElementById('modal-title').textContent = s.name + ' ' + s.surname;
    document.getElementById('modal-body').innerHTML = `
        <div class="detail-row"><span class="label">Email</span><span>${esc(s.email)}</span></div>
        <div class="detail-row"><span class="label">Address</span><span>${esc(s.address)}</span></div>
        <div class="detail-row"><span class="label">IP Address</span><span>${esc(s.ip_address)}</span></div>
        <div class="detail-row"><span class="label">Browser</span><span>${esc(s.browser)}</span></div>
        <div class="detail-row"><span class="label">OS</span><span>${esc(s.os)}</span></div>
        <div class="detail-row"><span class="label">Device</span><span>${esc(s.device)}</span></div>
        <div class="detail-row"><span class="label">Screen</span><span>${esc(s.screen_resolution)}</span></div>
        <div class="detail-row"><span class="label">Language</span><span>${esc(s.language)}</span></div>
        <div class="detail-row"><span class="label">Timezone</span><span>${esc(s.timezone)}</span></div>
        <div class="detail-row"><span class="label">User Agent</span><span style="font-size:.78rem;word-break:break-all">${esc(s.user_agent)}</span></div>
        <div class="detail-row"><span class="label">Registered</span><span>${esc(s.created_at)}</span></div>
        <div class="detail-row"><span class="label">Last Activity</span><span>${esc(s.last_activity)}</span></div>
        <h3 style="margin-top:20px;margin-bottom:10px;font-size:.95rem">Recent Activity</h3>
        <div style="max-height:200px;overflow-y:auto">
            ${logs.map(l => `<div class="detail-row"><span class="label" style="font-size:.8rem">${esc(l.created_at)}</span><span><strong>${esc(l.action)}</strong> ${esc(l.details)}</span></div>`).join('')}
        </div>`;
    document.getElementById('detailModal').classList.add('active');
}

function openRedirect(id) {
    document.getElementById('redirect-student-id').value = id;
    document.getElementById('redirect-url').value = '';
    document.getElementById('redirectModal').classList.add('active');
}

async function sendRedirect() {
    const studentId = document.getElementById('redirect-student-id').value;
    const url = document.getElementById('redirect-url').value.trim();
    if (!url) { alert('Please enter a URL'); return; }
    await fetch('api/redirect.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ student_id: studentId, url })
    });
    document.getElementById('redirectModal').classList.remove('active');
    alert('Redirect sent! Student will be redirected within 5 seconds.');
}

refreshDashboard();
setInterval(refreshDashboard, 3000);
</script>
</body>
</html>
