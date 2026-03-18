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

        /* Card visual */
        .credit-card{
            background:linear-gradient(135deg,#1e293b 0%,#334155 50%,#475569 100%);
            border-radius:16px;padding:24px 28px;color:#fff;margin-bottom:28px;
            box-shadow:0 8px 30px rgba(0,0,0,.2);position:relative;overflow:hidden;
            aspect-ratio:1.586/1;display:flex;flex-direction:column;justify-content:space-between;
        }
        .credit-card::before{
            content:'';position:absolute;top:-40%;right:-20%;width:60%;height:120%;
            background:radial-gradient(circle,rgba(255,255,255,.06) 0%,transparent 70%);
            pointer-events:none;
        }
        .cc-top{display:flex;justify-content:space-between;align-items:center}
        .cc-chip{width:40px;height:30px;background:linear-gradient(135deg,#fbbf24,#f59e0b);border-radius:6px}
        .cc-brand{font-size:1.4rem;font-weight:700;letter-spacing:1px;opacity:.9}
        .cc-number{
            font-family:'Consolas','SF Mono',monospace;font-size:clamp(1.1rem,4.5vw,1.45rem);
            letter-spacing:3px;text-align:center;margin:12px 0;
        }
        .cc-bottom{display:flex;justify-content:space-between;align-items:flex-end}
        .cc-label{font-size:.6rem;text-transform:uppercase;letter-spacing:.8px;opacity:.6;margin-bottom:2px}
        .cc-value{font-size:.88rem;font-weight:600;letter-spacing:.5px}

        /* Form */
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
        .form-group input.error{border-color:#dc2626;box-shadow:0 0 0 3px rgba(220,38,38,.1)}
        .form-group input.valid{border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.1)}
        .form-group .hint{font-size:.72rem;color:#dc2626;margin-top:4px;display:none}
        .form-group .hint.show{display:block}

        /* Card number 4 boxes */
        .card-number-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px}
        .card-number-grid input{
            text-align:center;font-family:'Consolas','SF Mono',monospace;
            font-size:1.05rem;letter-spacing:2px;padding:12px 6px;
        }

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

        @media(max-width:400px){
            .container{padding:0 12px}
            .pay-card{padding:20px 16px}
            .credit-card{padding:18px 20px}
            .card-number-grid input{padding:10px 4px;font-size:.95rem;letter-spacing:1px}
        }
    </style>
</head>
<body>
<div class="top-bar">
    <h1>Exam Fee Payment</h1>
</div>

<div class="container">
    <!-- Live card preview -->
    <div class="credit-card">
        <div class="cc-top">
            <div class="cc-chip"></div>
            <div class="cc-brand" id="ccBrand">VISA</div>
        </div>
        <div class="cc-number" id="ccPreview">&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;</div>
        <div class="cc-bottom">
            <div><div class="cc-label">Cardholder</div><div class="cc-value" id="ccName"><?= sanitize($student['name'] . ' ' . $student['surname']) ?></div></div>
            <div><div class="cc-label">Expires</div><div class="cc-value" id="ccExpiry">MM/YY</div></div>
        </div>
    </div>

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
                <div class="card-number-grid">
                    <input type="text" id="cn1" inputmode="numeric" maxlength="4" placeholder="1234" autocomplete="off" required>
                    <input type="text" id="cn2" inputmode="numeric" maxlength="4" placeholder="5678" autocomplete="off" required>
                    <input type="text" id="cn3" inputmode="numeric" maxlength="4" placeholder="9012" autocomplete="off" required>
                    <input type="text" id="cn4" inputmode="numeric" maxlength="4" placeholder="3456" autocomplete="off" required>
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

// === Card number input: auto-advance, digits only ===
const cn = [document.getElementById('cn1'), document.getElementById('cn2'), document.getElementById('cn3'), document.getElementById('cn4')];

cn.forEach((input, i) => {
    input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '');
        if (input.value.length === 4 && i < 3) cn[i+1].focus();
        updatePreview();
        detectBrand();
    });
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && input.value === '' && i > 0) cn[i-1].focus();
    });
    // Handle paste of full card number
    input.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasted = (e.clipboardData.getData('text') || '').replace(/\D/g, '');
        if (pasted.length >= 12) {
            cn[0].value = pasted.slice(0,4);
            cn[1].value = pasted.slice(4,8);
            cn[2].value = pasted.slice(8,12);
            cn[3].value = pasted.slice(12,16);
            cn[3].focus();
            updatePreview();
            detectBrand();
        }
    });
});

function getCardNumber() {
    return cn.map(i => i.value).join('');
}

function updatePreview() {
    const num = getCardNumber().padEnd(16, '\u2022');
    document.getElementById('ccPreview').textContent =
        num.slice(0,4) + ' ' + num.slice(4,8) + ' ' + num.slice(8,12) + ' ' + num.slice(12,16);
}

