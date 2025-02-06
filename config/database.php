<?php
$host = 'localhost';
$dbname = 'university_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Remove the connection success message
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
}
?>
