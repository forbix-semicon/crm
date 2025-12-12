<?php
/**
 * API Handler for AJAX Requests
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/modules/auth.php';
require_once __DIR__ . '/modules/customer.php';
require_once __DIR__ . '/modules/admin.php';
require_once __DIR__ . '/modules/backup.php';

if (!checkLogin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'save_customer':
            $result = saveCustomer($pdo, $_POST);
            echo json_encode($result);
            break;
            
        case 'get_all_customers':
            $customers = getAllCustomers($pdo);
            echo json_encode(['success' => true, 'data' => $customers]);
            break;
            
        case 'get_customer':
            $customer = getCustomerById($pdo, $_GET['customer_id']);
            echo json_encode(['success' => true, 'data' => $customer]);
            break;
            
        case 'update_customer_field':
            $result = updateCustomerField($pdo, $_POST['customer_id'], $_POST['field'], $_POST['value']);
            echo json_encode($result);
            break;
            
        case 'create_agent':
            requireAdmin();
            $result = createAgent($pdo, $_POST['username'], $_POST['password']);
            echo json_encode($result);
            break;
            
        case 'get_all_users':
            requireAdmin();
            $users = getAllUsers($pdo);
            echo json_encode(['success' => true, 'data' => $users]);
            break;
            
        case 'add_product_category':
            requireAdmin();
            $result = addProductCategory($pdo, $_POST['name']);
            echo json_encode($result);
            break;
            
        case 'get_product_categories':
            $categories = getAllProductCategories($pdo);
            echo json_encode(['success' => true, 'data' => $categories]);
            break;
            
        case 'delete_product_category':
            requireAdmin();
            $result = deleteProductCategory($pdo, $_POST['id']);
            echo json_encode($result);
            break;
            
        case 'add_customer_type':
            requireAdmin();
            $result = addCustomerType($pdo, $_POST['name']);
            echo json_encode($result);
            break;
            
        case 'get_customer_types':
            $types = getAllCustomerTypes($pdo);
            echo json_encode(['success' => true, 'data' => $types]);
            break;
            
        case 'delete_customer_type':
            requireAdmin();
            $result = deleteCustomerType($pdo, $_POST['id']);
            echo json_encode($result);
            break;
            
        case 'add_status':
            requireAdmin();
            $result = addStatus($pdo, $_POST['name']);
            echo json_encode($result);
            break;
            
        case 'get_statuses':
            $statuses = getAllStatuses($pdo);
            echo json_encode(['success' => true, 'data' => $statuses]);
            break;
            
        case 'delete_status':
            requireAdmin();
            $result = deleteStatus($pdo, $_POST['id']);
            echo json_encode($result);
            break;
            
        case 'create_backup':
            global $db_path;
            $result = createBackup($db_path);
            echo json_encode($result);
            break;
            
        case 'get_next_customer_id':
            $next_id = getNextCustomerID($pdo);
            echo json_encode(['success' => true, 'customer_id' => $next_id]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

