<?php
session_start();
header('Content-Type: application/json');

if(isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT full_name, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    echo json_encode([
        'status' => 'success',
        'user' => $user
    ]);
} else {
    header('Location: login.html');
}
?>
