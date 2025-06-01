<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        header("Location: login.html?error=Email and password are required");
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, full_name, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            
            header("Location: dashboard/index.php");
            exit;
        } else {
            header("Location: login.html?error=Invalid email or password");
            exit;
        }
        
    } catch (PDOException $e) {
        error_log($e->getMessage());
        header("Location: login.html?error=Database error occurred");
        exit;
    }
} else {
    header("Location: login.html");
    exit;
}
?>