<?php
/**
 * Form Handler Module
 * Processes form submissions and saves to libSQL
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
    
    // Get form data
    $customerId = $_POST['customer_id'] ?? '';
    $date = $_POST['date'] ?? date('Y-m-d');
    $time = $_POST['time'] ?? date('H:i');
    $customerName = $_POST['customer_name'] ?? '';
    $companyName = $_POST['company_name'] ?? '';
    $primaryContact = $_POST['primary_contact'] ?? '';
    $secondaryContact = $_POST['secondary_contact'] ?? '';
    $emailId = $_POST['email_id'] ?? '';
    $city = $_POST['city'] ?? '';
    $productCategory = isset($_POST['product_category']) ? implode(', ', $_POST['product_category']) : '';
    $requirement = $_POST['requirement'] ?? '';
    $customerType = $_POST['customer_type'] ?? '';
    $source = $_POST['source'] ?? '';
    $status = $_POST['status'] ?? '';
    $comments = isset($_POST['comments']) ? $_POST['comments'] : [];
    $assignedTo = $_SESSION['username'] ?? '';
    
    // Validate email (must contain @ and .)
    if (!empty($emailId)) {
        $emails = explode("\n", $emailId);
        foreach ($emails as $email) {
            $email = trim($email);
            if (!empty($email)) {
                if (strpos($email, '@') === false || strpos($email, '.') === false) {
                    echo json_encode(['success' => false, 'message' => 'Invalid email format. Email must contain @ and . symbols.']);
                    exit;
                }
            }
        }
    }
    
    // Validate required fields
    if (empty($customerId) || empty($date) || empty($time) || empty($customerName) || empty($emailId) || empty($source) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
        exit;
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Check if customer ID already exists
    $stmt = $pdo->prepare("SELECT id FROM customers WHERE customer_id = ?");
    $stmt->execute([$customerId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing record
        $stmt = $pdo->prepare("
            UPDATE customers SET
                date = ?,
                time = ?,
                customer_name = ?,
                company_name = ?,
                primary_contact = ?,
                secondary_contact = ?,
                email_id = ?,
                city = ?,
                product_category = ?,
                requirement = ?,
                source = ?,
                assigned_to = ?,
                customer_type = ?,
                status = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE customer_id = ?
        ");
        
        $stmt->execute([
            $date, $time, $customerName, $companyName, $primaryContact,
            $secondaryContact, $emailId, $city, $productCategory, $requirement,
            $source, $assignedTo, $customerType, $status, $customerId
        ]);
        
        // Delete existing comments
        $stmt = $pdo->prepare("DELETE FROM customer_comments WHERE customer_id = ?");
        $stmt->execute([$customerId]);
    } else {
        // Insert new record
        $stmt = $pdo->prepare("
            INSERT INTO customers (
                customer_id, date, time, customer_name, company_name,
                primary_contact, secondary_contact, email_id, city,
                product_category, requirement, source, assigned_to,
                customer_type, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $customerId, $date, $time, $customerName, $companyName,
            $primaryContact, $secondaryContact, $emailId, $city,
            $productCategory, $requirement, $source, $assignedTo,
            $customerType, $status
        ]);
    }
    
    // Insert comments
    if (!empty($comments)) {
        $stmt = $pdo->prepare("
            INSERT INTO customer_comments (customer_id, comment_text, comment_order)
            VALUES (?, ?, ?)
        ");
        
        foreach ($comments as $index => $comment) {
            $comment = trim($comment);
            if (!empty($comment)) {
                $stmt->execute([$customerId, $comment, $index + 1]);
            }
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Data saved successfully']);
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Form handler error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Form handler error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