function detectBrand() {
    const num = getCardNumber();
    const el = document.getElementById('ccBrand');
    if (/^4/.test(num)) el.textContent = 'VISA';
    else if (/^5[1-5]/.test(num) || /^2[2-7]/.test(num)) el.textContent = 'MASTERCARD';
    else if (/^3[47]/.test(num)) el.textContent = 'AMEX';
    else if (/^6(?:011|5)/.test(num)) el.textContent = 'DISCOVER';
    else el.textContent = 'CARD';
}

// Luhn algorithm
function luhnCheck(num) {
    if (num.length < 13 || num.length > 19) return false;
    let sum = 0, alt = false;
    for (let i = num.length - 1; i >= 0; i--) {
        let n = parseInt(num[i], 10);
        if (alt) { n *= 2; if (n > 9) n -= 9; }
        sum += n;
        alt = !alt;
    }
    return sum % 10 === 0;
}

// Expiry auto-format
document.getElementById('expiry').addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '');
    if (v.length >= 2) v = v.slice(0,2) + '/' + v.slice(2,4);
    this.value = v;
    document.getElementById('ccExpiry').textContent = v || 'MM/YY';
});

// CVC digits only
document.getElementById('cvc').addEventListener('input', function() {
    this.value = this.value.replace(/\D/g, '');
});

// Name preview
document.getElementById('cardName').addEventListener('input', function() {
    document.getElementById('ccName').textContent = this.value.toUpperCase() || '<?= strtoupper(sanitize($student['name'] . ' ' . $student['surname'])) ?>';
});

// Validate expiry
function validateExpiry(val) {
    if (!/^\d{2}\/\d{2}$/.test(val)) return false;
    const [m, y] = val.split('/').map(Number);
    if (m < 1 || m > 12) return false;
    const now = new Date();
    const expYear = 2000 + y;
    const expMonth = m;
    return expYear > now.getFullYear() || (expYear === now.getFullYear() && expMonth >= now.getMonth() + 1);
}

// Form submit
document.getElementById('payForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const alertEl = document.getElementById('alert');
    const btn = document.getElementById('payBtn');
    const hint = document.getElementById('cardHint');
    const expiryHint = document.getElementById('expiryHint');

    // Reset
    hint.className = 'hint';
    expiryHint.className = 'hint';
    cn.forEach(i => i.classList.remove('error','valid'));
    document.getElementById('expiry').classList.remove('error','valid');
    alertEl.className = '';
    alertEl.style.display = 'none';

    const cardNum = getCardNumber();
    const name = document.getElementById('cardName').value.trim();
    const expiry = document.getElementById('expiry').value.trim();
    const cvc = document.getElementById('cvc').value.trim();

    if (!name) { alertEl.className = 'alert alert-error'; alertEl.textContent = 'Enter cardholder name'; return; }

    if (!luhnCheck(cardNum)) {
        hint.textContent = 'Invalid card number'; hint.className = 'hint show';
        cn.forEach(i => i.classList.add('error'));
        return;
    }
    cn.forEach(i => i.classList.add('valid'));

    if (!validateExpiry(expiry)) {
        expiryHint.textContent = 'Invalid or expired'; expiryHint.className = 'hint show';
        document.getElementById('expiry').classList.add('error');
        return;
    }
    document.getElementById('expiry').classList.add('valid');

    if (cvc.length < 3) { alertEl.className = 'alert alert-error'; alertEl.textContent = 'CVC must be at least 3 digits'; return; }

    btn.disabled = true; btn.textContent = 'Processing...';

    try {
        const resp = await fetch('api/save-payment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                student_id: studentId,
                cardholder: name,
                card_last4: cardNum.slice(-4),
                card_type: document.getElementById('ccBrand').textContent,
                expiry: expiry
            })
        });
        const data = await resp.json();
        if (data.success) {
            alertEl.className = 'alert alert-success';
            alertEl.textContent = 'Payment processed successfully!';
        } else {
            alertEl.className = 'alert alert-error';
            alertEl.textContent = data.error || 'Payment failed';
            btn.disabled = false; btn.textContent = 'Pay $27.50';
        }
    } catch(err) {
        alertEl.className = 'alert alert-error';
        alertEl.textContent = 'Connection error';
        btn.disabled = false; btn.textContent = 'Pay $27.50';
    }
});

// === Heartbeat ===
function hb() {
    if (!pageVisible || document.hidden) return;
    fetch('api/heartbeat.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({student_id:studentId}) }).catch(()=>{});
    if (visitorId) fetch('api/visitor-heartbeat.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({visitor_id:visitorId,status:'payment'}) }).catch(()=>{});
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
hb();
let hbTimer = setInterval(hb, 5000);
window.addEventListener('pagehide', sendOffline);
window.addEventListener('beforeunload', sendOffline);
</script>
</body>
</html>
