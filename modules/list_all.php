<?php
/**
 * List All Customers Module
 * Returns all customers with their data for display in table
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once 'auth.php';
require_once 'db.php';

requireAgent();

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();
    
    // Fetch all customers
    $stmt = $pdo->query("
        SELECT 
            customer_id, date, time, customer_name, company_name,
            primary_contact, secondary_contact, email_id, city,
            product_category, requirement, source, assigned_to,
            customer_type, status, created_at, updated_at
        FROM customers
        ORDER BY customer_id DESC
    ");
    $customers = $stmt->fetchAll();
    
    // Fetch all comments grouped by customer
    $stmt = $pdo->query("
        SELECT customer_id, comment_text, comment_order
        FROM customer_comments
        ORDER BY customer_id, comment_order
    ");
    $comments = $stmt->fetchAll();
    
    // Group comments by customer_id
    $commentsByCustomer = [];
    foreach ($comments as $comment) {
        if (!isset($commentsByCustomer[$comment['customer_id']])) {
            $commentsByCustomer[$comment['customer_id']] = [];
        }
        $commentsByCustomer[$comment['customer_id']][] = $comment['comment_text'];
    }
    
    // Add comments to each customer
    foreach ($customers as &$customer) {
        $customer['comments'] = $commentsByCustomer[$customer['customer_id']] ?? [];
    }
    
    echo json_encode(['success' => true, 'customers' => $customers]);
    
} catch (PDOException $e) {
    error_log("List all error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}


