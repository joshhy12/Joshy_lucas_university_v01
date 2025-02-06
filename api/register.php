<?php
header('Content-Type: application/json');
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Debug incoming data
        error_log("Received POST data: " . print_r($_POST, true));
        
        $stmt = $pdo->prepare("INSERT INTO users (full_name, reg_number, sex, email, region, district, password) 
                              VALUES (:fullName, :regNumber, :sex, :email, :region, :district, :password)");
        
        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $params = [
            ':fullName' => $_POST['fullName'],
            ':regNumber' => $_POST['regNumber'],
            ':sex' => $_POST['sex'],
            ':email' => $_POST['email'],
            ':region' => $_POST['region'],
            ':district' => $_POST['district'],
            ':password' => $hashedPassword
        ];
        
        $stmt->execute($params);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Registration successful! Redirecting to login...'
        ]);
        
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?>
