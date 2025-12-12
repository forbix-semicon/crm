<?php
/**
 * Database Backup Module
 * Creates backup of CRM database file
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once 'auth.php';

requireAgent();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Create backups directory if it doesn't exist
    $backupDir = __DIR__ . '/../backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    // Generate backup filename: crm_yymmdd_hhmm.db
    $timestamp = date('ymd_His');
    $backupFile = $backupDir . '/crm_' . $timestamp . '.db';
    
    // Copy the database file
    if (!copy(DB_PATH, $backupFile)) {
        throw new Exception("Could not copy database file");
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database backup created successfully',
        'filename' => basename($backupFile)
    ]);
    
} catch (Exception $e) {
    error_log("Database backup error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Backup failed: ' . $e->getMessage()]);
}


