<?php
/**
 * Login Handler Module
 * Processes login requests
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once 'auth.php';
require_once 'db.php';

// Initialize database schema
require_once 'db_setup.php';
createDatabaseSchema();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password)) {
        // Redirect based on role
        if (isAdmin()) {
            header('Location: ../admin.php');
        } else {
            header('Location: ../index.php');
        }
        exit;
    } else {
        header('Location: ../index.php?error=Invalid credentials');
        exit;
    }
} else {
    header('Location: ../index.php');
    exit;
}
