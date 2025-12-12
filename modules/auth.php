<?php
/**
 * Authentication Module
 */

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    return true;
}

function requireLogin() {
    if (!checkLogin()) {
        header('Location: index.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: index.php?error=unauthorized');
        exit;
    }
}

function login($username, $password, $pdo) {
    $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    
    return false;
}

function logout() {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>

