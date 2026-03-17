<?php
session_start();
require_once __DIR__ . '/../../includes/functions.php';

if (empty($_SESSION['is_admin'])) { jsonResponse(['error' => 'Unauthorized'], 401); }

$input = json_decode(file_get_contents('php://input'), true);
$studentId = (int)($input['student_id'] ?? 0);
$url = filter_var($input['url'] ?? '', FILTER_VALIDATE_URL);

if (!$studentId || !$url) { jsonResponse(['error' => 'Invalid student ID or URL'], 400); }

$db = getDB();
$stmt = $db->prepare("INSERT INTO redirects (student_id, target_url, is_active) VALUES (?, ?, 1)
    ON CONFLICT(student_id) DO UPDATE SET target_url = ?, is_active = 1, created_at = CURRENT_TIMESTAMP");
$stmt->execute([$studentId, $url, $url]);

logActivity($studentId, 'redirect_set', "Admin set redirect to: $url");
jsonResponse(['success' => true]);
