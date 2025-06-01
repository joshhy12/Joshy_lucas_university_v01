<?php
session_start(); // Start session at the beginning
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullName']);
    $regNumber = trim($_POST['regNumber']);
    $sex = $_POST['sex'];
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // Basic validation
    if (empty($fullName) || empty($regNumber) || empty($sex) || empty($email) || empty($password)) {
        header("Location: registration.html?error=All fields are required");
        exit;
    }
    
    // Check if passwords match
    if ($password !== $confirmPassword) {
        header("Location: registration.html?error=Passwords do not match");
        exit;
    }
    
    // Check password length
    if (strlen($password) < 8) {
        header("Location: registration.html?error=Password must be at least 8 characters long");
        exit;
    }
    
    // Validate registration number format
    if (!preg_match('/^S\d{4}\.\d{4}\.\d{4}$/', $regNumber)) {
        header("Location: registration.html?error=Invalid registration number format. Use S3725.0187.2020");
        exit;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: registration.html?error=Invalid email address");
        exit;
    }
    
    try {
        // Check if registration number or email already exists
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE reg_number = ? OR email = ?");
        $checkStmt->execute([$regNumber, $email]);
        
        if ($checkStmt->fetch()) {
            header("Location: registration.html?error=Registration number or email already exists");
            exit;
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (full_name, reg_number, sex, email, password) VALUES (?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$fullName, $regNumber, $sex, $email, $hashedPassword])) {
            // Get the newly created user ID
            $userId = $pdo->lastInsertId();
            
            // Automatically log in the user by setting session variables
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $fullName;
            $_SESSION['user_email'] = $email;
            $_SESSION['reg_number'] = $regNumber;
            $_SESSION['sex'] = $sex;
            
            // Redirect directly to admin panel/dashboard
            header("Location: dashboard/index.php?welcome=1");
            exit;
        } else {
            header("Location: registration.html?error=Registration failed. Please try again.");
            exit;
        }
        
    } catch (PDOException $e) {
        error_log($e->getMessage());
        header("Location: registration.html?error=Database error occurred. Please try again.");
        exit;
    }
} else {
    header("Location: registration.html");
    exit;
}
?>
