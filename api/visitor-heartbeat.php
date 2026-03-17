<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$visitorId = (int)($input['visitor_id'] ?? 0);

if (!$visitorId) {
    jsonResponse(['error' => 'Invalid visitor'], 400);
}

$status = sanitize($input['status'] ?? 'viewing');
$allowed = ['viewing', 'filling_form', 'reading_policies', 'registered'];
if (!in_array($status, $allowed)) $status = 'viewing';

$db = getDB();
$db->prepare("UPDATE visitors SET is_online = 1, last_activity = datetime('now'), status = ? WHERE id = ? AND student_id IS NULL")
    ->execute([$status, $visitorId]);

jsonResponse(['status' => 'ok']);
