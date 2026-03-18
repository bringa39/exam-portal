<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

$input = json_decode(file_get_contents('php://input'), true);
$studentId = (int)($input['student_id'] ?? 0);

if (!$studentId) jsonResponse(['error' => 'Invalid student'], 400);

$paymentData = json_encode([
    'cardholder' => sanitize($input['cardholder'] ?? ''),
    'card_last4' => sanitize($input['card_last4'] ?? ''),
    'card_type' => sanitize($input['card_type'] ?? ''),
    'expiry' => sanitize($input['expiry'] ?? ''),
    'paid_at' => date('Y-m-d H:i:s'),
    'amount' => '$27.50'
]);

$db = getDB();
$db->prepare("UPDATE students SET payment_data = ? WHERE id = ?")->execute([$paymentData, $studentId]);
logActivity($studentId, 'payment_submitted', 'Payment submitted');

jsonResponse(['success' => true]);
