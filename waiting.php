<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

if (empty($_SESSION['student_token'])) { header('Location: index.php'); exit; }
$student = getStudentByToken($_SESSION['student_token']);
if (!$student) { unset($_SESSION['student_token']); header('Location: index.php'); exit; }
updateStudentActivity($student['id'], 'waiting_room');

$visitorId = (int)($_SESSION['visitor_id'] ?? 0);
// Keep visitor online on page load
if ($visitorId) {
    $db = getDB();
    $db->prepare("UPDATE visitors SET is_online = 1, status = 'waiting', last_activity = datetime('now') WHERE id = ?")->execute([$visitorId]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Portal - Waiting Room</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="landing-hero">
    <h1>Exam Portal</h1>
    <p>Welcome, <?= sanitize($student['name'] . ' ' . $student['surname']) ?></p>
</div>

<div class="container">
    <div class="card waiting-room">
        <div class="spinner"></div>
        <h2 data-i18n="wait_title">Waiting Room</h2>
        <p style="color:var(--text-light);margin-top:8px" data-i18n="wait_message">
            You are registered and connected. Please wait for the exam administrator to start your session.
        </p>
        <p style="color:var(--text-light);margin-top:16px;font-size:.85rem" data-i18n="wait_warning">
            Do not close this tab. Your activity is being monitored.
        </p>
        <div id="redirect-notice" style="display:none;margin-top:20px;padding:16px;background:#eff6ff;border-radius:8px;color:var(--primary)">
            <strong>Redirecting you now...</strong>
        </div>
    </div>
</div>

<script src="assets/js/i18n.js"></script>
<script>
const studentId = <?= (int)$student['id'] ?>;
const visitorId = <?= $visitorId ?>;
let pageVisible = true;
let navigatingAway = false;

async function heartbeat() {
    if (!pageVisible || document.hidden) return;
    try {
        const resp = await fetch('api/heartbeat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ student_id: studentId })
        });
        const data = await resp.json();
        if (data.redirect) {
            navigatingAway = true;
            document.getElementById('redirect-notice').style.display = 'block';
            // Update visitor status before navigating
            if (visitorId) {
                fetch('api/visitor-heartbeat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ visitor_id: visitorId, status: 'exam' })
                }).catch(() => {});
            }
            setTimeout(() => window.location.href = data.redirect, 1500);
        }
    } catch (e) {}
}

// Also heartbeat the visitor record
function visitorHeartbeat() {
    if (!pageVisible || document.hidden || !visitorId) return;
    fetch('api/visitor-heartbeat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ visitor_id: visitorId, status: 'waiting' })
    }).catch(() => {});
}

document.addEventListener('visibilitychange', () => {
    fetch('api/activity.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            student_id: studentId,
            action: document.hidden ? 'tab_hidden' : 'tab_visible',
            details: document.hidden ? 'Student switched away from exam tab' : 'Student returned to exam tab'
        })
    }).catch(() => {});
    if (document.hidden) {
        pageVisible = false;
        if (!navigatingAway) {
            navigator.sendBeacon('api/offline.php', JSON.stringify({ student_id: studentId }));
            if (visitorId) navigator.sendBeacon('api/visitor-offline.php', JSON.stringify({ visitor_id: visitorId }));
        }
        clearInterval(hbTimer); hbTimer = null;
        clearInterval(vhbTimer); vhbTimer = null;
    } else {
        pageVisible = true;
        heartbeat();
        visitorHeartbeat();
        if (!hbTimer) hbTimer = setInterval(heartbeat, 5000);
        if (!vhbTimer) vhbTimer = setInterval(visitorHeartbeat, 5000);
    }
});

['copy','paste'].forEach(evt => {
    document.addEventListener(evt, () => {
        fetch('api/activity.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ student_id: studentId, action: evt + '_attempt', details: 'Student attempted to ' + evt })
        }).catch(() => {});
    });
});

document.addEventListener('contextmenu', (e) => {
    e.preventDefault();
    fetch('api/activity.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ student_id: studentId, action: 'right_click', details: 'Student attempted right-click' })
    }).catch(() => {});
});

function sendOffline() {
    if (navigatingAway) return;
    navigator.sendBeacon('api/offline.php', JSON.stringify({ student_id: studentId }));
    if (visitorId) navigator.sendBeacon('api/visitor-offline.php', JSON.stringify({ visitor_id: visitorId }));
}

heartbeat();
visitorHeartbeat();
let hbTimer = setInterval(heartbeat, 5000);
let vhbTimer = setInterval(visitorHeartbeat, 5000);

window.addEventListener('pagehide', sendOffline);
window.addEventListener('beforeunload', sendOffline);
</script>
</body>
</html>
