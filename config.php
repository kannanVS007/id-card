<?php
// ===============================
// DATABASE CONFIGURATION
// ===============================
define('DB_HOST', 'localhost');
define('DB_NAME', 'id_card_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// ===============================
// PDO CONNECTION
// ===============================
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    // Prevent fatal error if DB is temporarily unavailable
    $pdo = null;
}

// ===============================
// SESSION START
// ===============================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===============================
// GLOBAL SETTINGS
// ===============================
define('PROJECT_NAME', 'ID Card Generator');
define('ADMIN_EMAIL', 'vskannan4153@gmail.com');

// Auto approval logic
define('TRUSTED_DOMAINS', ['school.edu', 'institutional.com', 'edu.in', 'ac.in']);
define('AUTO_APPROVAL_MINUTES', 5);

// ===============================
// ACTIVITY LOG FUNCTION (FIXED)
// ===============================
function logActivity($user_id, $action)
{
    global $pdo;
    if (!$pdo) return;

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO activity_logs (user_id, action, ip_address, created_at)
             VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute([
            $user_id,
            $action,
            $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
    } catch (Exception $e) {
        // Silent fail – never break app
    }
}