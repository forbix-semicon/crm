<?php
/**
 * Admin Agents Management Module
 * Handles creating and managing agent accounts
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
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Username and password are required']);
                exit;
            }
            
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Username already exists']);
                exit;
            }
            
            // Create new agent
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'agent')");
            $stmt->execute([$username, $password]);
            
            echo json_encode(['success' => true, 'message' => 'Agent created successfully']);
            
        } elseif ($action === 'update') {
            $id = $_POST['id'] ?? 0;
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($id) || empty($username)) {
                echo json_encode(['success' => false, 'message' => 'ID and username are required']);
                exit;
            }
            
            if (!empty($password)) {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND role = 'agent'");
                $stmt->execute([$username, $password, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND role = 'agent'");
                $stmt->execute([$username, $id]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Agent updated successfully']);
            
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID is required']);
                exit;
            }
            
            // Don't allow deleting admin
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            
            if ($user && $user['role'] === 'admin') {
                echo json_encode(['success' => false, 'message' => 'Cannot delete admin user']);
                exit;
            }
            
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'agent'");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true, 'message' => 'Agent deleted successfully']);
            
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        
    } catch (PDOException $e) {
        error_log("Admin agents error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // GET request - return list of agents
    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, username, role, created_at FROM users WHERE role = 'agent' ORDER BY username");
        $agents = $stmt->fetchAll();
        echo json_encode(['success' => true, 'agents' => $agents]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}


