<?php
/**
 * Database Setup Module
 * Creates tables and initializes SQLite database schema
 */

require_once __DIR__ . '/../config.php';

function createDatabaseSchema() {
    try {
        require_once __DIR__ . '/db.php';
        $pdo = Database::getConnection();
        
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
                FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
            )
        ");
        
        // Create index on customer_id for faster lookups
        $pdo->exec("
            CREATE INDEX IF NOT EXISTS idx_customer_id ON customers(customer_id)
        ");
        
        // Create index on email for search
        $pdo->exec("
            CREATE INDEX IF NOT EXISTS idx_email ON customers(email_id)
        ");
        
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
