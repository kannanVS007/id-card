<?php
$host = 'localhost';
$dbname = 'id_card_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    file_put_contents('db_debug.log', "SUCCESS: Connected to database.\n", FILE_APPEND);
} catch (PDOException $e) {
    file_put_contents('db_debug.log', "FAILURE: " . $e->getMessage() . " (Code: " . $e->getCode() . ")\n", FILE_APPEND);
}
