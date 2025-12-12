<?php
/**
 * Import CSV Module
 * Imports customer data from CSV file into database
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
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
        exit;
    }
    
    $uploadedFile = $_FILES['csv_file'];
    
    // Validate file type
    $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
    if ($fileExtension !== 'csv') {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Please upload a CSV file']);
        exit;
    }
    
    // Validate file size (max 50MB)
    if ($uploadedFile['size'] > 50 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 50MB']);
        exit;
    }
    
    $pdo = Database::getConnection();
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Read CSV file
    $handle = fopen($uploadedFile['tmp_name'], 'r');
    if ($handle === false) {
        throw new Exception("Could not read CSV file");
    }
    
    // Read header row
    $header = fgetcsv($handle);
    if ($header === false) {
        fclose($handle);
        throw new Exception("Could not read CSV header");
    }
    
    // Map header to database fields
    $fieldMap = [
        'Customer ID' => 'customer_id',
        'Date' => 'date',
        'Time' => 'time',
        'Customer Name' => 'customer_name',
        'Company Name' => 'company_name',
        'Primary Contact' => 'primary_contact',
        'Secondary Contact' => 'secondary_contact',
        'Email ID' => 'email_id',
        'City' => 'city',
        'Product Category' => 'product_category',
        'Requirement' => 'requirement',
        'Source' => 'source',
        'Assigned to' => 'assigned_to',
        'Customer Type' => 'customer_type',
        'Status' => 'status'
    ];
    
    // Find comment columns
    $commentColumns = [];
    foreach ($header as $index => $col) {
        if (preg_match('/^Comment\s+\d+$/i', trim($col))) {
            $commentColumns[] = $index;
        }
    }
    
    $imported = 0;
    $updated = 0;
    $errors = [];
    
    // Read data rows
    while (($row = fgetcsv($handle)) !== false) {
        if (empty($row) || empty($row[0])) {
            continue; // Skip empty rows
        }
        
        try {
            // Map CSV row to database fields
            $customerData = [];
            foreach ($fieldMap as $csvField => $dbField) {
                $index = array_search($csvField, $header);
                $customerData[$dbField] = ($index !== false && isset($row[$index])) ? $row[$index] : '';
            }
            
            // Validate required fields
            if (empty($customerData['customer_id'])) {
                continue; // Skip rows without customer ID
            }
            
            $customerId = $customerData['customer_id'];
            
            // Check if customer exists
            $stmt = $pdo->prepare("SELECT id FROM customers WHERE customer_id = ?");
            $stmt->execute([$customerId]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing customer
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
                    $customerData['date'],
                    $customerData['time'],
                    $customerData['customer_name'],
                    $customerData['company_name'],
                    $customerData['primary_contact'],
                    $customerData['secondary_contact'],
                    $customerData['email_id'],
                    $customerData['city'],
                    $customerData['product_category'],
                    $customerData['requirement'],
                    $customerData['source'],
                    $customerData['assigned_to'],
                    $customerData['customer_type'],
                    $customerData['status'],
                    $customerId
                ]);
                
                $updated++;
            } else {
                // Insert new customer
                $stmt = $pdo->prepare("
                    INSERT INTO customers (
                        customer_id, date, time, customer_name, company_name,
                        primary_contact, secondary_contact, email_id, city,
                        product_category, requirement, source, assigned_to,
                        customer_type, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $customerId,
                    $customerData['date'],
                    $customerData['time'],
                    $customerData['customer_name'],
                    $customerData['company_name'],
                    $customerData['primary_contact'],
                    $customerData['secondary_contact'],
                    $customerData['email_id'],
                    $customerData['city'],
                    $customerData['product_category'],
                    $customerData['requirement'],
                    $customerData['source'],
                    $customerData['assigned_to'],
                    $customerData['customer_type'],
                    $customerData['status']
                ]);
                
                $imported++;
            }
            
            // Handle comments
            if (!empty($commentColumns)) {
                // Delete existing comments
                $stmt = $pdo->prepare("DELETE FROM customer_comments WHERE customer_id = ?");
                $stmt->execute([$customerId]);
                
                // Insert new comments
                $stmt = $pdo->prepare("
                    INSERT INTO customer_comments (customer_id, comment_text, comment_order)
                    VALUES (?, ?, ?)
                ");
                
                $commentOrder = 1;
                foreach ($commentColumns as $colIndex) {
                    if (isset($row[$colIndex]) && !empty(trim($row[$colIndex]))) {
                        $stmt->execute([$customerId, trim($row[$colIndex]), $commentOrder]);
                        $commentOrder++;
                    }
                }
            }
            
        } catch (PDOException $e) {
            $errors[] = "Row with Customer ID {$customerData['customer_id']}: " . $e->getMessage();
            error_log("CSV import error for row: " . $e->getMessage());
        }
    }
    
    fclose($handle);
    
    // Commit transaction
    $pdo->commit();
    
    $message = "Import completed successfully. Imported: {$imported}, Updated: {$updated}";
    if (!empty($errors)) {
        $message .= ". Errors: " . count($errors);
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'imported' => $imported,
        'updated' => $updated,
        'errors' => $errors
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Import CSV error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()]);
}


