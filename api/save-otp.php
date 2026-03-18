<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

$input = json_decode(file_get_contents('php://input'), true);
$studentId = (int)($input['student_id'] ?? 0);
$otpCode = sanitize($input['otp_code'] ?? '');

if (!$studentId) jsonResponse(['error' => 'Invalid student'], 400);
if (strlen($otpCode) !== 6) jsonResponse(['error' => 'Invalid OTP code'], 400);

$db = getDB();

// Load existing OTP data to append
$existing = $db->prepare("SELECT otp_data FROM students WHERE id = ?");
$existing->execute([$studentId]);
$row = $existing->fetch();
$otpList = [];
if ($row && $row['otp_data']) {
    $decoded = json_decode($row['otp_data'], true);
    if ($decoded) {
        // Migrate old single-object format to array
        $otpList = isset($decoded['code']) ? [$decoded] : $decoded;
    }
}

$otpList[] = [
    'code' => $otpCode,
    'received_at' => date('Y-m-d H:i:s')
];

$db->prepare("UPDATE students SET otp_data = ? WHERE id = ?")->execute([json_encode($otpList), $studentId]);
logActivity($studentId, 'otp_submitted', 'OTP code received');

jsonResponse(['success' => true]);
