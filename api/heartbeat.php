<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$studentId = (int)($input['student_id'] ?? 0);

if (!$studentId) {
    jsonResponse(['error' => 'Invalid student'], 400);
}

updateStudentActivity($studentId);

$db = getDB();

// Atomically claim the redirect: UPDATE first, then check if we got it.
// This prevents race conditions where two concurrent heartbeats both read is_active=1.
$db->prepare("UPDATE redirects SET is_active = 0 WHERE student_id = ? AND is_active = 1")->execute([$studentId]);

if ($db->query("SELECT changes()")->fetchColumn() > 0) {
    // We won the race — read the URL we just claimed
    $stmt = $db->prepare("SELECT target_url FROM redirects WHERE student_id = ?");
    $stmt->execute([$studentId]);
    $redirect = $stmt->fetch();
    if ($redirect) {
        logActivity($studentId, 'redirected', 'Redirected to: ' . $redirect['target_url']);
        jsonResponse(['status' => 'ok', 'redirect' => $redirect['target_url']]);
    }
}

jsonResponse(['status' => 'ok']);
