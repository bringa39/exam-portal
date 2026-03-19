<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
// If already registered, go straight to waiting
if (!empty($_SESSION['student_token'])) {
    $student = getStudentByToken($_SESSION['student_token']);
    if ($student) { header('Location: waiting.php'); exit; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Portal</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',system-ui,sans-serif;background:#0f172a;color:#fff;min-height:100vh;display:flex;flex-direction:column}
        .hero{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:60px 24px;background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 40%,#2563eb 100%);position:relative;overflow:hidden}
        .hero::before{content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:radial-gradient(circle at 30% 50%,rgba(59,130,246,.15) 0%,transparent 50%),radial-gradient(circle at 70% 80%,rgba(147,51,234,.1) 0%,transparent 40%);pointer-events:none}
        .hero-content{position:relative;z-index:1;max-width:640px}
        .hero-badge{display:inline-block;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.15);padding:6px 16px;border-radius:24px;font-size:.82rem;font-weight:500;letter-spacing:.3px;margin-bottom:24px}
        h1{font-size:clamp(2rem,5vw,3rem);font-weight:800;letter-spacing:-.5px;margin-bottom:16px;line-height:1.2}
        .hero p{opacity:.8;font-size:clamp(1rem,2.5vw,1.15rem);max-width:500px;margin:0 auto 40px;line-height:1.7}
        .hero-btn{display:inline-block;padding:16px 48px;background:#fff;color:#1e3a8a;border-radius:12px;font-size:1.1rem;font-weight:700;text-decoration:none;transition:all .2s;box-shadow:0 4px 16px rgba(0,0,0,.2)}
        .hero-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.3)}
        .footer{text-align:center;padding:20px;color:rgba(255,255,255,.4);font-size:.8rem;background:#0f172a}
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>
<div class="hero">
    <div class="hero-content">
        <div class="hero-badge" data-i18n="badge">Secure Examination Platform</div>
        <h1 data-i18n="site_title">Exam Portal</h1>
        <p data-i18n="reg_hero_text">Fill in your information below to register for your examination session.</p>
        <a href="waiting.php?next=register" class="hero-btn" data-i18n="btn_submit">Begin Registration</a>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
