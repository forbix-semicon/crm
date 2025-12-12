<?php
/**
 * Admin Product Categories Management Module
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
                echo json_encode(['success' => false, 'message' => 'Category name is required']);
                exit;
            }
            
            $stmt = $pdo->prepare("INSERT INTO product_categories (name) VALUES (?)");
            $stmt->execute([$name]);
            
            echo json_encode(['success' => true, 'message' => 'Category created successfully']);
            
        } elseif ($action === 'update') {
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            
            if (empty($id) || empty($name)) {
                echo json_encode(['success' => false, 'message' => 'ID and name are required']);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE product_categories SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
            
            echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
            
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID is required']);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM product_categories WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
            
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'UNIQUE constraint') !== false) {
            echo json_encode(['success' => false, 'message' => 'Category name already exists']);
        } else {
            error_log("Admin categories error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
} else {
    // GET request - return list of categories
    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, name, display_order FROM product_categories ORDER BY display_order, name");
        $categories = $stmt->fetchAll();
        echo json_encode(['success' => true, 'categories' => $categories]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}


