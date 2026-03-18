<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(['error' => 'Invalid request body'], 400);
}

$name = sanitize($input['name'] ?? '');
$surname = sanitize($input['surname'] ?? '');
$email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
$phone = sanitize($input['phone'] ?? '');
$address = sanitize($input['address'] ?? '');

if (!$name || !$surname || !$email || !$phone || !$address) {
    jsonResponse(['error' => 'All fields are required.'], 400);
}

$ip = getClientIP();
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$parsed = parseUserAgent($userAgent);
$screenRes = sanitize($input['screen_resolution'] ?? '');
$language = sanitize($input['language'] ?? '');
$timezone = sanitize($input['timezone'] ?? '');
$token = generateToken();

try {
    $db = getDB();

    $stmt = $db->prepare("SELECT id FROM students WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $db->prepare("UPDATE students SET
            name=?, surname=?, phone=?, address=?, ip_address=?, user_agent=?,
            browser=?, device=?, os=?, screen_resolution=?, language=?,
            timezone=?, policies_accepted=1, session_token=?, is_online=1,
            last_activity=datetime('now') WHERE id=?");
        $stmt->execute([$name, $surname, $phone, $address, $ip, $userAgent,
            $parsed['browser'], $parsed['device'], $parsed['os'],
            $screenRes, $language, $timezone, $token, $existing['id']]);
        $studentId = $existing['id'];
        logActivity($studentId, 're-registered', "Re-registered from IP: $ip");
    } else {
        $stmt = $db->prepare("INSERT INTO students
            (name,surname,email,phone,address,ip_address,user_agent,browser,device,os,
             screen_resolution,language,timezone,policies_accepted,session_token,is_online,last_activity)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,1,datetime('now'))");
        $stmt->execute([$name, $surname, $email, $phone, $address, $ip, $userAgent,
            $parsed['browser'], $parsed['device'], $parsed['os'],
            $screenRes, $language, $timezone, $token]);
        $studentId = $db->lastInsertId();
        logActivity($studentId, 'registered', "New student from IP: $ip");
    }

    // Link visitor record to student
    if (!empty($_SESSION['visitor_id'])) {
        $visitorId = (int)$_SESSION['visitor_id'];
        $db->prepare("UPDATE visitors SET student_id = ?, status = 'waiting', is_online = 1, last_activity = datetime('now') WHERE id = ?")->execute([$studentId, $visitorId]);
        $db->prepare("UPDATE students SET visitor_id = ? WHERE id = ?")->execute([$visitorId, $studentId]);
    }

    $_SESSION['student_token'] = $token;
    $_SESSION['student_id'] = $studentId;

    sendTelegramAlert('registration', [
        'name' => $name . ' ' . $surname,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'ip' => $ip
    ]);

    jsonResponse(['success' => true, 'token' => $token]);
} catch (PDOException $e) {
    jsonResponse(['error' => 'Registration failed. Please try again.'], 500);
}
