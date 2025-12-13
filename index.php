<?php
/**
 * Main Entry Point for CRM System
 */

session_start();

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/modules/auth.php';

// Handle logout
if (isset($_GET['logout'])) {
    logout();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password, $pdo)) {
        if ($_SESSION['role'] === 'admin') {
            header('Location: index.php?page=admin');
        } else {
            header('Location: index.php?page=agent');
        }
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}

// Check if user is logged in
$logged_in = checkLogin();
$current_page = $_GET['page'] ?? 'login';

// Redirect based on login status
if (!$logged_in && $current_page !== 'login') {
    $current_page = 'login';
} elseif ($logged_in && $current_page === 'login') {
    $current_page = $_SESSION['role'] === 'admin' ? 'admin' : 'agent';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM System</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime(__DIR__ . '/css/style.css'); ?>">
</head>
<body>
    <?php if ($logged_in): ?>
        <div class="header">
            <div class="header-content">
                <h1>CRM System</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo $_SESSION['role']; ?>)</span>
                    <a href="?logout=1" class="btn-logout">Logout</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="container">
        <?php if (!$logged_in): ?>
            <?php include 'pages/login.php'; ?>
        <?php elseif ($current_page === 'agent'): ?>
            <?php include 'pages/agent.php'; ?>
        <?php elseif ($current_page === 'admin'): ?>
            <?php include 'pages/admin.php'; ?>
        <?php endif; ?>
    </div>

    <script src="js/script.js?v=<?php echo filemtime(__DIR__ . '/js/script.js'); ?>"></script>
</body>
</html>

