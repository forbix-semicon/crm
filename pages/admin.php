<?php
require_once __DIR__ . '/../modules/admin.php';
$users = getAllUsers($pdo);
$productCategories = getAllProductCategories($pdo);
$customerTypes = getAllCustomerTypes($pdo);
$statuses = getAllStatuses($pdo);
$sources = getAllSources($pdo);
?>
<div class="admin-dashboard">
    <h2>Admin Dashboard</h2>
    
    <div class="admin-tabs">
        <button class="tab-btn active" onclick="switchTab('users')">Users</button>
        <button class="tab-btn" onclick="switchTab('product-categories')">Product Categories</button>
        <button class="tab-btn" onclick="switchTab('customer-types')">Customer Types</button>
        <button class="tab-btn" onclick="switchTab('inquiry-sources')">Inquiry Source</button>
        <button class="tab-btn" onclick="switchTab('statuses')">Status</button>
        <button class="tab-btn" onclick="switchTab('database')">Database</button>
    </div>

    <!-- Users Tab -->
    <div id="users-tab" class="tab-content active">
        <h3>Create New Agent</h3>
        <form id="createAgentForm" class="admin-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="agent_username">Username:</label>
                    <input type="text" id="agent_username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="agent_password">Password:</label>
                    <input type="password" id="agent_password" name="password" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-primary">Create Agent</button>
                </div>
            </div>
        </form>

        <h3>Existing Users</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td>
                                <?php if (strtolower($user['username']) !== 'admin'): ?>
                                    <button type="button" class="btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>')">Delete</button>
                                <?php else: ?>
                                    <span style="color: #999; font-style: italic;">Protected</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Product Categories Tab -->
    <div id="product-categories-tab" class="tab-content">
        <h3>Add Product Category</h3>
        <form id="addProductCategoryForm" class="admin-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="category_name">Category Name:</label>
                    <input type="text" id="category_name" name="name" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-primary">Add Category</button>
                </div>
            </div>
        </form>

        <h3>Existing Product Categories</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="productCategoriesTableBody">
                    <?php foreach ($productCategories as $cat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cat['name']); ?></td>
                            <td>
                                <button type="button" class="btn-delete" data-id="<?php echo $cat['id']; ?>" data-name="<?php echo htmlspecialchars($cat['name'], ENT_QUOTES); ?>" onclick="deleteProductCategoryById(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['name'], ENT_QUOTES); ?>')">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Customer Types Tab -->
    <div id="customer-types-tab" class="tab-content">
        <h3>Add Customer Type</h3>
        <form id="addCustomerTypeForm" class="admin-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="customer_type_name">Customer Type Name:</label>
                    <input type="text" id="customer_type_name" name="name" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-primary">Add Customer Type</button>
                </div>
            </div>
        </form>

        <h3>Existing Customer Types</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="customerTypesTableBody">
                    <?php foreach ($customerTypes as $type): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($type['name']); ?></td>
                            <td>
                                <button type="button" class="btn-delete" data-id="<?php echo $type['id']; ?>" data-name="<?php echo htmlspecialchars($type['name'], ENT_QUOTES); ?>" onclick="deleteCustomerTypeById(<?php echo $type['id']; ?>, '<?php echo htmlspecialchars($type['name'], ENT_QUOTES); ?>')">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Inquiry Sources Tab -->
    <div id="inquiry-sources-tab" class="tab-content">
        <h3>Add Inquiry Source</h3>
        <form id="addSourceForm" class="admin-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="source_name">Source Name:</label>
                    <input type="text" id="source_name" name="name" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-primary">Add Source</button>
                </div>
            </div>
        </form>

        <h3>Existing Inquiry Sources</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="sourcesTableBody">
                    <?php foreach ($sources as $source): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($source['name']); ?></td>
                            <td>
                                <button type="button" class="btn-delete" data-id="<?php echo $source['id']; ?>" data-name="<?php echo htmlspecialchars($source['name'], ENT_QUOTES); ?>" onclick="deleteSourceById(<?php echo $source['id']; ?>, '<?php echo htmlspecialchars($source['name'], ENT_QUOTES); ?>')">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Status Tab -->
    <div id="statuses-tab" class="tab-content">
        <h3>Add Status</h3>
        <form id="addStatusForm" class="admin-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="status_name">Status Name:</label>
                    <input type="text" id="status_name" name="name" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-primary">Add Status</button>
                </div>
            </div>
        </form>

        <h3>Existing Status</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="statusesTableBody">
                    <?php foreach ($statuses as $status): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($status['name']); ?></td>
                            <td>
                                <button type="button" class="btn-delete" data-id="<?php echo $status['id']; ?>" data-name="<?php echo htmlspecialchars($status['name'], ENT_QUOTES); ?>" onclick="deleteStatusById(<?php echo $status['id']; ?>, '<?php echo htmlspecialchars($status['name'], ENT_QUOTES); ?>')">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Database Tab -->
    <div id="database-tab" class="tab-content">
        <h3>Database Management</h3>
        
        <div class="admin-form" style="background: #fff3cd; border-left: 4px solid #ffc107;">
            <h4 style="margin-top: 0; color: #856404;">⚠️ Warning: Dangerous Operations</h4>
            <p style="color: #856404; margin-bottom: 15px;">
                The operations below will permanently delete data from the database. 
                Make sure you have a backup before proceeding.
            </p>
        </div>

        <div class="admin-form">
            <h4>Clear Customer Data</h4>
            <p style="color: #666; margin-bottom: 15px;">
                This will delete all customer records from the database (crm.db). 
                Configuration data (users, categories, types, status) will remain intact.
            </p>
            <div class="form-row">
                <div class="form-group">
                    <button type="button" class="btn-delete" onclick="clearDatabase()" style="padding: 12px 24px; font-size: 16px;">
                        Clear All Customer Data
                    </button>
                </div>
            </div>
        </div>

        <div class="admin-form" style="margin-top: 20px;">
            <h4>Database Statistics</h4>
            <div id="databaseStats" style="padding: 15px; background: #f8f9fa; border-radius: 5px;">
                <p style="color: #666;">Loading statistics...</p>
            </div>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div id="messageModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="messageTitle">Message</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <p id="messageText"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary" onclick="closeMessageModal()">OK</button>
        </div>
    </div>
</div>

