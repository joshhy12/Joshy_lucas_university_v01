<?php
session_start();
require_once '../config/database.php';

if(isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT full_name, email, reg_number FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'data' => $userData
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'User not logged in'
    ]);
}
?>
