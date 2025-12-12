<?php
/**
 * Comments Handler Module
 * Handles saving/updating comments for customers
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once 'auth.php';
require_once 'db.php';

requireAgent();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $pdo = Database::getConnection();
    
    $customerId = $_POST['customer_id'] ?? '';
    $comments = isset($_POST['comments']) ? $_POST['comments'] : [];
    
    if (empty($customerId)) {
        echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
        exit;
    }
    
    // Check if customer exists
    $stmt = $pdo->prepare("SELECT id FROM customers WHERE customer_id = ?");
    $stmt->execute([$customerId]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Customer not found']);
        exit;
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Delete existing comments
    $stmt = $pdo->prepare("DELETE FROM customer_comments WHERE customer_id = ?");
    $stmt->execute([$customerId]);
    
    // Insert new comments
    if (!empty($comments)) {
        $stmt = $pdo->prepare("
            INSERT INTO customer_comments (customer_id, comment_text, comment_order, updated_at)
            VALUES (?, ?, ?, CURRENT_TIMESTAMP)
        ");
        
        foreach ($comments as $index => $comment) {
            $comment = trim($comment);
            if (!empty($comment)) {
                $stmt->execute([$customerId, $comment, $index + 1]);
            }
        }
    }
    
    // Update customer updated_at timestamp
    $stmt = $pdo->prepare("UPDATE customers SET updated_at = CURRENT_TIMESTAMP WHERE customer_id = ?");
    $stmt->execute([$customerId]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Comments saved successfully']);
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Comments handler error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}


