<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$message = ''; $messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $db = getDB();

    if ($action === 'change_password') {
        $newPass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (strlen($newPass) < 6) { $message = 'Password must be at least 6 characters.'; $messageType = 'error'; }
        elseif ($newPass !== $confirm) { $message = 'Passwords do not match.'; $messageType = 'error'; }
        else {
            $db->prepare("UPDATE admin_settings SET admin_password = ? WHERE id = 1")->execute([password_hash($newPass, PASSWORD_DEFAULT)]);
            $message = 'Password updated successfully.'; $messageType = 'success';
        }
    }
    if ($action === 'clear_students') {
        $db->exec("DELETE FROM activity_log"); $db->exec("DELETE FROM redirects"); $db->exec("DELETE FROM students");
        $message = 'All student data has been cleared.'; $messageType = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Exam Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="logo">Exam Portal</div>
        <nav>
            <a href="index.php">Dashboard</a>
            <a href="students.php">All Students</a>
            <a href="activity.php">Activity Log</a>
            <a href="settings.php" class="active">Settings</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>
    <main class="admin-main">
        <div class="admin-header"><h1>Settings</h1></div>
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>" style="display:block;margin-bottom:20px"><?= sanitize($message) ?></div>
        <?php endif; ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <div class="card">
                <h2 style="margin-bottom:20px">Change Admin Password</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group"><label>New Password</label><input type="password" name="new_password" required minlength="6"></div>
                    <div class="form-group"><label>Confirm Password</label><input type="password" name="confirm_password" required></div>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
            <div class="card">
                <h2 style="margin-bottom:20px">Danger Zone</h2>
                <p style="color:var(--text-light);font-size:.9rem;margin-bottom:20px">Clear all student registrations, activity logs, and redirects. This cannot be undone.</p>
                <form method="POST" onsubmit="return confirm('Are you sure? This will delete ALL student data permanently.')">
                    <input type="hidden" name="action" value="clear_students">
                    <button type="submit" class="btn btn-danger">Clear All Data</button>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>
