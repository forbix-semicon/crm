<?php
/**
 * Authentication Module
 * Handles user login and session management for both admin and agents
 */

function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isAgent() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'agent' || $_SESSION['role'] === 'admin');
}

function login($username, $password) {
    require_once __DIR__ . '/db.php';
    $pdo = Database::getConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && $user['password'] === $password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_id'] = $user['id'];
        return true;
    }
    return false;
}

function logout() {
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit;
    }
}

function requireAgent() {
    requireLogin();
    if (!isAgent()) {
        header('Location: ../index.php');
        exit;
    }
}
