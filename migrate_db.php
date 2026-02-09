<?php
require_once 'config.php';

echo "<h2>Starting Database Migration</h2>";

$columns = [
    'contact_number' => "VARCHAR(20) NULL",
    'door_street'    => "VARCHAR(255) NULL",
    'area'           => "VARCHAR(100) NULL",
    'city_town'      => "VARCHAR(100) NULL",
    'state'          => "VARCHAR(100) NULL",
    'pincode'        => "VARCHAR(20) NULL"
];

try {
    foreach ($columns as $column => $definition) {
        $check = $pdo->query("SHOW COLUMNS FROM users LIKE '$column'");
        if ($check->rowCount() == 0) {
            $pdo->exec("ALTER TABLE users ADD $column $definition");
            echo "Column '$column' added successfully.<br>";
        } else {
            echo "Column '$column' already exists. Skipping.<br>";
        }
    }
    echo "<h3>Migration completed successfully!</h3>";
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
?>
