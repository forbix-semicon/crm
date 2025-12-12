<?php
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Login</title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-overlay">
        <div class="login-popup">
            <h2>CRM Login</h2>
            <form id="loginForm" method="POST" action="<?php echo BASE_PATH; ?>/modules/login_handler.php">
                <div class="form-group">
                    <label for="username">Login:</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Passcode:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <?php if (isset($_GET['error'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <div style="margin-top: 15px; font-size: 12px; color: #666; text-align: center;">
                <p>Admin: admin / admin</p>
                <p>Agent: agent1 / agent1</p>
            </div>
        </div>
    </div>
</body>
</html>
