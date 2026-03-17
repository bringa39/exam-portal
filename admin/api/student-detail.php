<?php
session_start();
require_once __DIR__ . '/../../includes/functions.php';

if (empty($_SESSION['is_admin'])) { jsonResponse(['error' => 'Unauthorized'], 401); }

$id = (int)($_GET['id'] ?? 0);
if (!$id) jsonResponse(['error' => 'Invalid ID'], 400);

$db = getDB();
$stmt = $db->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();
if (!$student) jsonResponse(['error' => 'Not found'], 404);

// Get geo data from linked visitor record
$geo = ['country' => '', 'city' => '', 'region' => ''];
if (!empty($student['visitor_id'])) {
    $vstmt = $db->prepare("SELECT country, city, region FROM visitors WHERE id = ?");
    $vstmt->execute([$student['visitor_id']]);
    $vdata = $vstmt->fetch();
    if ($vdata) $geo = $vdata;
}
$student['country'] = $geo['country'];
$student['city'] = $geo['city'];
$student['region'] = $geo['region'];

$stmt = $db->prepare("SELECT * FROM activity_log WHERE student_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$id]);

jsonResponse(['student' => $student, 'logs' => $stmt->fetchAll()]);
