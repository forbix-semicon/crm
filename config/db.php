<?php
/**
 * Database Configuration
 * Uses libSQL (SQLite fork) for database operations
 */

// Database path - works on internet server
$db_dir = __DIR__ . '/../data';
if (!file_exists($db_dir)) {
    mkdir($db_dir, 0755, true);
}

$db_path = $db_dir . '/crm.db';

try {
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Initialize database tables if they don't exist
    initDatabase($pdo);
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function initDatabase($pdo) {
    // Check if db_initialized table exists and has correct structure
    $tableExists = false;
    $columnExists = false;
    try {
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='db_initialized'");
        $tableExists = $stmt->fetch() !== false;
        
        if ($tableExists) {
            // Check if column exists
            $stmt = $pdo->query("PRAGMA table_info(db_initialized)");
            $columns = $stmt->fetchAll();
            foreach ($columns as $col) {
                if ($col['name'] === 'defaults_initialized') {
                    $columnExists = true;
                    break;
                }
            }
        }
    } catch (PDOException $e) {
        // Error checking, assume table doesn't exist
    }
    
    // Create or recreate table with correct structure
    if (!$tableExists || !$columnExists) {
        // Drop and recreate to ensure correct structure
        try {
            $pdo->exec("DROP TABLE IF EXISTS db_initialized");
        } catch (PDOException $e) {
            // Ignore errors
        }
        $pdo->exec("CREATE TABLE db_initialized (
            id INTEGER PRIMARY KEY,
            defaults_initialized INTEGER DEFAULT 0
        )");
    }
    
    // Check if defaults have been initialized before
    $defaultsInitialized = false;
    try {
        $stmt = $pdo->query("SELECT defaults_initialized FROM db_initialized WHERE id = 1");
        $flag = $stmt->fetch();
        if ($flag && isset($flag['defaults_initialized'])) {
            $defaultsInitialized = intval($flag['defaults_initialized']) == 1;
        }
    } catch (PDOException $e) {
        // Table doesn't exist or error, defaults not initialized
        $defaultsInitialized = false;
    }
    
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'agent'
    )");
    
    // Product Categories table
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL
    )");
    
    // Customer Types table
    $pdo->exec("CREATE TABLE IF NOT EXISTS customer_types (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL
    )");
    
    // Statuses table
    $pdo->exec("CREATE TABLE IF NOT EXISTS statuses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL,
        color TEXT DEFAULT '#e0e0e0'
    )");
    // Ensure color column exists for older databases
    $statusColumns = $pdo->query("PRAGMA table_info(statuses)")->fetchAll(PDO::FETCH_ASSOC);
    $hasColor = false;
    foreach ($statusColumns as $col) {
        if (strtolower($col['name']) === 'color') {
            $hasColor = true;
            break;
        }
    }
    if (!$hasColor) {
        $pdo->exec("ALTER TABLE statuses ADD COLUMN color TEXT DEFAULT '#e0e0e0'");
        $pdo->exec("UPDATE statuses SET color = '#e0e0e0' WHERE color IS NULL OR color = ''");
    }
    
    // Sources table
    $pdo->exec("CREATE TABLE IF NOT EXISTS sources (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL
    )");
    
    // Customers table
    $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer_id TEXT UNIQUE NOT NULL,
        date TEXT NOT NULL,
        time TEXT NOT NULL,
        customer_name TEXT,
        company_name TEXT,
        primary_contact TEXT,
        secondary_contact TEXT,
        email_id TEXT,
        city TEXT,
        product_category TEXT,
        requirement TEXT,
        source TEXT,
        assigned_to TEXT,
        customer_type TEXT,
        status TEXT,
        comments TEXT
    )");
    
    // Only initialize defaults if they haven't been initialized before
    if (!$defaultsInitialized) {
        // Initialize default admin user if not exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute(['admin', password_hash('admin', PASSWORD_DEFAULT), 'admin']);
        }
        
        // Initialize default agent user if not exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'agent1'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute(['agent1', password_hash('agent1', PASSWORD_DEFAULT), 'agent']);
        }
        
        // Initialize default product categories
        $defaultCategories = [
            'Category 1', 'Category 2', 'Category 3', 'Category 4', 'Category 5',
            'Category 6', 'Category 7', 'Category 8', 'Category 9', 'Category 10'
        ];
        foreach ($defaultCategories as $cat) {
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO product_categories (name) VALUES (?)");
            $stmt->execute([$cat]);
        }
        
        // Initialize default customer types
        $defaultCustomerTypes = ['New Customer', 'Existing Customer'];
        foreach ($defaultCustomerTypes as $type) {
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO customer_types (name) VALUES (?)");
            $stmt->execute([$type]);
        }
        
        // Initialize default sources
        $defaultSources = [
            'Email', 'Phone Call', 'Social Media', 'Website', 'Whatsup', 'Not Sure'
        ];
        foreach ($defaultSources as $source) {
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO sources (name) VALUES (?)");
            $stmt->execute([$source]);
        }
        
        // Initialize default statuses
        $defaultStatuses = [
            'Introduction Email' => '#5bc0de',
            'Talks going on' => '#6c757d',
            'Quotation sent' => '#17a2b8',
            'Demo Requested' => '#ffc107',
            'PO released' => '#20c997',
            'Waiting for Payment' => '#fd7e14',
            'Converted' => '#28a745',
            'Rejected' => '#dc3545',
            'Follow-up soon' => '#007bff',
            'Follow-up later' => '#6610f2'
        ];
        foreach ($defaultStatuses as $statusName => $color) {
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO statuses (name, color) VALUES (?, ?)");
            $stmt->execute([$statusName, $color]);
        }
        
        // Mark defaults as initialized
        $stmt = $pdo->prepare("INSERT OR REPLACE INTO db_initialized (id, defaults_initialized) VALUES (1, 1)");
        $stmt->execute();
    }
}

function getNextCustomerID($pdo) {
    $stmt = $pdo->query("SELECT customer_id FROM customers ORDER BY customer_id DESC LIMIT 1");
    $last = $stmt->fetch();
    
    if ($last && preg_match('/CID(\d+)/', $last['customer_id'], $matches)) {
        $nextNum = intval($matches[1]) + 1;
        return 'CID' . $nextNum;
    }
    
    return 'CID20001';
}
?>

