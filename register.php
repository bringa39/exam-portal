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
        .form-group textarea,
        .form-group select {
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
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,.1);
        }
        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #94a3b8;
        }
        .form-group select {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
            cursor: pointer;
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
            .form-group input, .form-group textarea, .form-group select { padding: 10px 12px; font-size: .9rem; }
            .btn { padding: 12px 20px; font-size: .92rem; }
        }

        /* Prefers reduced motion */
        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>
<div class="hero">
    <div class="hero-content">
        <div class="hero-badge" data-i18n="badge">Secure Examination Platform</div>
        <h1 data-i18n="site_title">Exam Portal</h1>
        <p data-i18n="reg_hero_text">Fill in your information below to register for your examination session.</p>
    </div>
</div>

<div class="container">
    <div class="card">
        <h2 class="card-title" data-i18n="reg_title">Student Registration</h2>
        <p class="card-subtitle" data-i18n="reg_subtitle">All fields are required. Make sure your information is accurate.</p>

        <div id="alert" class="alert"></div>

        <form id="registrationForm" novalidate>
            <div class="form-row">
                <div class="form-group">
                    <label for="name" data-i18n="lbl_firstname">First Name</label>
                    <input type="text" id="name" name="name" placeholder="John" required>
                </div>
                <div class="form-group">
                    <label for="surname" data-i18n="lbl_lastname">Last Name</label>
                    <input type="text" id="surname" name="surname" placeholder="Doe" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email" data-i18n="lbl_email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="john.doe@example.com" required>
                </div>
                <div class="form-group">
                    <label for="phone" data-i18n="lbl_phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="+1 234 567 890" required>
                </div>
            </div>

            <div class="form-group">
                <label for="street" data-i18n="lbl_street">Street Address</label>
                <input type="text" id="street" name="street" placeholder="123 Main Street, Apt 4B" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city" data-i18n="lbl_city">City</label>
                    <input type="text" id="city" name="city" placeholder="New York" required>
                </div>
                <div class="form-group">
                    <label for="state" data-i18n="lbl_state">State / Region</label>
                    <input type="text" id="state" name="state" placeholder="NY" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="zip" data-i18n="lbl_zip">ZIP / Postal Code</label>
                    <input type="text" id="zip" name="zip" placeholder="10001" required>
                </div>
                <div class="form-group">
                    <label for="country" data-i18n="lbl_country">Country</label>
                    <select id="country" name="country" required>
                        <option value="" disabled selected>Select country...</option>
                        <option value="Afghanistan">&#127462;&#127467; Afghanistan</option>
                        <option value="Albania">&#127462;&#127473; Albania</option>
                        <option value="Algeria">&#127465;&#127487; Algeria</option>
                        <option value="Andorra">&#127462;&#127465; Andorra</option>
                        <option value="Angola">&#127462;&#127476; Angola</option>
                        <option value="Argentina">&#127462;&#127479; Argentina</option>
                        <option value="Armenia">&#127462;&#127474; Armenia</option>
                        <option value="Australia">&#127462;&#127482; Australia</option>
                        <option value="Austria">&#127462;&#127481; Austria</option>
                        <option value="Azerbaijan">&#127462;&#127487; Azerbaijan</option>
                        <option value="Bahamas">&#127463;&#127480; Bahamas</option>
                        <option value="Bahrain">&#127463;&#127469; Bahrain</option>
                        <option value="Bangladesh">&#127463;&#127465; Bangladesh</option>
                        <option value="Barbados">&#127463;&#127463; Barbados</option>
                        <option value="Belarus">&#127463;&#127486; Belarus</option>
                        <option value="Belgium">&#127463;&#127466; Belgium</option>
                        <option value="Belize">&#127463;&#127487; Belize</option>
                        <option value="Benin">&#127463;&#127471; Benin</option>
                        <option value="Bhutan">&#127463;&#127481; Bhutan</option>
                        <option value="Bolivia">&#127463;&#127476; Bolivia</option>
                        <option value="Bosnia and Herzegovina">&#127463;&#127462; Bosnia and Herzegovina</option>
                        <option value="Botswana">&#127463;&#127484; Botswana</option>
                        <option value="Brazil">&#127463;&#127479; Brazil</option>
                        <option value="Brunei">&#127463;&#127475; Brunei</option>
                        <option value="Bulgaria">&#127463;&#127468; Bulgaria</option>
                        <option value="Burkina Faso">&#127463;&#127467; Burkina Faso</option>
                        <option value="Burundi">&#127463;&#127470; Burundi</option>
                        <option value="Cambodia">&#127472;&#127469; Cambodia</option>
                        <option value="Cameroon">&#127464;&#127474; Cameroon</option>
                        <option value="Canada">&#127464;&#127462; Canada</option>
                        <option value="Cape Verde">&#127464;&#127483; Cape Verde</option>
                        <option value="Central African Republic">&#127464;&#127467; Central African Republic</option>
                        <option value="Chad">&#127481;&#127465; Chad</option>
                        <option value="Chile">&#127464;&#127473; Chile</option>
                        <option value="China">&#127464;&#127475; China</option>
                        <option value="Colombia">&#127464;&#127476; Colombia</option>
                        <option value="Comoros">&#127472;&#127474; Comoros</option>
                        <option value="Congo">&#127464;&#127468; Congo</option>
                        <option value="Costa Rica">&#127464;&#127479; Costa Rica</option>
                        <option value="Croatia">&#127469;&#127479; Croatia</option>
                        <option value="Cuba">&#127464;&#127482; Cuba</option>
                        <option value="Cyprus">&#127464;&#127486; Cyprus</option>
                        <option value="Czech Republic">&#127464;&#127487; Czech Republic</option>
                        <option value="Denmark">&#127465;&#127472; Denmark</option>
                        <option value="Djibouti">&#127465;&#127471; Djibouti</option>
                        <option value="Dominican Republic">&#127465;&#127476; Dominican Republic</option>
                        <option value="DR Congo">&#127464;&#127465; DR Congo</option>
                        <option value="Ecuador">&#127466;&#127464; Ecuador</option>
                        <option value="Egypt">&#127466;&#127468; Egypt</option>
                        <option value="El Salvador">&#127480;&#127483; El Salvador</option>
                        <option value="Equatorial Guinea">&#127468;&#127478; Equatorial Guinea</option>
                        <option value="Eritrea">&#127466;&#127479; Eritrea</option>
                        <option value="Estonia">&#127466;&#127466; Estonia</option>
                        <option value="Eswatini">&#127480;&#127487; Eswatini</option>
                        <option value="Ethiopia">&#127466;&#127481; Ethiopia</option>
                        <option value="Fiji">&#127467;&#127471; Fiji</option>
                        <option value="Finland">&#127467;&#127470; Finland</option>
                        <option value="France">&#127467;&#127479; France</option>
                        <option value="Gabon">&#127468;&#127462; Gabon</option>
                        <option value="Gambia">&#127468;&#127474; Gambia</option>
                        <option value="Georgia">&#127468;&#127466; Georgia</option>
                        <option value="Germany">&#127465;&#127466; Germany</option>
                        <option value="Ghana">&#127468;&#127469; Ghana</option>
                        <option value="Greece">&#127468;&#127479; Greece</option>
                        <option value="Guatemala">&#127468;&#127481; Guatemala</option>
                        <option value="Guinea">&#127468;&#127475; Guinea</option>
                        <option value="Guyana">&#127468;&#127486; Guyana</option>
                        <option value="Haiti">&#127469;&#127481; Haiti</option>
                        <option value="Honduras">&#127469;&#127475; Honduras</option>
                        <option value="Hungary">&#127469;&#127482; Hungary</option>
                        <option value="Iceland">&#127470;&#127480; Iceland</option>
                        <option value="India">&#127470;&#127475; India</option>
                        <option value="Indonesia">&#127470;&#127465; Indonesia</option>
                        <option value="Iran">&#127470;&#127479; Iran</option>
                        <option value="Iraq">&#127470;&#127478; Iraq</option>
                        <option value="Ireland">&#127470;&#127466; Ireland</option>
                        <option value="Israel">&#127470;&#127473; Israel</option>
                        <option value="Italy">&#127470;&#127481; Italy</option>
                        <option value="Ivory Coast">&#127464;&#127470; Ivory Coast</option>
                        <option value="Jamaica">&#127471;&#127474; Jamaica</option>
                        <option value="Japan">&#127471;&#127477; Japan</option>
                        <option value="Jordan">&#127471;&#127476; Jordan</option>
                        <option value="Kazakhstan">&#127472;&#127487; Kazakhstan</option>
                        <option value="Kenya">&#127472;&#127466; Kenya</option>
                        <option value="Kuwait">&#127472;&#127484; Kuwait</option>
                        <option value="Kyrgyzstan">&#127472;&#127468; Kyrgyzstan</option>
                        <option value="Laos">&#127473;&#127462; Laos</option>
                        <option value="Latvia">&#127473;&#127483; Latvia</option>
                        <option value="Lebanon">&#127473;&#127463; Lebanon</option>
                        <option value="Lesotho">&#127473;&#127480; Lesotho</option>
                        <option value="Liberia">&#127473;&#127479; Liberia</option>
                        <option value="Libya">&#127473;&#127486; Libya</option>
                        <option value="Liechtenstein">&#127473;&#127470; Liechtenstein</option>
                        <option value="Lithuania">&#127473;&#127481; Lithuania</option>
                        <option value="Luxembourg">&#127473;&#127482; Luxembourg</option>
                        <option value="Madagascar">&#127474;&#127468; Madagascar</option>
                        <option value="Malawi">&#127474;&#127484; Malawi</option>
                        <option value="Malaysia">&#127474;&#127486; Malaysia</option>
                        <option value="Maldives">&#127474;&#127483; Maldives</option>
                        <option value="Mali">&#127474;&#127473; Mali</option>
                        <option value="Malta">&#127474;&#127481; Malta</option>
                        <option value="Mauritania">&#127474;&#127479; Mauritania</option>
                        <option value="Mauritius">&#127474;&#127482; Mauritius</option>
                        <option value="Mexico">&#127474;&#127485; Mexico</option>
                        <option value="Moldova">&#127474;&#127465; Moldova</option>
                        <option value="Monaco">&#127474;&#127464; Monaco</option>
                        <option value="Mongolia">&#127474;&#127475; Mongolia</option>
                        <option value="Montenegro">&#127474;&#127466; Montenegro</option>
                        <option value="Morocco">&#127474;&#127462; Morocco</option>
                        <option value="Mozambique">&#127474;&#127487; Mozambique</option>
                        <option value="Myanmar">&#127474;&#127474; Myanmar</option>
                        <option value="Namibia">&#127475;&#127462; Namibia</option>
                        <option value="Nepal">&#127475;&#127477; Nepal</option>
                        <option value="Netherlands">&#127475;&#127473; Netherlands</option>
                        <option value="New Zealand">&#127475;&#127487; New Zealand</option>
                        <option value="Nicaragua">&#127475;&#127470; Nicaragua</option>
                        <option value="Niger">&#127475;&#127466; Niger</option>
                        <option value="Nigeria">&#127475;&#127468; Nigeria</option>
                        <option value="North Korea">&#127472;&#127477; North Korea</option>
                        <option value="North Macedonia">&#127474;&#127472; North Macedonia</option>
                        <option value="Norway">&#127475;&#127476; Norway</option>
                        <option value="Oman">&#127476;&#127474; Oman</option>
                        <option value="Pakistan">&#127477;&#127472; Pakistan</option>
                        <option value="Palestine">&#127477;&#127480; Palestine</option>
                        <option value="Panama">&#127477;&#127462; Panama</option>
                        <option value="Papua New Guinea">&#127477;&#127468; Papua New Guinea</option>
                        <option value="Paraguay">&#127477;&#127486; Paraguay</option>
                        <option value="Peru">&#127477;&#127466; Peru</option>
                        <option value="Philippines">&#127477;&#127469; Philippines</option>
                        <option value="Poland">&#127477;&#127473; Poland</option>
                        <option value="Portugal">&#127477;&#127481; Portugal</option>
                        <option value="Qatar">&#127478;&#127462; Qatar</option>
                        <option value="Romania">&#127479;&#127476; Romania</option>
                        <option value="Russia">&#127479;&#127482; Russia</option>
                        <option value="Rwanda">&#127479;&#127484; Rwanda</option>
                        <option value="Saudi Arabia">&#127480;&#127462; Saudi Arabia</option>
                        <option value="Senegal">&#127480;&#127475; Senegal</option>
                        <option value="Serbia">&#127479;&#127480; Serbia</option>
                        <option value="Sierra Leone">&#127480;&#127473; Sierra Leone</option>
                        <option value="Singapore">&#127480;&#127468; Singapore</option>
                        <option value="Slovakia">&#127480;&#127472; Slovakia</option>
                        <option value="Slovenia">&#127480;&#127470; Slovenia</option>
                        <option value="Somalia">&#127480;&#127476; Somalia</option>
                        <option value="South Africa">&#127487;&#127462; South Africa</option>
                        <option value="South Korea">&#127472;&#127479; South Korea</option>
                        <option value="South Sudan">&#127480;&#127480; South Sudan</option>
                        <option value="Spain">&#127466;&#127480; Spain</option>
                        <option value="Sri Lanka">&#127473;&#127472; Sri Lanka</option>
                        <option value="Sudan">&#127480;&#127465; Sudan</option>
                        <option value="Suriname">&#127480;&#127479; Suriname</option>
                        <option value="Sweden">&#127480;&#127466; Sweden</option>
                        <option value="Switzerland">&#127464;&#127469; Switzerland</option>
                        <option value="Syria">&#127480;&#127486; Syria</option>
                        <option value="Taiwan">&#127481;&#127484; Taiwan</option>
                        <option value="Tajikistan">&#127481;&#127471; Tajikistan</option>
                        <option value="Tanzania">&#127481;&#127487; Tanzania</option>
                        <option value="Thailand">&#127481;&#127469; Thailand</option>
                        <option value="Togo">&#127481;&#127468; Togo</option>
                        <option value="Trinidad and Tobago">&#127481;&#127481; Trinidad and Tobago</option>
                        <option value="Tunisia">&#127481;&#127475; Tunisia</option>
                        <option value="Turkey">&#127481;&#127479; Turkey</option>
                        <option value="Turkmenistan">&#127481;&#127474; Turkmenistan</option>
                        <option value="Uganda">&#127482;&#127468; Uganda</option>
                        <option value="Ukraine">&#127482;&#127462; Ukraine</option>
                        <option value="United Arab Emirates">&#127462;&#127466; United Arab Emirates</option>
                        <option value="United Kingdom">&#127468;&#127463; United Kingdom</option>
                        <option value="United States">&#127482;&#127480; United States</option>
                        <option value="Uruguay">&#127482;&#127486; Uruguay</option>
                        <option value="Uzbekistan">&#127482;&#127487; Uzbekistan</option>
                        <option value="Venezuela">&#127483;&#127466; Venezuela</option>
                        <option value="Vietnam">&#127483;&#127475; Vietnam</option>
                        <option value="Yemen">&#127486;&#127466; Yemen</option>
                        <option value="Zambia">&#127487;&#127474; Zambia</option>
                        <option value="Zimbabwe">&#127487;&#127484; Zimbabwe</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="submitBtn" data-i18n="btn_submit">Submit</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
