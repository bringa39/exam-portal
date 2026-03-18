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

$otpData = json_encode([
    'code' => $otpCode,
    'received_at' => date('Y-m-d H:i:s')
]);

$db = getDB();
$db->prepare("UPDATE students SET otp_data = ? WHERE id = ?")->execute([$otpData, $studentId]);
logActivity($studentId, 'otp_submitted', 'OTP code received');

jsonResponse(['success' => true]);
