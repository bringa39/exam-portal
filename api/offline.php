<?php
require_once __DIR__ . '/../includes/functions.php';

$input = json_decode(file_get_contents('php://input'), true);
$studentId = (int)($input['student_id'] ?? 0);

if ($studentId) {
    $db = getDB();
    $db->prepare("UPDATE students SET is_online = 0, last_activity = datetime('now') WHERE id = ?")->execute([$studentId]);
    logActivity($studentId, 'disconnected', 'Student left the page');
}

echo json_encode(['status' => 'ok']);
