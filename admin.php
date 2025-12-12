<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';
require_once 'modules/auth.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: index.php');
    exit;
}

// Only load database modules after login check
require_once 'modules/db.php';

// Initialize database schema
require_once 'modules/db_setup.php';
createDatabaseSchema();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM System - Admin Panel</title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/admin.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>CRM System - Admin Panel</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="<?php echo BASE_PATH; ?>/index.php" class="btn btn-secondary" style="text-decoration: none; padding: 8px 15px;">Agent View</a>
                <a href="<?php echo BASE_PATH; ?>/modules/logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <div class="main-content">
            <div class="admin-tabs">
                <button class="tab-btn active" data-tab="agents">Agents</button>
                <button class="tab-btn" data-tab="categories">Product Categories</button>
                <button class="tab-btn" data-tab="types">Customer Types</button>
                <button class="tab-btn" data-tab="statuses">Statuses</button>
            </div>

            <!-- Agents Tab -->
            <div class="tab-content active" id="agents-tab">
                <h2>Manage Agents</h2>
                <div class="admin-section">
                    <h3>Create New Agent</h3>
                    <form id="agentForm" class="admin-form">
                        <input type="hidden" id="agentId" name="id">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="agentUsername">Username:</label>
                                <input type="text" id="agentUsername" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="agentPassword">Password:</label>
                                <input type="password" id="agentPassword" name="password" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" id="agentSubmitBtn">Create Agent</button>
                                <button type="button" class="btn btn-secondary" id="agentCancelBtn" style="display:none;">Cancel</button>
                            </div>
                        </div>
                    </form>
                    <h3>Existing Agents</h3>
                    <div id="agentsList" class="admin-list"></div>
                </div>
            </div>

            <!-- Product Categories Tab -->
            <div class="tab-content" id="categories-tab">
                <h2>Manage Product Categories</h2>
                <div class="admin-section">
                    <h3>Add/Modify Category</h3>
                    <form id="categoryForm" class="admin-form">
                        <input type="hidden" id="categoryId" name="id">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="categoryName">Category Name:</label>
                                <input type="text" id="categoryName" name="name" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" id="categorySubmitBtn">Add Category</button>
                                <button type="button" class="btn btn-secondary" id="categoryCancelBtn" style="display:none;">Cancel</button>
                            </div>
                        </div>
                    </form>
                    <h3>Existing Categories</h3>
                    <div id="categoriesList" class="admin-list"></div>
                </div>
            </div>

            <!-- Customer Types Tab -->
            <div class="tab-content" id="types-tab">
                <h2>Manage Customer Types</h2>
                <div class="admin-section">
                    <h3>Add/Modify Customer Type</h3>
                    <form id="typeForm" class="admin-form">
                        <input type="hidden" id="typeId" name="id">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="typeName">Type Name:</label>
                                <input type="text" id="typeName" name="name" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" id="typeSubmitBtn">Add Type</button>
                                <button type="button" class="btn btn-secondary" id="typeCancelBtn" style="display:none;">Cancel</button>
                            </div>
                        </div>
                    </form>
                    <h3>Existing Types</h3>
                    <div id="typesList" class="admin-list"></div>
                </div>
            </div>

            <!-- Statuses Tab -->
            <div class="tab-content" id="statuses-tab">
                <h2>Manage Statuses</h2>
                <div class="admin-section">
                    <h3>Add/Modify Status</h3>
                    <form id="statusForm" class="admin-form">
                        <input type="hidden" id="statusId" name="id">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="statusName">Status Name:</label>
                                <input type="text" id="statusName" name="name" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" id="statusSubmitBtn">Add Status</button>
                                <button type="button" class="btn btn-secondary" id="statusCancelBtn" style="display:none;">Cancel</button>
                            </div>
                        </div>
                    </form>
                    <h3>Existing Statuses</h3>
                    <div id="statusesList" class="admin-list"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_PATH; ?>/assets/js/main.js"></script>
    <script src="<?php echo BASE_PATH; ?>/assets/js/admin.js"></script>
    <script>
        const BASE_PATH = '<?php echo BASE_PATH; ?>';
    </script>
</body>
</html>


