<?php
/**
 * Installation Verification Script
 * Run this once to verify your CRM installation
 */

echo "<h1>CRM Installation Check</h1>";
echo "<pre>";

$errors = [];
$warnings = [];
$success = [];

// Check PHP version
echo "Checking PHP version...\n";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    $success[] = "PHP version: " . PHP_VERSION . " (OK)";
} else {
    $errors[] = "PHP version " . PHP_VERSION . " is too old. Requires PHP 7.4 or higher.";
}

// Check PDO SQLite
echo "Checking PDO SQLite support...\n";
if (extension_loaded('pdo_sqlite')) {
    $success[] = "PDO SQLite extension is loaded (OK)";
} else {
    $errors[] = "PDO SQLite extension is not loaded. Please install php-pdo-sqlite.";
}

// Check directory permissions
echo "Checking directory permissions...\n";
$data_dir = __DIR__ . '/data';
if (is_dir($data_dir) || mkdir($data_dir, 0755, true)) {
    if (is_writable($data_dir)) {
        $success[] = "Data directory is writable (OK)";
    } else {
        $errors[] = "Data directory is not writable. Please set permissions to 755 or 775.";
    }
} else {
    $errors[] = "Cannot create data directory. Please create it manually with write permissions.";
}

$backup_dir = $data_dir . '/backups';
if (is_dir($backup_dir) || mkdir($backup_dir, 0755, true)) {
    if (is_writable($backup_dir)) {
        $success[] = "Backup directory is writable (OK)";
    } else {
        $warnings[] = "Backup directory is not writable. Backups may not work.";
    }
}

// Check required files
echo "Checking required files...\n";
$required_files = [
    'index.php',
    'api.php',
    'config/db.php',
    'modules/auth.php',
    'modules/admin.php',
    'modules/customer.php',
    'modules/backup.php',
    'pages/login.php',
    'pages/agent.php',
    'pages/admin.php',
    'css/style.css',
    'js/script.js'
];

foreach ($required_files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $success[] = "File exists: $file (OK)";
    } else {
        $errors[] = "Missing file: $file";
    }
}

// Test database connection
echo "Testing database connection...\n";
try {
    require_once __DIR__ . '/config/db.php';
    if (isset($pdo)) {
        $success[] = "Database connection successful (OK)";
        
        // Check if tables exist
        $tables = ['users', 'customers', 'product_categories', 'customer_types', 'statuses', 'sources'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
            if ($stmt->fetch()) {
                $success[] = "Table '$table' exists (OK)";
            } else {
                $warnings[] = "Table '$table' does not exist. Will be created on first use.";
            }
        }
    }
} catch (Exception $e) {
    $errors[] = "Database connection failed: " . $e->getMessage();
}

// Display results
echo "\n=== RESULTS ===\n\n";

if (!empty($success)) {
    echo "SUCCESS:\n";
    foreach ($success as $msg) {
        echo "  ✓ $msg\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "WARNINGS:\n";
    foreach ($warnings as $msg) {
        echo "  ⚠ $msg\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "ERRORS:\n";
    foreach ($errors as $msg) {
        echo "  ✗ $msg\n";
    }
    echo "\n";
    echo "Please fix the errors before using the CRM system.\n";
} else {
    echo "✓ Installation check passed! You can now use the CRM system.\n";
    echo "\nDefault login credentials:\n";
    echo "  Admin: admin / admin\n";
    echo "  Agent: agent1 / agent1\n";
    echo "\n⚠ Please change default passwords after first login!\n";
}

echo "</pre>";
?>

