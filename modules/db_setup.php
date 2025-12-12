<?php
/**
 * Database Setup Module
 * Creates tables and initializes libSQL database schema
 */

require_once __DIR__ . '/../config.php';

function createDatabaseSchema() {
    try {
        require_once __DIR__ . '/db.php';
        $pdo = Database::getConnection();
        
        // Create users table (for agents and admin)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(20) NOT NULL DEFAULT 'agent',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Create default admin user if not exists
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
        $result = $stmt->fetch();
        if ($result['count'] == 0) {
            $pdo->exec("INSERT INTO users (username, password, role) VALUES ('admin', 'admin', 'admin')");
        }
        
        // Create default agent user if not exists
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE username = 'agent1'");
        $result = $stmt->fetch();
        if ($result['count'] == 0) {
            $pdo->exec("INSERT INTO users (username, password, role) VALUES ('agent1', 'agent1', 'agent')");
        }
        
        // Create product_categories table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS product_categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) UNIQUE NOT NULL,
                display_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Insert default product categories if table is empty
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM product_categories");
        $result = $stmt->fetch();
        if ($result['count'] == 0) {
            $defaultCategories = [
                'Nurse Call', 'Panic Alarm', 'Peon Call', 'Long Range', 
                'Token Display', 'Motor Control', 'Air Monitor', 
                'Transmitter Receiver', 'Customized Solution'
            ];
            $stmt = $pdo->prepare("INSERT INTO product_categories (name, display_order) VALUES (?, ?)");
            foreach ($defaultCategories as $index => $category) {
                $stmt->execute([$category, $index + 1]);
            }
        }
        
        // Create customer_types table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS customer_types (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(50) UNIQUE NOT NULL,
                display_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Insert default customer types if table is empty
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM customer_types");
        $result = $stmt->fetch();
        if ($result['count'] == 0) {
            $defaultTypes = ['New Customer', 'Existing Customer'];
            $stmt = $pdo->prepare("INSERT INTO customer_types (name, display_order) VALUES (?, ?)");
            foreach ($defaultTypes as $index => $type) {
                $stmt->execute([$type, $index + 1]);
            }
        }
        
        // Create statuses table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS statuses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(50) UNIQUE NOT NULL,
                display_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Insert default statuses if table is empty
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM statuses");
        $result = $stmt->fetch();
        if ($result['count'] == 0) {
            $defaultStatuses = [
                'Introduction Email', 'Talks going on', 'Quotation sent',
                'Demo Requested', 'PO released', 'Waiting for Payment',
                'Converted', 'Rejected', 'Follow-up soon', 'Follow-up later'
            ];
            $stmt = $pdo->prepare("INSERT INTO statuses (name, display_order) VALUES (?, ?)");
            foreach ($defaultStatuses as $index => $status) {
                $stmt->execute([$status, $index + 1]);
            }
        }
        
        // Create customers table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS customers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_id VARCHAR(20) UNIQUE NOT NULL,
                date DATE NOT NULL,
                time TIME NOT NULL,
                customer_name TEXT,
                company_name TEXT,
                primary_contact TEXT,
                secondary_contact TEXT,
                email_id TEXT,
                city TEXT,
                product_category TEXT,
                requirement TEXT,
                source VARCHAR(50),
                assigned_to VARCHAR(50),
                customer_type VARCHAR(50),
                status VARCHAR(50),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Create comments table (separate table for multiple comments)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS customer_comments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_id VARCHAR(20) NOT NULL,
                comment_text TEXT,
                comment_order INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
            )
        ");
        
        // Create indexes for faster lookups
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_customer_id ON customers(customer_id)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_email ON customers(email_id)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_assigned_to ON customers(assigned_to)");
        
        return true;
    } catch (PDOException $e) {
        error_log("Database setup error: " . $e->getMessage());
        return false;
    }
}

// Auto-create schema if this file is accessed directly
if (php_sapi_name() === 'cli' || (isset($_GET['setup']) && $_GET['setup'] === 'db')) {
    if (createDatabaseSchema()) {
        echo "Database schema created successfully!\n";
    } else {
        echo "Error creating database schema!\n";
    }
}
