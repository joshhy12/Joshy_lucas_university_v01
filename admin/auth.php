<?php
session_start();

function requireAdminAuth() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit;
    }
}

function requireRole($required_role) {
    requireAdminAuth();
    
    if ($_SESSION['admin_role'] !== $required_role && $_SESSION['admin_role'] !== 'super_admin') {
        header("Location: dashboard.php?error=insufficient_permissions");
        exit;
    }
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function getAdminName() {
    return $_SESSION['admin_name'] ?? 'Admin';
}

function getAdminRole() {
    return $_SESSION['admin_role'] ?? 'user';
}
?>