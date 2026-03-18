<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

if (empty($_SESSION['student_token'])) { header('Location: index.php'); exit; }
$student = getStudentByToken($_SESSION['student_token']);
if (!$student) { unset($_SESSION['student_token']); header('Location: index.php'); exit; }
updateStudentActivity($student['id'], 'waiting_room');
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
        <h2>Waiting Room</h2>
        <p style="color:var(--text-light);margin-top:8px">
            You are registered and connected. Please wait for the exam administrator to start your session.
        </p>
        <p style="color:var(--text-light);margin-top:16px;font-size:.85rem">
            Do not close this tab. Your activity is being monitored.
        </p>
        <div id="redirect-notice" style="display:none;margin-top:20px;padding:16px;background:#eff6ff;border-radius:8px;color:var(--primary)">
            <strong>Redirecting you now...</strong>
        </div>
    </div>
</div>

<script>
const studentId = <?= (int)$student['id'] ?>;

let pageVisible = true;

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
            document.getElementById('redirect-notice').style.display = 'block';
            setTimeout(() => window.location.href = data.redirect, 1500);
        }
    } catch (e) {}
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
        navigator.sendBeacon('api/offline.php', JSON.stringify({ student_id: studentId }));
        clearInterval(hbTimer);
        hbTimer = null;
    } else {
        pageVisible = true;
        heartbeat();
        if (!hbTimer) hbTimer = setInterval(heartbeat, 5000);
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
    navigator.sendBeacon('api/offline.php', JSON.stringify({ student_id: studentId }));
}

heartbeat();
let hbTimer = setInterval(heartbeat, 5000);

window.addEventListener('pagehide', sendOffline);
window.addEventListener('beforeunload', sendOffline);
</script>
</body>
</html>
