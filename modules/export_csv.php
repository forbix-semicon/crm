<?php
/**
 * Export CSV Module
 * Creates CSV export of CRM data
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once 'auth.php';
require_once 'db.php';

requireAgent();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method');
}

try {
    $pdo = Database::getConnection();
    
    // Generate export filename: crm_yymmdd_hhmm.csv
    $timestamp = date('ymd_His');
    $filename = 'crm_' . $timestamp . '.csv';
    
    // Set headers for file download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Fetch all customers
    $stmt = $pdo->query("
        SELECT 
            customer_id, date, time, customer_name, company_name,
            primary_contact, secondary_contact, email_id, city,
            product_category, requirement, source, assigned_to,
            customer_type, status
        FROM customers
        ORDER BY customer_id
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
        $commentsByCustomer[$comment['customer_id']][] = $comment['comment_text'];
    }
    
    // Find max number of comment columns needed
    $maxComments = 0;
    foreach ($commentsByCustomer as $customerComments) {
        $maxComments = max($maxComments, count($customerComments));
    }
    
    // Write header
    $header = [
        'Customer ID', 'Date', 'Time', 'Customer Name', 'Company Name',
        'Primary Contact', 'Secondary Contact', 'Email ID', 'City',
        'Product Category', 'Requirement', 'Source', 'Assigned to',
        'Customer Type', 'Status'
    ];
    
    // Add comment columns
    for ($i = 1; $i <= $maxComments; $i++) {
        $header[] = 'Comment ' . $i;
    }
    
    fputcsv($output, $header);
    
    // Write data rows
    foreach ($customers as $customer) {
        $row = [
            $customer['customer_id'],
            $customer['date'],
            $customer['time'],
            $customer['customer_name'],
            $customer['company_name'],
            $customer['primary_contact'],
            $customer['secondary_contact'],
            $customer['email_id'],
            $customer['city'],
            $customer['product_category'],
            $customer['requirement'],
            $customer['source'],
            $customer['assigned_to'],
            $customer['customer_type'],
            $customer['status']
        ];
        
        // Add comments
        $customerComments = $commentsByCustomer[$customer['customer_id']] ?? [];
        for ($i = 0; $i < $maxComments; $i++) {
            $row[] = $customerComments[$i] ?? '';
        }
        
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
    
} catch (Exception $e) {
    error_log("Export CSV error: " . $e->getMessage());
    die('Export failed: ' . $e->getMessage());
}


