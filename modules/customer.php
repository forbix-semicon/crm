<?php
/**
 * Customer Operations Module
 */

function saveCustomer($pdo, $data) {
    try {
        // Validate email if provided
        if (!empty($data['email_id'])) {
            $emails = explode("\n", $data['email_id']);
            foreach ($emails as $email) {
                $email = trim($email);
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Invalid email address: " . $email);
                }
            }
        }
        
        // Check if customer exists (update) or new (insert)
        $customer_id = !empty($data['customer_id']) && $data['customer_id'] !== 'AUTO' 
            ? $data['customer_id'] 
            : null;
            
        if ($customer_id) {
            $stmt = $pdo->prepare("SELECT id FROM customers WHERE customer_id = ?");
            $stmt->execute([$customer_id]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE customers SET 
                    date = ?, time = ?, customer_name = ?, company_name = ?, 
                    primary_contact = ?, secondary_contact = ?, email_id = ?, 
                    city = ?, product_category = ?, requirement = ?, source = ?, 
                    assigned_to = ?, customer_type = ?, status = ?, comments = ?
                    WHERE customer_id = ?");
                    
                $stmt->execute([
                    $data['date'], $data['time'], $data['customer_name'],
                    $data['company_name'], $data['primary_contact'], $data['secondary_contact'],
                    $data['email_id'], $data['city'], $data['product_category'],
                    $data['requirement'], $data['source'], $data['assigned_to'],
                    $data['customer_type'], $data['status'], $data['comments'],
                    $customer_id
                ]);
                return ['success' => true, 'message' => 'Customer updated successfully', 'customer_id' => $customer_id];
            } else {
                // Customer ID provided but doesn't exist - use it for new entry
                $data['customer_id'] = $customer_id;
            }
        } else {
            // Insert new customer with auto-generated ID
            $data['customer_id'] = getNextCustomerID($pdo);
        }
        
        $stmt = $pdo->prepare("INSERT INTO customers (
            customer_id, date, time, customer_name, company_name, 
            primary_contact, secondary_contact, email_id, city, 
            product_category, requirement, source, assigned_to, 
            customer_type, status, comments
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $data['customer_id'], $data['date'], $data['time'],
            $data['customer_name'], $data['company_name'],
            $data['primary_contact'], $data['secondary_contact'],
            $data['email_id'], $data['city'], $data['product_category'],
            $data['requirement'], $data['source'], $data['assigned_to'],
            $data['customer_type'], $data['status'], $data['comments']
        ]);
        
        return ['success' => true, 'message' => 'Customer saved successfully', 'customer_id' => $data['customer_id']];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function getAllCustomers($pdo) {
    $stmt = $pdo->query("SELECT * FROM customers ORDER BY customer_id DESC");
    return $stmt->fetchAll();
}

function getCustomerById($pdo, $customer_id) {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE customer_id = ?");
    $stmt->execute([$customer_id]);
    return $stmt->fetch();
}

function updateCustomerField($pdo, $customer_id, $field, $value) {
    try {
        // Validate email field
        if ($field === 'email_id' && !empty($value)) {
            $emails = explode("\n", $value);
            foreach ($emails as $email) {
                $email = trim($email);
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Invalid email address: " . $email);
                }
            }
        }
        
        $allowedFields = ['date', 'time', 'customer_name', 'company_name', 'primary_contact',
            'secondary_contact', 'email_id', 'city', 'product_category', 'requirement',
            'source', 'assigned_to', 'customer_type', 'status', 'comments'];
            
        if (!in_array($field, $allowedFields)) {
            throw new Exception("Invalid field");
        }
        
        $stmt = $pdo->prepare("UPDATE customers SET $field = ? WHERE customer_id = ?");
        $stmt->execute([$value, $customer_id]);
        
        return ['success' => true, 'message' => 'Field updated successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
?>

