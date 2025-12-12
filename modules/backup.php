<?php
/**
 * Backup Module
 */

function createBackup($db_path) {
    $backup_dir = dirname($db_path) . '/backups';
    if (!file_exists($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    $timestamp = date('ymd_His');
    $backup_file = $backup_dir . '/crm_' . $timestamp . '.db';
    
    if (copy($db_path, $backup_file)) {
        return ['success' => true, 'message' => 'Backup created successfully', 'file' => basename($backup_file)];
    } else {
        return ['success' => false, 'message' => 'Failed to create backup'];
    }
}
?>

