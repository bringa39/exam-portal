<?php
session_start();
require_once __DIR__ . '/../../includes/functions.php';

if (empty($_SESSION['is_admin'])) { jsonResponse(['error' => 'Unauthorized'], 401); }

$db = getDB();

// Mark stale visitors offline
$db->exec("UPDATE visitors SET is_online = 0 WHERE last_activity < datetime('now', '-10 seconds') AND is_online = 1");

$visitorTotal = $db->query("SELECT COUNT(*) as cnt FROM visitors")->fetch()['cnt'];
$visitorOnline = $db->query("SELECT COUNT(*) as cnt FROM visitors WHERE is_online = 1")->fetch()['cnt'];
$visitorRegistered = $db->query("SELECT COUNT(*) as cnt FROM visitors WHERE student_id IS NOT NULL")->fetch()['cnt'];
$visitorDisconnected = $db->query("SELECT COUNT(*) as cnt FROM visitors WHERE is_online = 0")->fetch()['cnt'];
$visitorWaiting = $db->query("SELECT COUNT(*) as cnt FROM visitors WHERE status = 'waiting' AND is_online = 1")->fetch()['cnt'];

$visitors = $db->query("
    SELECT v.*, s.name, s.surname, s.email, s.address
    FROM visitors v
    LEFT JOIN students s ON v.student_id = s.id
    ORDER BY
        (CASE WHEN v.status = 'waiting' AND v.is_online = 1 THEN 0 ELSE 1 END),
        v.is_online DESC,
        v.last_activity DESC
")->fetchAll();

jsonResponse([
    'stats' => compact('visitorTotal', 'visitorOnline', 'visitorRegistered', 'visitorDisconnected', 'visitorWaiting'),
    'visitors' => $visitors
]);
