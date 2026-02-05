<?php
require_once 'config.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS custom_layouts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        layout_name VARCHAR(100) DEFAULT 'Default Layout',
        layout_json JSON NOT NULL,
        background_front VARCHAR(255),
        background_back VARCHAR(255),
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    echo "Table 'custom_layouts' created successfully!";
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
