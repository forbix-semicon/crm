<?php
/**
 * Admin Operations Module
 */

function createAgent($pdo, $username, $password) {
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'agent')");
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
        return ['success' => true, 'message' => 'Agent created successfully'];
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Unique constraint violation
            return ['success' => false, 'message' => 'Username already exists'];
        }
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function getAllUsers($pdo) {
    $stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY id DESC");
    return $stmt->fetchAll();
}

function addProductCategory($pdo, $name) {
    try {
        $stmt = $pdo->prepare("INSERT INTO product_categories (name) VALUES (?)");
        $stmt->execute([$name]);
        return ['success' => true, 'message' => 'Product category added successfully'];
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            return ['success' => false, 'message' => 'Category already exists'];
        }
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function getAllProductCategories($pdo) {
    $stmt = $pdo->query("SELECT * FROM product_categories ORDER BY name");
    return $stmt->fetchAll();
}

function deleteProductCategory($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM product_categories WHERE id = ?");
        $stmt->execute([$id]);
        return ['success' => true, 'message' => 'Product category deleted successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function addCustomerType($pdo, $name) {
    try {
        $stmt = $pdo->prepare("INSERT INTO customer_types (name) VALUES (?)");
        $stmt->execute([$name]);
        return ['success' => true, 'message' => 'Customer type added successfully'];
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            return ['success' => false, 'message' => 'Customer type already exists'];
        }
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function getAllCustomerTypes($pdo) {
    $stmt = $pdo->query("SELECT * FROM customer_types ORDER BY name");
    return $stmt->fetchAll();
}

function deleteCustomerType($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM customer_types WHERE id = ?");
        $stmt->execute([$id]);
        return ['success' => true, 'message' => 'Customer type deleted successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function addStatus($pdo, $name) {
    try {
        $stmt = $pdo->prepare("INSERT INTO statuses (name) VALUES (?)");
        $stmt->execute([$name]);
        return ['success' => true, 'message' => 'Status added successfully'];
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            return ['success' => false, 'message' => 'Status already exists'];
        }
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function getAllStatuses($pdo) {
    $stmt = $pdo->query("SELECT * FROM statuses ORDER BY name");
    return $stmt->fetchAll();
}

function deleteStatus($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM statuses WHERE id = ?");
        $stmt->execute([$id]);
        return ['success' => true, 'message' => 'Status deleted successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function getAllSources($pdo) {
    $stmt = $pdo->query("SELECT * FROM sources ORDER BY name");
    return $stmt->fetchAll();
}
?>