// === Visitor tracking ===
let visitorStatus = 'viewing';
let heartbeatTimer = null;
let pageVisible = true;
let navigatingAway = false; // flag to prevent offline on same-site navigation

function sendOffline() {
    if (navigatingAway || !window._visitorId) return;
    navigator.sendBeacon('api/visitor-offline.php', JSON.stringify({ visitor_id: window._visitorId }));
}

function startHeartbeat() {
    if (heartbeatTimer) return;
    pageVisible = true;
    // Send one immediately when becoming visible
    if (window._visitorId) {
        fetch('api/visitor-heartbeat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ visitor_id: window._visitorId, status: visitorStatus })
        }).catch(() => {});
    }
    heartbeatTimer = setInterval(() => {
        // Double-check page is actually visible before sending
        if (!pageVisible || document.hidden) return;
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
    pageVisible = false;
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

// === Form logic ===
document.getElementById('registrationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const alertEl = document.getElementById('alert');
    const btn = document.getElementById('submitBtn');

    const name = document.getElementById('name').value.trim();
    const surname = document.getElementById('surname').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const street = document.getElementById('street').value.trim();
    const city = document.getElementById('city').value.trim();
    const state = document.getElementById('state').value.trim();
    const zip = document.getElementById('zip').value.trim();
    const country = document.getElementById('country').value.trim();

    if (!name || !surname || !email || !phone || !street || !city || !zip || !country) {
        alertEl.className = 'alert alert-error'; alertEl.style.display = 'block';
        alertEl.textContent = i18n.t('msg_fill_all'); return;
    }
    const address = JSON.stringify({ street, city, state, zip, country });

    btn.disabled = true; btn.textContent = i18n.t('msg_submitting');
    visitorStatus = 'registered';

    try {
        const resp = await fetch('api/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name, surname, email, phone, address, policies: true,
                screen_resolution: screen.width + 'x' + screen.height,
                language: navigator.language || 'unknown',
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || 'unknown',
                user_agent: navigator.userAgent
            })
        });
        const data = await resp.json();

        if (data.success) {
            alertEl.className = 'alert alert-success'; alertEl.style.display = 'block';
            alertEl.textContent = i18n.t('msg_success_redirect');
            navigatingAway = true;
            stopHeartbeat();
            setTimeout(() => window.location.href = 'waiting.php', 1000);
        } else {
            alertEl.className = 'alert alert-error'; alertEl.style.display = 'block';
            alertEl.textContent = data.error || i18n.t('msg_submit_fail');
            btn.disabled = false; btn.textContent = i18n.t('btn_submit');
            visitorStatus = 'filling_form';
        }
    } catch (err) {
        alertEl.className = 'alert alert-error'; alertEl.style.display = 'block';
        alertEl.textContent = i18n.t('msg_conn_error');
        btn.disabled = false; btn.textContent = i18n.t('btn_submit');
        visitorStatus = 'filling_form';
    }
});
</script>
</body>
</html>
