<div class="mobile-nav">
    <span class="mn-logo">Exam Portal</span>
    <button class="mn-toggle" onclick="document.getElementById('mobileMenu').classList.toggle('open')" aria-label="Menu">&#9776;</button>
</div>
<div class="mobile-nav-menu" id="mobileMenu">
    <a href="index.php" <?= basename($_SERVER['PHP_SELF'])==='index.php'?'class="active"':'' ?>>Dashboard</a>
    <a href="students.php" <?= basename($_SERVER['PHP_SELF'])==='students.php'?'class="active"':'' ?>>Students</a>
    <a href="activity.php" <?= basename($_SERVER['PHP_SELF'])==='activity.php'?'class="active"':'' ?>>Activity</a>
    <a href="settings.php" <?= basename($_SERVER['PHP_SELF'])==='settings.php'?'class="active"':'' ?>>Settings</a>
    <a href="logout.php">Logout</a>
</div>
