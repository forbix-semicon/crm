<?php
/**
 * Database Connection Module
 * Handles SQLite database connections
 */

require_once __DIR__ . '/../config.php';

class Database {
    private static $connection = null;
    
    public static function getConnection() {
        if (self::$connection === null) {
            try {
                // Create data directory if it doesn't exist
                $dataDir = dirname(DB_PATH);
                if (!is_dir($dataDir)) {
                    mkdir($dataDir, 0755, true);
                }
                
                // Connect to SQLite database
                self::$connection = new PDO('sqlite:' . DB_PATH);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                // Enable foreign keys
                self::$connection->exec('PRAGMA foreign_keys = ON');
                
            } catch (PDOException $e) {
                die("
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Database Connection Error</title>
                        <style>
                            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
                            .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                            .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0; }
                            code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <h2>Database Connection Error</h2>
                            <div class='error'>
                                <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "
                            </div>
                            <p>Please check file permissions for the data directory.</p>
                            <p>Database path: <code>" . htmlspecialchars(DB_PATH) . "</code></p>
                        </div>
                    </body>
                    </html>
                ");
            }
        }
        return self::$connection;
    }
    
    public static function closeConnection() {
        self::$connection = null;
    }
}
