<?php
session_start();
require_once __DIR__ . '/../../includes/functions.php';

if (empty($_SESSION['is_admin'])) { jsonResponse(['error' => 'Unauthorized'], 401); }

$db = getDB();
$db->exec("UPDATE students SET is_online = 0 WHERE last_activity < datetime('now', '-15 seconds') AND is_online = 1");

$total = $db->query("SELECT COUNT(*) as cnt FROM students")->fetch()['cnt'];
$online = $db->query("SELECT COUNT(*) as cnt FROM students WHERE is_online = 1")->fetch()['cnt'];
$events = $db->query("SELECT COUNT(*) as cnt FROM activity_log")->fetch()['cnt'];
$flagged = $db->query("SELECT COUNT(*) as cnt FROM activity_log WHERE action IN ('tab_hidden','copy_attempt','paste_attempt','right_click')")->fetch()['cnt'];

$students = $db->query("
    SELECT s.*, (SELECT COUNT(*) FROM activity_log WHERE student_id = s.id AND action IN ('tab_hidden','copy_attempt','paste_attempt','right_click')) as flags
    FROM students s ORDER BY s.is_online DESC, s.last_activity DESC
")->fetchAll();

jsonResponse(['stats' => compact('total', 'online', 'events', 'flagged'), 'students' => $students]);
