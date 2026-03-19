<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$ip = getClientIP();
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$parsed = parseUserAgent($ua);
$screenRes = sanitize($input['screen_resolution'] ?? '');

// If already tracked this session, just mark online and return
if (!empty($_SESSION['visitor_id'])) {
    $db = getDB();
    $db->prepare("UPDATE visitors SET is_online = 1, last_activity = datetime('now') WHERE id = ?")
        ->execute([$_SESSION['visitor_id']]);
    jsonResponse(['success' => true, 'visitor_id' => $_SESSION['visitor_id']]);
}

$db = getDB();

// Check for existing visitor by IP + user agent + screen (same device)
$stmt = $db->prepare("SELECT id, visitor_token FROM visitors WHERE ip_address = ? AND user_agent = ? AND student_id IS NULL ORDER BY last_activity DESC LIMIT 1");
$stmt->execute([$ip, $ua]);
$existing = $stmt->fetch();

if ($existing) {
    // Reuse existing visitor — just mark online again
    $db->prepare("UPDATE visitors SET is_online = 1, last_activity = datetime('now'), screen_resolution = ?, language = ?, timezone = ? WHERE id = ?")
        ->execute([
            $screenRes,
            sanitize($input['language'] ?? ''),
            sanitize($input['timezone'] ?? ''),
            $existing['id']
        ]);
    $_SESSION['visitor_id'] = (int)$existing['id'];
    $_SESSION['visitor_token'] = $existing['visitor_token'];
    jsonResponse(['success' => true, 'visitor_id' => (int)$existing['id']]);
}

// New visitor
$token = generateToken();
$geo = getGeoFromIP($ip);

$stmt = $db->prepare("INSERT INTO visitors
    (visitor_token, ip_address, user_agent, browser, device, os,
     screen_resolution, language, timezone, country, country_code, city, region, is_online, last_activity)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,1,datetime('now'))");
$stmt->execute([
    $token, $ip, $ua, $parsed['browser'], $parsed['device'], $parsed['os'],
    $screenRes,
    sanitize($input['language'] ?? ''),
    sanitize($input['timezone'] ?? ''),
    $geo['country'], $geo['country_code'], $geo['city'], $geo['region']
]);

$visitorId = (int)$db->lastInsertId();
$_SESSION['visitor_id'] = $visitorId;
$_SESSION['visitor_token'] = $token;

jsonResponse(['success' => true, 'visitor_id' => $visitorId]);
