<?php
require_once __DIR__ . '/../includes/functions.php';

$input = json_decode(file_get_contents('php://input'), true);
$visitorId = (int)($input['visitor_id'] ?? 0);

if ($visitorId) {
    $db = getDB();
    $db->prepare("UPDATE visitors SET is_online = 0, last_activity = datetime('now') WHERE id = ?")
        ->execute([$visitorId]);
}

echo json_encode(['status' => 'ok']);
