<?php
/**
 * Update Customer Module
 * Handles inline editing of customer data
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
    $field = $_POST['field'] ?? '';
    $value = $_POST['value'] ?? '';
    
    if (empty($customerId) || empty($field)) {
        echo json_encode(['success' => false, 'message' => 'Customer ID and field are required']);
        exit;
    }
    
    // Handle comments separately (stored in customer_comments table)
    if ($field === 'comments') {
        $pdo->beginTransaction();
        
        // Delete existing comments
        $stmt = $pdo->prepare("DELETE FROM customer_comments WHERE customer_id = ?");
        $stmt->execute([$customerId]);
        
        // Insert new comments (split by newline)
        if (!empty($value)) {
            $comments = explode("\n", $value);
            $stmt = $pdo->prepare("INSERT INTO customer_comments (customer_id, comment_text, comment_order, updated_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
            foreach ($comments as $index => $comment) {
                $comment = trim($comment);
                if (!empty($comment)) {
                    $stmt->execute([$customerId, $comment, $index + 1]);
                }
            }
        }
        
        // Update customer updated_at
        $stmt = $pdo->prepare("UPDATE customers SET updated_at = CURRENT_TIMESTAMP WHERE customer_id = ?");
        $stmt->execute([$customerId]);
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Comments updated successfully']);
        exit;
    }
    
    // Validate field name to prevent SQL injection
    $allowedFields = [
        'date', 'time', 'customer_name', 'company_name', 'primary_contact',
        'secondary_contact', 'email_id', 'city', 'product_category', 'requirement',
        'source', 'assigned_to', 'customer_type', 'status'
    ];
    
    if (!in_array($field, $allowedFields)) {
        echo json_encode(['success' => false, 'message' => 'Invalid field name']);
        exit;
    }
    
    // Validate email if field is email_id
    if ($field === 'email_id' && !empty($value)) {
        $emails = explode("\n", $value);
        foreach ($emails as $email) {
            $email = trim($email);
            if (!empty($email) && (strpos($email, '@') === false || strpos($email, '.') === false)) {
                echo json_encode(['success' => false, 'message' => 'Invalid email format. Email must contain @ and . symbols.']);
                exit;
            }
        }
    }
    
    // Update the field in customers table
    $stmt = $pdo->prepare("UPDATE customers SET {$field} = ?, updated_at = CURRENT_TIMESTAMP WHERE customer_id = ?");
    $stmt->execute([$value, $customerId]);
    
    echo json_encode(['success' => true, 'message' => 'Customer updated successfully']);
    
} catch (PDOException $e) {
    error_log("Update customer error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

