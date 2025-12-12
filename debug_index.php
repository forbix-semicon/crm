<?php
/**
 * Debug version of index.php
 * This will show errors if they occur
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<!-- Starting index.php debug -->\n";

try {
    echo "<!-- Step 1: Starting session -->\n";
    session_start();
    echo "<!-- Session started successfully -->\n";

    echo "<!-- Step 2: Including config/db.php -->\n";
    $config_path = __DIR__ . '/config/db.php';
    if (!file_exists($config_path)) {
        die("ERROR: config/db.php not found at: $config_path<br>Current directory: " . __DIR__);
    }
    require_once $config_path;
    echo "<!-- Config loaded successfully -->\n";

    echo "<!-- Step 3: Including modules/auth.php -->\n";
    $auth_path = __DIR__ . '/modules/auth.php';
    if (!file_exists($auth_path)) {
        die("ERROR: modules/auth.php not found at: $auth_path");
    }
    require_once $auth_path;
    echo "<!-- Auth module loaded successfully -->\n";

    echo "<!-- Step 4: Processing requests -->\n";
    
    // Handle logout
    if (isset($_GET['logout'])) {
        if (function_exists('logout')) {
            logout();
        } else {
            session_destroy();
            header('Location: index.php');
            exit;
        }
    }

    // Handle login
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
        echo "<!-- Login attempt detected -->\n";
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (function_exists('login') && isset($pdo)) {
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
        } else {
            $error = 'Login function not available';
        }
    }

    // Check if user is logged in
    $logged_in = false;
    if (function_exists('checkLogin')) {
        $logged_in = checkLogin();
    } else {
        $logged_in = isset($_SESSION['user_id']);
    }
    
    $current_page = $_GET['page'] ?? 'login';

    // Redirect based on login status
    if (!$logged_in && $current_page !== 'login') {
        $current_page = 'login';
    } elseif ($logged_in && $current_page === 'login') {
        $current_page = $_SESSION['role'] === 'admin' ? 'admin' : 'agent';
    }

    echo "<!-- Step 5: Rendering page -->\n";
    echo "<!-- Logged in: " . ($logged_in ? 'Yes' : 'No') . " -->\n";
    echo "<!-- Current page: $current_page -->\n";

} catch (Exception $e) {
    die("ERROR: " . $e->getMessage() . "<br>File: " . $e->getFile() . "<br>Line: " . $e->getLine());
} catch (Error $e) {
    die("FATAL ERROR: " . $e->getMessage() . "<br>File: " . $e->getFile() . "<br>Line: " . $e->getLine());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM System - Debug</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .debug-info {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="debug-info">
        <strong>Debug Mode Active</strong><br>
        Current Directory: <?php echo __DIR__; ?><br>
        Document Root: <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Not set'; ?><br>
        Script Path: <?php echo $_SERVER['SCRIPT_NAME'] ?? 'Not set'; ?><br>
        Logged In: <?php echo $logged_in ? 'Yes' : 'No'; ?><br>
        Current Page: <?php echo $current_page; ?>
    </div>

    <?php if ($logged_in): ?>
        <div class="header">
            <div class="header-content">
                <h1>CRM System</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Unknown'); ?> (<?php echo $_SESSION['role'] ?? 'Unknown'; ?>)</span>
                    <a href="?logout=1" class="btn-logout">Logout</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="container">
        <?php if (!$logged_in): ?>
            <?php 
            $login_file = __DIR__ . '/pages/login.php';
            if (file_exists($login_file)) {
                include $login_file; 
            } else {
                echo "<p>ERROR: pages/login.php not found at: $login_file</p>";
            }
            ?>
        <?php elseif ($current_page === 'agent'): ?>
            <?php 
            $agent_file = __DIR__ . '/pages/agent.php';
            if (file_exists($agent_file)) {
                include $agent_file; 
            } else {
                echo "<p>ERROR: pages/agent.php not found at: $agent_file</p>";
            }
            ?>
        <?php elseif ($current_page === 'admin'): ?>
            <?php 
            $admin_file = __DIR__ . '/pages/admin.php';
            if (file_exists($admin_file)) {
                include $admin_file; 
            } else {
                echo "<p>ERROR: pages/admin.php not found at: $admin_file</p>";
            }
            ?>
        <?php endif; ?>
    </div>

    <script src="js/script.js"></script>
</body>
</html>

