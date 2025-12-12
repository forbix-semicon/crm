<?php
/**
 * Admin Statuses Management Module
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once 'auth.php';
require_once 'db.php';

requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        $pdo = Database::getConnection();
        
        if ($action === 'create') {
            $name = $_POST['name'] ?? '';
            
            if (empty($name)) {
                echo json_encode(['success' => false, 'message' => 'Status name is required']);
                exit;
            }
            
            $stmt = $pdo->prepare("INSERT INTO statuses (name) VALUES (?)");
            $stmt->execute([$name]);
            
            echo json_encode(['success' => true, 'message' => 'Status created successfully']);
            
        } elseif ($action === 'update') {
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            
            if (empty($id) || empty($name)) {
                echo json_encode(['success' => false, 'message' => 'ID and name are required']);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE statuses SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
            
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
            
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID is required']);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM statuses WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Status deleted successfully']);
            
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'UNIQUE constraint') !== false) {
            echo json_encode(['success' => false, 'message' => 'Status name already exists']);
        } else {
            error_log("Admin statuses error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
} else {
    // GET request - return list of statuses
    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, name, display_order FROM statuses ORDER BY display_order, name");
        $statuses = $stmt->fetchAll();
        echo json_encode(['success' => true, 'statuses' => $statuses]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}


