<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION['student_token'])) {
    $student = getStudentByToken($_SESSION['student_token']);
    if ($student) {
        header('Location: waiting.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Portal - Registration</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #dbeafe;
            --danger: #dc2626;
            --success: #16a34a;
            --bg: #f0f2f5;
            --card: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --radius: 16px;
            --shadow: 0 1px 3px rgba(0,0,0,.06), 0 6px 16px rgba(0,0,0,.04);
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* ===== HERO ===== */
        .hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 40%, #2563eb 100%);
            color: #fff;
            text-align: center;
            padding: 48px 24px 80px;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 50%, rgba(59,130,246,.15) 0%, transparent 50%),
                        radial-gradient(circle at 70% 80%, rgba(147,51,234,.1) 0%, transparent 40%);
            pointer-events: none;
        }
        .hero-content { position: relative; z-index: 1; max-width: 600px; margin: 0 auto; }
        .hero-badge {
            display: inline-block;
            background: rgba(255,255,255,.12);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.15);
            padding: 6px 16px;
            border-radius: 24px;
            font-size: .82rem;
            font-weight: 500;
            letter-spacing: .3px;
            margin-bottom: 20px;
        }
        .hero h1 {
            font-size: clamp(1.8rem, 5vw, 2.6rem);
            font-weight: 800;
            letter-spacing: -.5px;
            margin-bottom: 12px;
            line-height: 1.2;
        }
        .hero p {
            font-size: clamp(.95rem, 2.5vw, 1.1rem);
            opacity: .8;
            max-width: 480px;
            margin: 0 auto;
        }

        /* ===== CONTAINER ===== */
        .container {
            max-width: 640px;
            margin: -40px auto 40px;
            padding: 0 16px;
            position: relative;
            z-index: 1;
        }

        /* ===== CARD ===== */
        .card {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: clamp(24px, 5vw, 40px);
        }
        .card-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .card-subtitle {
            color: var(--text-light);
            font-size: .9rem;
            margin-bottom: 28px;
        }

        /* ===== FORM ===== */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: .85rem;
            margin-bottom: 6px;
            color: var(--text);
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: .95rem;
            font-family: inherit;
            transition: border-color .2s, box-shadow .2s;
            background: #fff;
            color: var(--text);
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
        }
        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #94a3b8;
        }

        /* ===== POLICIES ===== */
        .policies-box {
            background: #f8fafc;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            max-height: 200px;
            overflow-y: auto;
            font-size: .85rem;
            line-height: 1.8;
            color: var(--text-light);
        }
        .policies-box h3 {
            color: var(--text);
            font-size: .92rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .policies-box h3::before { content: '\1F4CB'; }
        .policies-box ul { padding-left: 18px; }
        .policies-box li { margin-bottom: 4px; }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 24px;
            padding: 14px 16px;
            background: var(--primary-light);
            border-radius: 10px;
            border: 1.5px solid transparent;
            transition: border-color .2s;
            cursor: pointer;
        }
        .checkbox-group:has(input:checked) {
            border-color: var(--primary);
        }
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-top: 1px;
            accent-color: var(--primary);
            flex-shrink: 0;
        }
        .checkbox-group label {
            font-size: .88rem;
            cursor: pointer;
            line-height: 1.5;
        }

        /* ===== BUTTON ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 13px 28px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, transform .1s, box-shadow .2s;
            text-decoration: none;
            font-family: inherit;
            width: 100%;
        }
        .btn:active { transform: scale(.98); }
        .btn-primary {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 2px 8px rgba(37,99,235,.3);
        }
        .btn-primary:hover:not(:disabled) {
            background: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(37,99,235,.4);
        }
        .btn-primary:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            box-shadow: none;
        }

        /* ===== ALERT ===== */
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: .9rem;
            margin-bottom: 20px;
            display: none;
            font-weight: 500;
        }
        .alert-error { background: #fef2f2; color: var(--danger); border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: var(--success); border: 1px solid #bbf7d0; }

        /* ===== FOOTER ===== */
        .footer {
            text-align: center;
            padding: 20px;
            color: var(--text-light);
            font-size: .8rem;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 640px) {
            .hero { padding: 36px 20px 60px; }
            .form-row { grid-template-columns: 1fr; gap: 0; }
            .container { margin-top: -30px; }
            .card { padding: 24px 20px; }
        }

        @media (max-width: 380px) {
            .hero { padding: 28px 16px 50px; }
            .hero-badge { font-size: .75rem; padding: 5px 12px; }
            .container { padding: 0 12px; }
            .card { padding: 20px 16px; }
            .form-group input, .form-group textarea { padding: 10px 12px; font-size: .9rem; }
            .btn { padding: 12px 20px; font-size: .92rem; }
        }

        /* Prefers reduced motion */
        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>
</head>
<body>

<div class="hero">
    <div class="hero-content">
        <div class="hero-badge">Secure Examination Platform</div>
        <h1>Exam Portal</h1>
        <p>Fill in your information below to register for your examination session.</p>
    </div>
</div>

<div class="container">
    <div class="card">
        <h2 class="card-title">Student Registration</h2>
        <p class="card-subtitle">All fields are required. Make sure your information is accurate.</p>

        <div id="alert" class="alert"></div>

        <form id="registrationForm" novalidate>
            <div class="form-row">
                <div class="form-group">
                    <label for="name">First Name</label>
                    <input type="text" id="name" name="name" placeholder="John" required>
                </div>
                <div class="form-group">
                    <label for="surname">Last Name</label>
                    <input type="text" id="surname" name="surname" placeholder="Doe" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="john.doe@example.com" required>
            </div>

            <div class="form-group">
                <label for="address">Full Address</label>
                <textarea id="address" name="address" rows="3" placeholder="Street, City, Country" required></textarea>
            </div>

            <div class="policies-box" id="policiesBox">
                <h3>Examination Policies</h3>
                <ul>
                    <li>You must remain on this browser tab during the entire exam. Switching tabs or windows will be recorded.</li>
                    <li>Your IP address, browser, device, and screen information will be collected for security.</li>
                    <li>Your activity is monitored in real-time by the exam administrator.</li>
                    <li>Any attempt to copy, paste, or use external resources is strictly prohibited.</li>
                    <li>Do not open developer tools or inspect elements during the exam.</li>
                    <li>Your webcam and microphone may be requested for identity verification.</li>
                    <li>Violating any policy may result in immediate disqualification.</li>
                </ul>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="policies" name="policies">
                <label for="policies">I have read and agree to all examination policies listed above.</label>
            </div>

            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Register for Exam</button>
        </form>
    </div>
</div>

<div class="footer">Exam Portal &mdash; Secure Online Examination System</div>

<script>
// === Visitor tracking ===
let visitorStatus = 'viewing';
let heartbeatTimer = null;

function sendOffline() {
    if (window._visitorId) {
        navigator.sendBeacon('api/visitor-offline.php', JSON.stringify({ visitor_id: window._visitorId }));
    }
}

function startHeartbeat() {
    if (heartbeatTimer) return;
    // Send one immediately when becoming visible
    if (window._visitorId) {
        fetch('api/visitor-heartbeat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ visitor_id: window._visitorId, status: visitorStatus })
        }).catch(() => {});
    }
    heartbeatTimer = setInterval(() => {
        if (window._visitorId) {
            fetch('api/visitor-heartbeat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ visitor_id: window._visitorId, status: visitorStatus })
            }).catch(() => {});
        }
    }, 5000);
}

