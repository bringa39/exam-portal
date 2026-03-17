<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$visitorId = (int)($input['visitor_id'] ?? 0);

if (!$visitorId) {
    jsonResponse(['error' => 'Invalid visitor'], 400);
}

$db = getDB();
$db->prepare("UPDATE visitors SET is_online = 1, last_activity = datetime('now') WHERE id = ? AND student_id IS NULL")
    ->execute([$visitorId]);

jsonResponse(['status' => 'ok']);
