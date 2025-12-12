<?php
/**
 * Import Database Module
 * Imports database file from uploaded file
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
    if (!isset($_FILES['db_file']) || $_FILES['db_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
        exit;
    }
    
    $uploadedFile = $_FILES['db_file'];
    
    // Validate file type
    $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
    if ($fileExtension !== 'db') {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Please upload a .db file']);
        exit;
    }
    
    // Validate file size (max 50MB)
    if ($uploadedFile['size'] > 50 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 50MB']);
        exit;
    }
    
    // Create backup of current database before import
    $backupDir = __DIR__ . '/../backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    $backupFile = $backupDir . '/crm_backup_before_import_' . date('ymd_His') . '.db';
    if (file_exists(DB_PATH)) {
        copy(DB_PATH, $backupFile);
    }
    
    // Move uploaded file to database location
    if (!move_uploaded_file($uploadedFile['tmp_name'], DB_PATH)) {
        throw new Exception("Could not move uploaded file");
    }
    
    // Set proper permissions
    chmod(DB_PATH, 0644);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database imported successfully. Previous database backed up.',
        'backup_file' => basename($backupFile)
    ]);
    
} catch (Exception $e) {
    error_log("Import database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()]);
}


