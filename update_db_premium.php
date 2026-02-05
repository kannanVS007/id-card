<?php
require_once 'config.php';

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN is_premium TINYINT(1) DEFAULT 0");
    echo "Column 'is_premium' added to 'users' table successfully!";
    
    // Make default admin premium
    $pdo->exec("UPDATE users SET is_premium = 1 WHERE username = 'admin'");
    echo "<br>Admin user granted premium access.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column 'is_premium' already exists.";
    } else {
        die("Database Error: " . $e->getMessage());
    }
}
?>
