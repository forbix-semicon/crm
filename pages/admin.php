<?php
require_once __DIR__ . '/../modules/admin.php';
$users = getAllUsers($pdo);
$productCategories = getAllProductCategories($pdo);
$customerTypes = getAllCustomerTypes($pdo);
$statuses = getAllStatuses($pdo);
?>
<div class="admin-dashboard">
    <h2>Admin Dashboard</h2>
    
    <div class="admin-tabs">
        <button class="tab-btn active" onclick="switchTab('users')">Users</button>
        <button class="tab-btn" onclick="switchTab('product-categories')">Product Categories</button>
        <button class="tab-btn" onclick="switchTab('customer-types')">Customer Types</button>
        <button class="tab-btn" onclick="switchTab('statuses')">Statuses</button>
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
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
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
                        <th>ID</th>
                        <th>Name</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="productCategoriesTableBody">
                    <?php foreach ($productCategories as $cat): ?>
                        <tr>
                            <td><?php echo $cat['id']; ?></td>
                            <td><?php echo htmlspecialchars($cat['name']); ?></td>
                            <td><?php echo htmlspecialchars($cat['created_at']); ?></td>
                            <td>
                                <button type="button" class="btn-delete" onclick="deleteProductCategory(<?php echo $cat['id']; ?>)">Delete</button>
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
                        <th>ID</th>
                        <th>Name</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="customerTypesTableBody">
                    <?php foreach ($customerTypes as $type): ?>
                        <tr>
                            <td><?php echo $type['id']; ?></td>
                            <td><?php echo htmlspecialchars($type['name']); ?></td>
                            <td><?php echo htmlspecialchars($type['created_at']); ?></td>
                            <td>
                                <button type="button" class="btn-delete" onclick="deleteCustomerType(<?php echo $type['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Statuses Tab -->
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

        <h3>Existing Statuses</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="statusesTableBody">
                    <?php foreach ($statuses as $status): ?>
                        <tr>
                            <td><?php echo $status['id']; ?></td>
                            <td><?php echo htmlspecialchars($status['name']); ?></td>
                            <td><?php echo htmlspecialchars($status['created_at']); ?></td>
                            <td>
                                <button type="button" class="btn-delete" onclick="deleteStatus(<?php echo $status['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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

