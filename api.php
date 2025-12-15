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
require_once __DIR__ . '/modules/export.php';
require_once __DIR__ . '/modules/import.php';

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
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $result = deleteProductCategory($pdo, $id);
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
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $result = deleteCustomerType($pdo, $id);
            echo json_encode($result);
            break;
            
        case 'add_status':
            requireAdmin();
            $color = isset($_POST['color']) ? $_POST['color'] : '#e0e0e0';
            $result = addStatus($pdo, $_POST['name'], $color);
            echo json_encode($result);
            break;
            
        case 'get_statuses':
            $statuses = getAllStatuses($pdo);
            echo json_encode(['success' => true, 'data' => $statuses]);
            break;
            
        case 'delete_status':
            requireAdmin();
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $result = deleteStatus($pdo, $id);
            echo json_encode($result);
            break;
        
        case 'update_status_color':
            requireAdmin();
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $color = isset($_POST['color']) ? $_POST['color'] : '';
            $result = updateStatusColor($pdo, $id, $color);
            echo json_encode($result);
            break;
            
        case 'add_source':
            requireAdmin();
            $result = addSource($pdo, $_POST['name']);
            echo json_encode($result);
            break;
            
        case 'get_sources':
            $sources = getAllSources($pdo);
            echo json_encode(['success' => true, 'data' => $sources]);
            break;
            
        case 'delete_source':
            requireAdmin();
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $result = deleteSource($pdo, $id);
            echo json_encode($result);
            break;
            
        case 'delete_user':
            requireAdmin();
            $result = deleteUser($pdo, $_POST['id']);
            echo json_encode($result);
            break;
            
        case 'clear_database':
            requireAdmin();
            $result = clearDatabase($pdo);
            echo json_encode($result);
            break;
            
        case 'get_database_stats':
            requireAdmin();
            // Get database path from config
            require_once __DIR__ . '/config/db.php';
            $result = getDatabaseStats($pdo, $db_path);
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
            
        case 'list_backup_files':
            global $db_path;
            $backup_dir = dirname($db_path) . '/backups';
            $result = listBackupFiles($backup_dir);
            echo json_encode($result);
            break;
            
        case 'export_excel':
            global $db_path;
            $backup_dir = dirname($db_path) . '/backups';
            $result = exportToExcel($pdo, $backup_dir);
            echo json_encode($result);
            break;
            
        case 'validate_excel':
            global $db_path;
            $backup_dir = dirname($db_path) . '/backups';
            $filename = $_POST['filename'] ?? $_GET['filename'] ?? '';
            if (empty($filename)) {
                echo json_encode(['success' => false, 'message' => 'Filename is required']);
                break;
            }
            $filepath = $backup_dir . '/' . $filename;
            $result = validateExcelFile($pdo, $filepath);
            echo json_encode($result);
            break;
            
        case 'import_excel':
            global $db_path;
            $backup_dir = dirname($db_path) . '/backups';
            $filename = $_POST['filename'] ?? '';
            if (empty($filename)) {
                echo json_encode(['success' => false, 'message' => 'Filename is required']);
                break;
            }
            $filepath = $backup_dir . '/' . $filename;
            $result = importFromExcel($pdo, $filepath);
            echo json_encode($result);
            break;
            
        case 'import_db':
            global $db_path;
            $backup_dir = dirname($db_path) . '/backups';
            $filename = $_POST['filename'] ?? '';
            if (empty($filename)) {
                echo json_encode(['success' => false, 'message' => 'Filename is required']);
                break;
            }
            $source_db_path = $backup_dir . '/' . $filename;
            $result = importFromDB($pdo, $source_db_path, $db_path);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

