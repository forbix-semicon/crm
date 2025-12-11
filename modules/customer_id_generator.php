<?php
/**
 * Customer ID Generator Module
 * Generates next customer ID starting from CID20001
 */

require_once 'db.php';

function getNextCustomerId() {
    try {
        $pdo = Database::getConnection();
        
        // Get the highest customer ID
        $stmt = $pdo->query("SELECT customer_id FROM customers ORDER BY customer_id DESC LIMIT 1");
        $result = $stmt->fetch();
        
        if ($result) {
            // Extract number from CID20001 format
            $lastId = $result['customer_id'];
            if (preg_match('/CID(\d+)/', $lastId, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
                return 'CID' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            }
        }
        
        // Default starting ID
        return 'CID20001';
        
    } catch (PDOException $e) {
        error_log("Customer ID generator error: " . $e->getMessage());
        // Return default if database error
        return 'CID20001';
    }
}
