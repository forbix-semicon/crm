<?php
/**
 * Simple Test File
 * Access this file to verify PHP is working
 */

echo "<h1>PHP Test - CRM System</h1>";
echo "<p>If you can see this, PHP is working on your server.</p>";
echo "<hr>";

echo "<h2>PHP Information:</h2>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";

echo "<h2>Directory Check:</h2>";
echo "<p>Current Directory: " . __DIR__ . "</p>";
echo "<p>Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "</p>";

echo "<h2>File Check:</h2>";
$files_to_check = [
    'index.php',
    'config/db.php',
    'modules/auth.php',
    'pages/login.php'
];

foreach ($files_to_check as $file) {
    $full_path = __DIR__ . '/' . $file;
    $exists = file_exists($full_path);
    echo "<p>" . ($exists ? "✓" : "✗") . " $file - " . ($exists ? "EXISTS" : "NOT FOUND") . "</p>";
}

echo "<h2>Directory Permissions:</h2>";
$dirs_to_check = [
    __DIR__,
    __DIR__ . '/data',
    __DIR__ . '/data/backups'
];

foreach ($dirs_to_check as $dir) {
    if (file_exists($dir)) {
        $writable = is_writable($dir);
        $readable = is_readable($dir);
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "<p>✓ $dir - Readable: " . ($readable ? "Yes" : "No") . ", Writable: " . ($writable ? "Yes" : "No") . ", Permissions: $perms</p>";
    } else {
        echo "<p>✗ $dir - NOT EXISTS</p>";
    }
}

echo "<h2>PHP Extensions:</h2>";
$extensions = ['pdo', 'pdo_sqlite', 'session'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<p>" . ($loaded ? "✓" : "✗") . " $ext - " . ($loaded ? "LOADED" : "NOT LOADED") . "</p>";
}

echo "<h2>Error Reporting:</h2>";
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "<p>Error reporting is enabled. Check below for any errors:</p>";

echo "<hr>";
echo "<h2>Try Index.php:</h2>";
echo "<p><a href='index.php'>Click here to try index.php</a></p>";

echo "<h2>Try Installation Check:</h2>";
echo "<p><a href='install_check.php'>Click here to run installation check</a></p>";
?>

