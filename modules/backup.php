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

function listBackupFiles($backup_dir) {
    if (!file_exists($backup_dir)) {
        mkdir($backup_dir, 0755, true);
        return ['success' => true, 'data' => []];
    }
    
    $files = [];
    $items = scandir($backup_dir);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $filepath = $backup_dir . '/' . $item;
        if (is_file($filepath)) {
            $extension = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            if ($extension === 'db' || $extension === 'xlsx' || $extension === 'csv') {
                $files[] = [
                    'filename' => $item,
                    'type' => $extension === 'db' ? 'database' : ($extension === 'csv' ? 'csv' : 'excel'),
                    'size' => filesize($filepath),
                    'modified' => date('Y-m-d H:i:s', filemtime($filepath))
                ];
            }
        }
    }
    
    // Sort by modified date (newest first)
    usort($files, function($a, $b) {
        return strtotime($b['modified']) - strtotime($a['modified']);
    });
    
    return ['success' => true, 'data' => $files];
}
?>

