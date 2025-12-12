<?php
/**
 * Configuration file
 * Sets base path and database settings
 */

// Get the base path (directory where index.php is located)
$basePath = dirname($_SERVER['PHP_SELF']);
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}

// Define base URL path
define('BASE_PATH', $basePath);

// libSQL (SQLite fork) Database file path
define('DB_PATH', __DIR__ . '/data/crm.db');

// Timezone
date_default_timezone_set('Asia/Kolkata'); // IST
