<?php
/**
 * Get Field Options Module
 * Returns available options for dropdown fields
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once 'auth.php';
require_once 'db.php';

requireAgent();

header('Content-Type: application/json');

try {
    $pdo = Database::getConnection();
    
    $options = [];
    
    // Get product categories
    $stmt = $pdo->query("SELECT name FROM product_categories ORDER BY display_order, name");
    $options['product_category'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get customer types
    $stmt = $pdo->query("SELECT name FROM customer_types ORDER BY display_order, name");
    $options['customer_type'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get statuses
    $stmt = $pdo->query("SELECT name FROM statuses ORDER BY display_order, name");
    $options['status'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Source options (static)
    $options['source'] = [
        'Phone Call',
        'Whatsup',
        'Website',
        'Email',
        'Social Media',
        'Not Sure'
    ];
    
    // Get assigned_to options (all agents)
    $stmt = $pdo->query("SELECT username FROM users WHERE role = 'agent' OR role = 'admin' ORDER BY username");
    $options['assigned_to'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode(['success' => true, 'options' => $options]);
    
} catch (PDOException $e) {
    error_log("Get field options error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

