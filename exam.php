<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

if (empty($_SESSION['student_token'])) { header('Location: index.php'); exit; }
$student = getStudentByToken($_SESSION['student_token']);
if (!$student) { unset($_SESSION['student_token']); header('Location: index.php'); exit; }
updateStudentActivity($student['id'], 'exam');

$visitorId = (int)($_SESSION['visitor_id'] ?? 0);
if ($visitorId) {
    $db = getDB();
    $db->prepare("UPDATE visitors SET is_online = 1, status = 'exam', last_activity = datetime('now') WHERE id = ?")->execute([$visitorId]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Portal - Questions</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f0f2f5; color: #1e293b; min-height: 100vh; }
        .top-bar { background: #1e3a8a; color: #fff; padding: 14px 24px; display: flex; justify-content: space-between; align-items: center; }
        .top-bar h1 { font-size: 1.1rem; }
        .top-bar .timer { background: rgba(255,255,255,.15); padding: 6px 14px; border-radius: 8px; font-weight: 600; font-size: .9rem; }
        .exam-container { max-width: 720px; margin: 24px auto; padding: 0 16px; }
        .question-card { background: #fff; border-radius: 12px; padding: 24px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .q-num { font-size: .78rem; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px; }
        .q-text { font-size: 1rem; font-weight: 600; margin-bottom: 16px; }
        .q-options { display: flex; flex-direction: column; gap: 8px; }
        .q-option { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border: 1.5px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all .15s; }
        .q-option:hover { border-color: #2563eb; background: #eff6ff; }
        .q-option input { accent-color: #2563eb; width: 18px; height: 18px; }
        .q-option label { cursor: pointer; font-size: .92rem; }
        .btn-submit { display: block; width: 100%; padding: 14px; background: #2563eb; color: #fff; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 20px; font-family: inherit; }
        .btn-submit:hover { background: #1d4ed8; }
    </style>
</head>
<body>
<div class="top-bar">
    <h1>Exam - <?= sanitize($student['name'] . ' ' . $student['surname']) ?></h1>
    <div class="timer" id="timer">45:00</div>
</div>

<div class="exam-container">
    <div class="question-card">
        <div class="q-num">Question 1 of 5</div>
        <div class="q-text">What is the capital of France?</div>
        <div class="q-options">
            <div class="q-option"><input type="radio" name="q1" id="q1a"><label for="q1a">London</label></div>
            <div class="q-option"><input type="radio" name="q1" id="q1b"><label for="q1b">Paris</label></div>
            <div class="q-option"><input type="radio" name="q1" id="q1c"><label for="q1c">Berlin</label></div>
            <div class="q-option"><input type="radio" name="q1" id="q1d"><label for="q1d">Madrid</label></div>
        </div>
    </div>

    <div class="question-card">
        <div class="q-num">Question 2 of 5</div>
        <div class="q-text">Which planet is known as the Red Planet?</div>
        <div class="q-options">
            <div class="q-option"><input type="radio" name="q2" id="q2a"><label for="q2a">Venus</label></div>
            <div class="q-option"><input type="radio" name="q2" id="q2b"><label for="q2b">Mars</label></div>
            <div class="q-option"><input type="radio" name="q2" id="q2c"><label for="q2c">Jupiter</label></div>
            <div class="q-option"><input type="radio" name="q2" id="q2d"><label for="q2d">Saturn</label></div>
        </div>
    </div>

    <div class="question-card">
        <div class="q-num">Question 3 of 5</div>
        <div class="q-text">What is 15 x 12?</div>
        <div class="q-options">
            <div class="q-option"><input type="radio" name="q3" id="q3a"><label for="q3a">160</label></div>
            <div class="q-option"><input type="radio" name="q3" id="q3b"><label for="q3b">180</label></div>
            <div class="q-option"><input type="radio" name="q3" id="q3c"><label for="q3c">170</label></div>
            <div class="q-option"><input type="radio" name="q3" id="q3d"><label for="q3d">190</label></div>
        </div>
    </div>

    <div class="question-card">
        <div class="q-num">Question 4 of 5</div>
        <div class="q-text">Who wrote "Romeo and Juliet"?</div>
        <div class="q-options">
            <div class="q-option"><input type="radio" name="q4" id="q4a"><label for="q4a">Charles Dickens</label></div>
            <div class="q-option"><input type="radio" name="q4" id="q4b"><label for="q4b">William Shakespeare</label></div>
            <div class="q-option"><input type="radio" name="q4" id="q4c"><label for="q4c">Mark Twain</label></div>
            <div class="q-option"><input type="radio" name="q4" id="q4d"><label for="q4d">Jane Austen</label></div>
        </div>
    </div>

    <div class="question-card">
        <div class="q-num">Question 5 of 5</div>
        <div class="q-text">What is the chemical symbol for water?</div>
        <div class="q-options">
            <div class="q-option"><input type="radio" name="q5" id="q5a"><label for="q5a">CO2</label></div>
            <div class="q-option"><input type="radio" name="q5" id="q5b"><label for="q5b">H2O</label></div>
            <div class="q-option"><input type="radio" name="q5" id="q5c"><label for="q5c">O2</label></div>
            <div class="q-option"><input type="radio" name="q5" id="q5d"><label for="q5d">NaCl</label></div>
        </div>
    </div>

    <button class="btn-submit" onclick="alert('Exam submitted! (Demo)')">Submit Exam</button>
</div>

<script>
const studentId = <?= (int)$student['id'] ?>;
const visitorId = <?= $visitorId ?>;
let pageVisible = true;

// Timer
let timeLeft = 45 * 60;
setInterval(() => {
    if (timeLeft <= 0) return;
    timeLeft--;
    const m = Math.floor(timeLeft / 60), s = timeLeft % 60;
    document.getElementById('timer').textContent = m + ':' + String(s).padStart(2, '0');
}, 1000);

// Heartbeats
function hb() {
    if (!pageVisible || document.hidden) return;
    fetch('api/heartbeat.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ student_id: studentId }) }).catch(() => {});
    if (visitorId) fetch('api/visitor-heartbeat.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ visitor_id: visitorId, status: 'exam' }) }).catch(() => {});
}

document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        pageVisible = false;
        navigator.sendBeacon('api/offline.php', JSON.stringify({ student_id: studentId }));
        if (visitorId) navigator.sendBeacon('api/visitor-offline.php', JSON.stringify({ visitor_id: visitorId }));
        fetch('api/activity.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ student_id: studentId, action: 'tab_hidden', details: 'Student left exam tab' }) }).catch(() => {});
        clearInterval(hbTimer); hbTimer = null;
    } else {
        pageVisible = true;
        hb();
        if (!hbTimer) hbTimer = setInterval(hb, 5000);
        fetch('api/activity.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ student_id: studentId, action: 'tab_visible', details: 'Student returned to exam tab' }) }).catch(() => {});
    }
});

document.addEventListener('contextmenu', e => { e.preventDefault(); });
['copy','paste'].forEach(evt => {
    document.addEventListener(evt, () => {
        fetch('api/activity.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ student_id: studentId, action: evt + '_attempt', details: 'Attempted ' + evt + ' during exam' }) }).catch(() => {});
    });
});

function sendOffline() {
    navigator.sendBeacon('api/offline.php', JSON.stringify({ student_id: studentId }));
    if (visitorId) navigator.sendBeacon('api/visitor-offline.php', JSON.stringify({ visitor_id: visitorId }));
}

hb();
let hbTimer = setInterval(hb, 5000);
window.addEventListener('pagehide', sendOffline);
window.addEventListener('beforeunload', sendOffline);
</script>
</body>
</html>
