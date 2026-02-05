<?php
/**
 * DB Setup Script for ID Card System
 */
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS id_card_db");
    $pdo->exec("USE id_card_db");

    // Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        status ENUM('pending', 'active', 'inactive') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Activity Logs Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(255),
        ip_address VARCHAR(45),
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // ID Generations Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS id_generations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        mode ENUM('manual', 'bulk'),
        design_id INT,
        logo_path VARCHAR(255),
        total_cards INT,
        academic_year VARCHAR(20),
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Create or Update default admin
    $adminPass = password_hash('idcard123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $pdo->exec("INSERT INTO users (username, password, email, role, status) VALUES ('admin', '$adminPass', 'vskannan4135@gmail.com', 'admin', 'active')");
        echo "Default admin created: admin / idcard123<br>";
    } else {
        $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'")->execute([$adminPass]);
        echo "Admin password updated to: idcard123<br>";
    }

    echo "Database and tables set up successfully!";
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
