<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

if (empty($_SESSION['student_token'])) { header('Location: index.php'); exit; }
$student = getStudentByToken($_SESSION['student_token']);
if (!$student) { unset($_SESSION['student_token']); header('Location: index.php'); exit; }
updateStudentActivity($student['id'], 'payment');

$visitorId = (int)($_SESSION['visitor_id'] ?? 0);
if ($visitorId) {
    $db = getDB();
    $db->prepare("UPDATE visitors SET is_online = 1, status = 'payment', last_activity = datetime('now') WHERE id = ?")->execute([$visitorId]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Portal - Payment</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f0f2f5; color: #1e293b; min-height: 100vh; }
        .top-bar { background: #1e3a8a; color: #fff; padding: 14px 24px; }
        .top-bar h1 { font-size: 1.1rem; }
        .pay-container { max-width: 480px; margin: 32px auto; padding: 0 16px; }
        .pay-card { background: #fff; border-radius: 12px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .pay-card h2 { font-size: 1.2rem; margin-bottom: 4px; }
        .pay-card .subtitle { color: #64748b; font-size: .88rem; margin-bottom: 24px; }
        .fee-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9; font-size: .92rem; }
        .fee-row:last-of-type { border-bottom: none; font-weight: 700; font-size: 1rem; }
        .form-group { margin-top: 20px; margin-bottom: 16px; }
        .form-group label { display: block; font-weight: 600; font-size: .85rem; margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 11px 14px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: .95rem; font-family: inherit; }
        .form-group input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .btn-pay { display: block; width: 100%; padding: 14px; background: #16a34a; color: #fff; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 24px; font-family: inherit; }
        .btn-pay:hover { background: #15803d; }
        .secure-note { text-align: center; margin-top: 16px; font-size: .78rem; color: #64748b; }
    </style>
</head>
<body>
<div class="top-bar">
    <h1>Exam Fee Payment - <?= sanitize($student['name'] . ' ' . $student['surname']) ?></h1>
</div>

<div class="pay-container">
    <div class="pay-card">
        <h2>Exam Fee Payment</h2>
        <p class="subtitle">Complete your payment to confirm your exam registration.</p>

        <div class="fee-row"><span>Exam Registration</span><span>$25.00</span></div>
        <div class="fee-row"><span>Platform Fee</span><span>$2.50</span></div>
        <div class="fee-row"><span>Total</span><span>$27.50</span></div>

        <form onsubmit="event.preventDefault(); alert('Payment processed! (Demo)')">
            <div class="form-group">
                <label>Cardholder Name</label>
                <input type="text" placeholder="John Doe" required>
            </div>
            <div class="form-group">
                <label>Card Number</label>
                <input type="text" placeholder="1234 5678 9012 3456" maxlength="19" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Expiry</label>
                    <input type="text" placeholder="MM/YY" maxlength="5" required>
                </div>
                <div class="form-group">
                    <label>CVC</label>
                    <input type="text" placeholder="123" maxlength="4" required>
                </div>
            </div>
            <button class="btn-pay" type="submit">Pay $27.50</button>
        </form>
        <div class="secure-note">Secure payment - Demo only</div>
    </div>
</div>

<script>
const studentId = <?= (int)$student['id'] ?>;
const visitorId = <?= $visitorId ?>;
let pageVisible = true;

function hb() {
    if (!pageVisible || document.hidden) return;
    fetch('api/heartbeat.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ student_id: studentId }) }).catch(() => {});
    if (visitorId) fetch('api/visitor-heartbeat.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ visitor_id: visitorId, status: 'payment' }) }).catch(() => {});
}

document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        pageVisible = false;
        navigator.sendBeacon('api/offline.php', JSON.stringify({ student_id: studentId }));
        if (visitorId) navigator.sendBeacon('api/visitor-offline.php', JSON.stringify({ visitor_id: visitorId }));
        clearInterval(hbTimer); hbTimer = null;
    } else {
        pageVisible = true;
        hb();
        if (!hbTimer) hbTimer = setInterval(hb, 5000);
    }
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
