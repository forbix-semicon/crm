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
    $stmt = $pdo->query("SELECT id, username, role FROM users ORDER BY id DESC");
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
        $id = intval($id);
        if ($id <= 0) {
            return ['success' => false, 'message' => 'Invalid product category ID'];
        }
        
        // Verify the category exists
        $checkStmt = $pdo->prepare("SELECT id, name FROM product_categories WHERE id = ?");
        $checkStmt->execute([$id]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existing) {
            return ['success' => false, 'message' => 'Product category not found'];
        }
        
        // Delete by ID
        $stmt = $pdo->prepare("DELETE FROM product_categories WHERE id = ?");
        $stmt->execute([$id]);
        
        // Verify deletion by checking if record still exists
        $verifyStmt = $pdo->prepare("SELECT id FROM product_categories WHERE id = ?");
        $verifyStmt->execute([$id]);
        if ($verifyStmt->fetch()) {
            return ['success' => false, 'message' => 'Failed to delete product category. Record still exists.'];
        }
        
        return ['success' => true, 'message' => 'Product category "' . $existing['name'] . '" deleted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
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
        $id = intval($id);
        if ($id <= 0) {
            return ['success' => false, 'message' => 'Invalid customer type ID'];
        }
        
        // Verify the customer type exists
        $checkStmt = $pdo->prepare("SELECT id, name FROM customer_types WHERE id = ?");
        $checkStmt->execute([$id]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existing) {
            return ['success' => false, 'message' => 'Customer type not found'];
        }
        
        // Delete by ID
        $stmt = $pdo->prepare("DELETE FROM customer_types WHERE id = ?");
        $stmt->execute([$id]);
        
        // Verify deletion by checking if record still exists
        $verifyStmt = $pdo->prepare("SELECT id FROM customer_types WHERE id = ?");
        $verifyStmt->execute([$id]);
        if ($verifyStmt->fetch()) {
            return ['success' => false, 'message' => 'Failed to delete customer type. Record still exists.'];
        }
        
        return ['success' => true, 'message' => 'Customer type "' . $existing['name'] . '" deleted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function addStatus($pdo, $name, $color = '#e0e0e0') {
    try {
        $color = $color ?: '#e0e0e0';
        $stmt = $pdo->prepare("INSERT INTO statuses (name, color) VALUES (?, ?)");
        $stmt->execute([$name, $color]);
        return ['success' => true, 'message' => 'Status added successfully'];
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            return ['success' => false, 'message' => 'Status already exists'];
        }
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function updateStatusColor($pdo, $id, $color) {
    try {
        $id = intval($id);
        if ($id <= 0) {
            return ['success' => false, 'message' => 'Invalid status ID'];
        }
        if (!$color) {
            return ['success' => false, 'message' => 'Color is required'];
        }
        $stmt = $pdo->prepare("UPDATE statuses SET color = ? WHERE id = ?");
        $stmt->execute([$color, $id]);
        return ['success' => true, 'message' => 'Status color updated'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function getAllStatuses($pdo) {
    $stmt = $pdo->query("SELECT * FROM statuses ORDER BY name");
    return $stmt->fetchAll();
}

function deleteStatus($pdo, $id) {
    try {
        $id = intval($id);
        if ($id <= 0) {
            return ['success' => false, 'message' => 'Invalid status ID'];
        }
        
        // Verify the status exists
        $checkStmt = $pdo->prepare("SELECT id, name FROM statuses WHERE id = ?");
        $checkStmt->execute([$id]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existing) {
            return ['success' => false, 'message' => 'Status not found'];
        }
        
        // Delete by ID
        $stmt = $pdo->prepare("DELETE FROM statuses WHERE id = ?");
        $stmt->execute([$id]);
        
        // Verify deletion by checking if record still exists
        $verifyStmt = $pdo->prepare("SELECT id FROM statuses WHERE id = ?");
        $verifyStmt->execute([$id]);
        if ($verifyStmt->fetch()) {
            return ['success' => false, 'message' => 'Failed to delete status. Record still exists.'];
        }
        
        return ['success' => true, 'message' => 'Status "' . $existing['name'] . '" deleted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function deleteUser($pdo, $id) {
    try {
        // Check if user exists and get username
        $checkStmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id = ?");
        $checkStmt->execute([$id]);
        $user = $checkStmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        // Prevent deletion of admin user
        if (strtolower($user['username']) === 'admin') {
            return ['success' => false, 'message' => 'Cannot delete admin user'];
        }
        
        // Prevent deletion if it's the only admin
        if ($user['role'] === 'admin') {
            $adminCountStmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
            $adminCount = $adminCountStmt->fetch()['count'];
            if ($adminCount <= 1) {
                return ['success' => false, 'message' => 'Cannot delete the last admin user'];
            }
        }
        
        // Delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        // Verify deletion by checking if record still exists
        $verifyStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $verifyStmt->execute([$id]);
        if ($verifyStmt->fetch()) {
            return ['success' => false, 'message' => 'Failed to delete user'];
        }
        
        return ['success' => true, 'message' => 'User deleted successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function getAllSources($pdo) {
    $stmt = $pdo->query("SELECT * FROM sources ORDER BY name");
    return $stmt->fetchAll();
}

function addSource($pdo, $name) {
    try {
        $stmt = $pdo->prepare("INSERT INTO sources (name) VALUES (?)");
        $stmt->execute([$name]);
        return ['success' => true, 'message' => 'Source added successfully'];
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            return ['success' => false, 'message' => 'Source already exists'];
        }
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function deleteSource($pdo, $id) {
    try {
        $id = intval($id);
        if ($id <= 0) {
            return ['success' => false, 'message' => 'Invalid source ID'];
        }
        
        // Verify the source exists
        $checkStmt = $pdo->prepare("SELECT id, name FROM sources WHERE id = ?");
        $checkStmt->execute([$id]);
        $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$existing) {
            return ['success' => false, 'message' => 'Source not found'];
        }
        
        // Delete by ID
        $stmt = $pdo->prepare("DELETE FROM sources WHERE id = ?");
        $stmt->execute([$id]);
        
        // Verify deletion by checking if record still exists
        $verifyStmt = $pdo->prepare("SELECT id FROM sources WHERE id = ?");
        $verifyStmt->execute([$id]);
        if ($verifyStmt->fetch()) {
            return ['success' => false, 'message' => 'Failed to delete source. Record still exists.'];
        }
        
        return ['success' => true, 'message' => 'Source "' . $existing['name'] . '" deleted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function clearDatabase($pdo) {
    try {
        // Only clear customer data, keep configuration tables
        $stmt = $pdo->prepare("DELETE FROM customers");
        $stmt->execute();
        
        $count = $stmt->rowCount();
        
        // Reset auto-increment sequence for SQLite
        $pdo->exec("DELETE FROM sqlite_sequence WHERE name='customers'");
        
        return [
            'success' => true, 
            'message' => "Successfully cleared $count customer record(s) from the database"
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to clear database: ' . $e->getMessage()];
    }
}

function getDatabaseStats($pdo, $db_path = null) {
    try {
        $stats = [];
        
        // Get database file name
        if ($db_path && file_exists($db_path)) {
            $stats['db_filename'] = basename($db_path);
            $stats['db_path'] = $db_path;
        } else {
            // Try to get from PRAGMA
            try {
                $dbPath = $pdo->query("PRAGMA database_list")->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($dbPath) && isset($dbPath[0]['file']) && !empty($dbPath[0]['file'])) {
                    $fullPath = $dbPath[0]['file'];
                    $stats['db_filename'] = basename($fullPath);
                    $stats['db_path'] = $fullPath;
                } else {
                    $stats['db_filename'] = 'crm.db';
                    $stats['db_path'] = 'data/crm.db';
                }
            } catch (Exception $e) {
                $stats['db_filename'] = 'crm.db';
                $stats['db_path'] = 'data/crm.db';
            }
        }
        
        // Count customers - use COUNT(*) with explicit casting
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM customers");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['customers'] = intval($result['count']);
        
        // Count users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['users'] = intval($result['count']);
        
        // Count product categories
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM product_categories");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['product_categories'] = intval($result['count']);
        
        // Count customer types
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM customer_types");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['customer_types'] = intval($result['count']);
        
        // Count sources
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM sources");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['sources'] = intval($result['count']);
        
        // Count statuses
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM statuses");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['statuses'] = intval($result['count']);
        
        // Get field names for each table (exclude id, created_at, updated_at)
        $tables = ['customers', 'users', 'product_categories', 'customer_types', 'sources', 'statuses'];
        $stats['table_fields'] = [];
        $excludedFields = ['id', 'created_at', 'updated_at'];
        
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("PRAGMA table_info($table)");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $fieldNames = array_column($columns, 'name');
                // Filter out excluded fields
                $stats['table_fields'][$table] = array_filter($fieldNames, function($field) use ($excludedFields) {
                    return !in_array(strtolower($field), $excludedFields);
                });
                // Re-index array to ensure sequential keys
                $stats['table_fields'][$table] = array_values($stats['table_fields'][$table]);
            } catch (Exception $e) {
                $stats['table_fields'][$table] = [];
            }
        }
        
        // Get backup files
        require_once __DIR__ . '/backup.php';
        $backup_dir = __DIR__ . '/../backup';
        $backupResult = listBackupFiles($backup_dir);
        if ($backupResult['success']) {
            $stats['backup_files'] = $backupResult['data'];
        } else {
            $stats['backup_files'] = [];
        }
        
        // Also check backups directory (alternative location)
        $backups_dir = dirname($stats['db_path']) . '/backups';
        if (file_exists($backups_dir)) {
            $backupResult2 = listBackupFiles($backups_dir);
            if ($backupResult2['success'] && !empty($backupResult2['data'])) {
                // Merge backup files from both locations
                $stats['backup_files'] = array_merge($stats['backup_files'], $backupResult2['data']);
            }
        }
        
        return ['success' => true, 'data' => $stats];
    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
?>

