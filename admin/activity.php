<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$logs = $db->query("
    SELECT al.*, s.name, s.surname, s.email
    FROM activity_log al LEFT JOIN students s ON al.student_id = s.id
    ORDER BY al.created_at DESC LIMIT 200
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log - Exam Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .action-flag { background: #fef2f2; }
        .action-tag { display:inline-block; padding:2px 8px; border-radius:4px; font-size:.78rem; font-weight:600; }
        .tag-danger { background:#fee2e2; color:#dc2626; }
        .tag-info { background:#dbeafe; color:#2563eb; }
        .tag-default { background:#f1f5f9; color:#475569; }
    </style>
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="logo">Exam Portal</div>
        <nav>
            <a href="index.php">Dashboard</a>
            <a href="students.php">All Students</a>
            <a href="activity.php" class="active">Activity Log</a>
            <a href="settings.php">Settings</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>
    <main class="admin-main">
        <div class="admin-header"><h1>Activity Log</h1><span style="font-size:.85rem;color:var(--text-light)">Last 200 events</span></div>
        <div class="table-card">
            <table>
                <thead><tr><th>Time</th><th>Student</th><th>Action</th><th>Details</th><th>IP</th></tr></thead>
                <tbody>
                <?php foreach ($logs as $log):
                    $flagActions = ['tab_hidden','copy_attempt','paste_attempt','right_click'];
                    $isFlag = in_array($log['action'], $flagActions);
                    $tagClass = $isFlag ? 'tag-danger' : ($log['action']==='registered' ? 'tag-info' : 'tag-default');
                ?>
                <tr class="<?= $isFlag?'action-flag':'' ?>">
                    <td style="white-space:nowrap;font-size:.82rem"><?= sanitize($log['created_at']) ?></td>
                    <td><?= sanitize(($log['name']??'').' '.($log['surname']??'')) ?></td>
                    <td><span class="action-tag <?= $tagClass ?>"><?= sanitize($log['action']) ?></span></td>
                    <td style="font-size:.85rem"><?= sanitize($log['details']) ?></td>
                    <td><code style="font-size:.82rem"><?= sanitize($log['ip_address']) ?></code></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
