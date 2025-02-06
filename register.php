<?php
require_once '../config/database.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Log incoming data
            error_log(print_r($_POST, true));

            // Sanitize and validate input
            $fullName = filter_var($_POST['fullName'], FILTER_SANITIZE_STRING);
            $regNumber = filter_var($_POST['regNumber'], FILTER_SANITIZE_STRING);
            $sex = filter_var($_POST['sex'], FILTER_SANITIZE_STRING);
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $region = filter_var($_POST['region'], FILTER_SANITIZE_STRING);
            $district = filter_var($_POST['district'], FILTER_SANITIZE_STRING);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Prepare SQL statement
            $stmt = $pdo->prepare("INSERT INTO users (full_name, reg_number, sex, email, region, district, password) 
                                  VALUES (:fullName, :regNumber, :sex, :email, :region, :district, :password)");

            // Bind parameters
            $stmt->bindParam(':fullName', $fullName);
            $stmt->bindParam(':regNumber', $regNumber);
            $stmt->bindParam(':sex', $sex);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':region', $region);
            $stmt->bindParam(':district', $district);
            $stmt->bindParam(':password', $password);

            // Execute the statement
            $stmt->execute();

            echo json_encode(['status' => 'success', 'message' => 'Registration successful']);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
}
?>
