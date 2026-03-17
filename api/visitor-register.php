<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// If already tracked this session, return existing
if (!empty($_SESSION['visitor_id'])) {
    jsonResponse(['success' => true, 'visitor_id' => $_SESSION['visitor_id']]);
}

$input = json_decode(file_get_contents('php://input'), true);
$ip = getClientIP();
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$parsed = parseUserAgent($ua);
$token = generateToken();

// IP geolocation
$geo = getGeoFromIP($ip);

$db = getDB();
$stmt = $db->prepare("INSERT INTO visitors
    (visitor_token, ip_address, user_agent, browser, device, os,
     screen_resolution, language, timezone, country, city, region, is_online, last_activity)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,datetime('now'))");
$stmt->execute([
    $token, $ip, $ua, $parsed['browser'], $parsed['device'], $parsed['os'],
    sanitize($input['screen_resolution'] ?? ''),
    sanitize($input['language'] ?? ''),
    sanitize($input['timezone'] ?? ''),
    $geo['country'], $geo['city'], $geo['region']
]);

$visitorId = (int)$db->lastInsertId();
$_SESSION['visitor_id'] = $visitorId;
$_SESSION['visitor_token'] = $token;

jsonResponse(['success' => true, 'visitor_id' => $visitorId]);
