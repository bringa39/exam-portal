<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!empty($_SESSION['is_admin'])) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $db = getDB();
    $row = $db->query("SELECT admin_password FROM admin_settings LIMIT 1")->fetch();
    if ($row && password_verify($password, $row['admin_password'])) {
        $_SESSION['is_admin'] = true;
        header('Location: index.php');
        exit;
    }
    $error = 'Invalid password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Exam Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <h1>Admin Panel</h1>
        <p class="subtitle">Enter your password to access the monitoring dashboard.</p>
        <?php if ($error): ?>
            <div class="alert alert-error" style="display:block"><?= sanitize($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter admin password" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>
    </div>
</div>
</body>
</html>
