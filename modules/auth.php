<?php
/**
 * Authentication Module
 * Handles user login and session management
 */

function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function login($username, $password) {
    // Hardcoded credentials for agent1
    if ($username === 'agent1' && $password === 'agent1') {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
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
