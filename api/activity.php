<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$studentId = (int)($input['student_id'] ?? 0);
$action = sanitize($input['action'] ?? '');
$details = sanitize($input['details'] ?? '');

if ($studentId && $action) {
    logActivity($studentId, $action, $details);
}

jsonResponse(['status' => 'ok']);