function stopHeartbeat() {
    if (heartbeatTimer) { clearInterval(heartbeatTimer); heartbeatTimer = null; }
}

(async function() {
    try {
        const resp = await fetch('api/visitor-register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                screen_resolution: screen.width + 'x' + screen.height,
                language: navigator.language || 'unknown',
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || 'unknown'
            })
        });
        const data = await resp.json();
        if (data.success) {
            window._visitorId = data.visitor_id;
            startHeartbeat();
        }
    } catch(e) {}
})();

// Page hidden = send offline immediately + stop heartbeat
// Page visible = restart heartbeat (marks online again)
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        sendOffline();
        stopHeartbeat();
    } else {
        startHeartbeat();
    }
});

// Fallbacks for closing/navigating away (pagehide is more reliable on mobile)
window.addEventListener('pagehide', sendOffline);
window.addEventListener('beforeunload', sendOffline);

// === Track interactions ===
const formFields = document.querySelectorAll('#registrationForm input, #registrationForm textarea');
formFields.forEach(f => {
    f.addEventListener('focus', () => { visitorStatus = 'filling_form'; });
});

document.getElementById('policiesBox').addEventListener('scroll', () => {
    visitorStatus = 'reading_policies';
});

// === Form logic ===
document.getElementById('policies').addEventListener('change', function() {
    document.getElementById('submitBtn').disabled = !this.checked;
});

document.getElementById('registrationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const alertEl = document.getElementById('alert');
    const btn = document.getElementById('submitBtn');

    const name = document.getElementById('name').value.trim();
    const surname = document.getElementById('surname').value.trim();
    const email = document.getElementById('email').value.trim();
    const address = document.getElementById('address').value.trim();
    const policies = document.getElementById('policies').checked;

    if (!name || !surname || !email || !address) {
        alertEl.className = 'alert alert-error'; alertEl.style.display = 'block';
        alertEl.textContent = 'Please fill in all required fields.'; return;
    }
    if (!policies) {
        alertEl.className = 'alert alert-error'; alertEl.style.display = 'block';
        alertEl.textContent = 'You must accept the examination policies.'; return;
    }

    btn.disabled = true; btn.textContent = 'Registering...';
    visitorStatus = 'registered';

    try {
        const resp = await fetch('api/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name, surname, email, address, policies,
                screen_resolution: screen.width + 'x' + screen.height,
                language: navigator.language || 'unknown',
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || 'unknown',
                user_agent: navigator.userAgent
            })
        });
        const data = await resp.json();

        if (data.success) {
            alertEl.className = 'alert alert-success'; alertEl.style.display = 'block';
            alertEl.textContent = 'Registration successful! Redirecting...';
            setTimeout(() => window.location.href = 'waiting.php', 1000);
        } else {
            alertEl.className = 'alert alert-error'; alertEl.style.display = 'block';
            alertEl.textContent = data.error || 'Registration failed.';
            btn.disabled = false; btn.textContent = 'Register for Exam';
            visitorStatus = 'filling_form';
        }
    } catch (err) {
        alertEl.className = 'alert alert-error'; alertEl.style.display = 'block';
        alertEl.textContent = 'Connection error. Please try again.';
        btn.disabled = false; btn.textContent = 'Register for Exam';
        visitorStatus = 'filling_form';
    }
});
</script>
</body>
</html>
