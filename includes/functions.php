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
    session_start();
    if (empty($_SESSION['is_admin'])) {
        header('Location: login.php');
        exit;
    }
}

function getStudentByToken(string $token): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM students WHERE session_token = ?");
    $stmt->execute([$token]);
    return $stmt->fetch() ?: null;
}

function updateStudentActivity(int $studentId, string $page = ''): void {
    $db = getDB();
    $stmt = $db->prepare("UPDATE students SET is_online = 1, last_activity = datetime('now') WHERE id = ?");
    $stmt->execute([$studentId]);
    if ($page) {
        $db->prepare("UPDATE students SET current_page = ? WHERE id = ?")->execute([$page, $studentId]);
    }
}
