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
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'agent',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Product Categories table
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Customer Types table
    $pdo->exec("CREATE TABLE IF NOT EXISTS customer_types (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Statuses table
    $pdo->exec("CREATE TABLE IF NOT EXISTS statuses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Sources table
    $pdo->exec("CREATE TABLE IF NOT EXISTS sources (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
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
        comments TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
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
        'Nurse Call', 'Panic Alarm', 'Peon Call', 'Long Range', 'Token Display',
        'Motor Control', 'Air Monitor', 'Transmitter Receiver', 'Customized Solution'
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
        'Phone Call', 'Whatsup', 'Website', 'Email', 'Social Media', 'Not Sure'
    ];
    foreach ($defaultSources as $source) {
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO sources (name) VALUES (?)");
        $stmt->execute([$source]);
    }
    
    // Initialize default statuses
    $defaultStatuses = [
        'Introduction Email', 'Talks going on', 'Quotation sent', 'Demo Requested',
        'PO released', 'Waiting for Payment', 'Converted', 'Rejected',
        'Follow-up soon', 'Follow-up later'
    ];
    foreach ($defaultStatuses as $status) {
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO statuses (name) VALUES (?)");
        $stmt->execute([$status]);
    }
}

function getNextCustomerID($pdo) {
    $stmt = $pdo->query("SELECT customer_id FROM customers ORDER BY id DESC LIMIT 1");
    $last = $stmt->fetch();
    
    if ($last && preg_match('/CID(\d+)/', $last['customer_id'], $matches)) {
        $nextNum = intval($matches[1]) + 1;
        return 'CID' . $nextNum;
    }
    
    return 'CID20001';
}
?>

