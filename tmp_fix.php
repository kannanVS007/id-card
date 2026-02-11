<?php
require 'config.php';
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN selected_design VARCHAR(50) DEFAULT 'design1'");
    echo "COL_ADDED";
} catch (Exception $e) {
    echo $e->getMessage();
}
