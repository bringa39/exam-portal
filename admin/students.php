<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$students = $db->query("SELECT * FROM students ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Students - Exam Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="logo">Exam Portal</div>
        <nav>
            <a href="index.php">Dashboard</a>
            <a href="students.php" class="active">All Students</a>
            <a href="activity.php">Activity Log</a>
            <a href="settings.php">Settings</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>
    <main class="admin-main">
        <div class="admin-header"><h1>All Students (<?= count($students) ?>)</h1></div>
        <div class="table-card">
            <table>
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Address</th><th>IP</th><th>Browser</th><th>OS</th><th>Device</th><th>Screen</th><th>Timezone</th><th>Status</th><th>Registered</th></tr></thead>
                <tbody>
                <?php if (empty($students)): ?>
                    <tr><td colspan="12" style="text-align:center;color:var(--text-light)">No students registered.</td></tr>
                <?php else: foreach ($students as $s): ?>
                    <tr>
                        <td><?= $s['id'] ?></td>
                        <td><strong><?= sanitize($s['name'].' '.$s['surname']) ?></strong></td>
                        <td><?= sanitize($s['email']) ?></td>
                        <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis"><?= sanitize($s['address']) ?></td>
                        <td><code><?= sanitize($s['ip_address']) ?></code></td>
                        <td><?= sanitize($s['browser']) ?></td>
                        <td><?= sanitize($s['os']) ?></td>
                        <td><?= sanitize($s['device']) ?></td>
                        <td><?= sanitize($s['screen_resolution']) ?></td>
                        <td><?= sanitize($s['timezone']) ?></td>
                        <td><span class="badge <?= $s['is_online']?'badge-online':'badge-offline' ?>"><?= $s['is_online']?'Online':'Offline' ?></span></td>
                        <td><?= sanitize($s['created_at']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
