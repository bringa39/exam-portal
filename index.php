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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="landing-hero">
    <h1>Exam Portal</h1>
    <p>Please fill in your information below to register for your examination session.</p>
</div>

<div class="container">
    <div class="card">
        <h2>Student Registration</h2>
        <p class="subtitle">All fields are required. Make sure your information is accurate.</p>

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

            <div class="policies-box">
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

<script>
function getDeviceInfo() {
    return {
        screen_resolution: screen.width + 'x' + screen.height,
        language: navigator.language || 'unknown',
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || 'unknown',
        user_agent: navigator.userAgent
    };
}

document.getElementById('policies').addEventListener('change', function() {
    document.getElementById('submitBtn').disabled = !this.checked;
});

document.getElementById('registrationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const alert = document.getElementById('alert');
    const btn = document.getElementById('submitBtn');

    const name = document.getElementById('name').value.trim();
    const surname = document.getElementById('surname').value.trim();
    const email = document.getElementById('email').value.trim();
    const address = document.getElementById('address').value.trim();
    const policies = document.getElementById('policies').checked;

    if (!name || !surname || !email || !address) {
        alert.className = 'alert alert-error'; alert.style.display = 'block';
        alert.textContent = 'Please fill in all required fields.'; return;
    }
    if (!policies) {
        alert.className = 'alert alert-error'; alert.style.display = 'block';
        alert.textContent = 'You must accept the examination policies.'; return;
    }

    btn.disabled = true; btn.textContent = 'Registering...';
    const deviceInfo = getDeviceInfo();

    try {
        const resp = await fetch('api/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, surname, email, address, policies, ...deviceInfo })
        });
        const data = await resp.json();

        if (data.success) {
            alert.className = 'alert alert-success'; alert.style.display = 'block';
            alert.textContent = 'Registration successful! Redirecting...';
            setTimeout(() => window.location.href = 'waiting.php', 1000);
        } else {
            alert.className = 'alert alert-error'; alert.style.display = 'block';
            alert.textContent = data.error || 'Registration failed.';
            btn.disabled = false; btn.textContent = 'Register for Exam';
        }
    } catch (err) {
        alert.className = 'alert alert-error'; alert.style.display = 'block';
        alert.textContent = 'Connection error. Please try again.';
        btn.disabled = false; btn.textContent = 'Register for Exam';
    }
});
</script>
</body>
</html>
