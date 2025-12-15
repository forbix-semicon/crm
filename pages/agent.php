<?php
require_once __DIR__ . '/../modules/admin.php';
$sources = getAllSources($pdo);
$statuses = getAllStatuses($pdo);
$customerTypes = getAllCustomerTypes($pdo);
$productCategories = getAllProductCategories($pdo);

$allowedProductCategories = array_values(array_map(function ($c) { return $c['name']; }, $productCategories));
$allowedCustomerTypes = array_values(array_map(function ($c) { return $c['name']; }, $customerTypes));
$allowedStatuses = array_values(array_map(function ($s) { return $s['name']; }, $statuses));
$allowedSources = array_values(array_map(function ($s) { return $s['name']; }, $sources));
$statusColorMap = [];
foreach ($statuses as $s) {
    $statusColorMap[$s['name']] = $s['color'] ?? '#e0e0e0';
}
?>
<script>
    // Expose allowed values for dropdowns in List/Modify popup
    window.allowedProductCategories = <?php echo json_encode($allowedProductCategories); ?>;
    window.allowedCustomerTypes = <?php echo json_encode($allowedCustomerTypes); ?>;
    window.allowedStatuses = <?php echo json_encode($allowedStatuses); ?>;
    window.allowedSources = <?php echo json_encode($allowedSources); ?>;
    window.statusColors = <?php echo json_encode($statusColorMap); ?>;
</script>
<div class="agent-dashboard">
    <div class="form-section">
        <div class="form-header">
            <h2>Customer Entry Form</h2>
            <button type="button" id="listAllBtn" class="btn-accent">List / Modify All</button>
        </div>
        <form id="customerForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="customer_id">Customer ID:</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" id="customer_id" name="customer_id" value="AUTO" readonly style="flex: 1;">
                        <button type="button" id="editCustomerIdBtn" class="btn-secondary" style="padding: 10px 15px;">Edit</button>
                    </div>
                    <small>Auto-generated from CID20001. Click Edit to update existing customer.</small>
                </div>
                <div class="form-group">
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required>
                </div>
                <div class="form-group">
                    <label for="time">Time (IST):</label>
                    <input type="time" id="time" name="time" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="customer_name">Customer Name:</label>
                    <textarea id="customer_name" name="customer_name" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label for="company_name">Company Name:</label>
                    <textarea id="company_name" name="company_name" rows="2"></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="primary_contact">Primary Contact (multiple numbers allowed):</label>
                    <input type="text" id="primary_contact" name="primary_contact" placeholder="Add multiple numbers separated by comma or new line">
                </div>
                <div class="form-group">
                    <label for="city">City:</label>
                    <input type="text" id="city" name="city">
                </div>
            </div>

            <div class="form-group">
                <label for="email_id">Email ID (one per line):</label>
                <textarea id="email_id" name="email_id" rows="3"></textarea>
                <small>Enter multiple emails, one per line. Must contain @ and .</small>
            </div>

            <div class="form-group">
                <label for="product_category_single">Product Category (Single Selection):</label>
                <select id="product_category_single" name="product_category">
                    <option value="">Select Category</option>
                    <?php foreach ($productCategories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="requirement">Requirement:</label>
                <textarea id="requirement" name="requirement" rows="4"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Customer Type:</label>
                    <div class="radio-group">
                        <?php foreach ($customerTypes as $type): ?>
                            <label class="radio-label">
                                <input type="radio" name="customer_type" value="<?php echo htmlspecialchars($type['name']); ?>">
                                <?php echo htmlspecialchars($type['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="source">Source:</label>
                    <select id="source" name="source">
                        <option value="">Select Source</option>
                        <?php foreach ($sources as $source): ?>
                            <option value="<?php echo htmlspecialchars($source['name']); ?>"><?php echo htmlspecialchars($source['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status">
                    <option value="">Select Status</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo htmlspecialchars($status['name']); ?>"><?php echo htmlspecialchars($status['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="assigned_to">Assigned to:</label>
                <input type="text" id="assigned_to" name="assigned_to" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="comments">Comments:</label>
                <textarea id="comments" name="comments" rows="4"></textarea>
                <button type="button" id="saveCommentsBtn" class="btn-secondary">Save/Update Comments</button>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Save</button>
                <button type="button" id="cancelBtn" class="btn-secondary">Cancel</button>
                <button type="button" id="shortReportBtn" class="btn-secondary">Short Report</button>
                <button type="button" id="expandedReportBtn" class="btn-secondary">Expanded Report</button>
                <button type="button" id="backupBtn" class="btn-secondary">Backup</button>
                <button type="button" id="showDatabasesBtn" class="btn-secondary">Show Databases</button>
                <button type="button" id="exportExcelBtn" class="btn-secondary">Export as Excel</button>
                <button type="button" id="importExcelBtn" class="btn-secondary">Import from Excel</button>
                <button type="button" id="importDbBtn" class="btn-secondary">Import from DB</button>
            </div>
        </form>

        <div class="search-section">
            <h3>Search</h3>
            <div class="search-box">
                <select id="searchType">
                    <option value="customer_id">Customer ID</option>
                    <option value="primary_contact">Phone</option>
                    <option value="email_id">Email</option>
                    <option value="customer_name">Customer Name</option>
                    <option value="company_name">Company Name</option>
                </select>
                <input type="text" id="searchInput" placeholder="Enter search term">
                <button type="button" id="searchBtn" class="btn-secondary">Search</button>
            </div>
            <div id="searchResults"></div>
        </div>
    </div>
</div>

<!-- List All Popup -->
<div id="listAllModal" class="modal">
    <div class="modal-content large-modal">
        <div class="modal-header">
            <h2>All Customers - Database View</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body list-all-body" style="padding: 20px;">
            <div id="listAllContent"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="document.getElementById('listAllModal').style.display='none'">Close</button>
        </div>
    </div>
</div>

<!-- Message Popup -->
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

<!-- Show Databases Popup -->
<div id="showDatabasesModal" class="modal">
    <div class="modal-content large-modal">
        <div class="modal-header">
            <h2>Backup Files - Databases & Excel</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <div id="databasesContent"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="document.getElementById('showDatabasesModal').style.display='none'">Close</button>
        </div>
    </div>
</div>

<!-- Import Excel Popup -->
<div id="importExcelModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Import from Excel</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <p>Select an Excel (.xlsx) or CSV (.csv) file from backup directory:</p>
            <div id="excelFilesList"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="document.getElementById('importExcelModal').style.display='none'">Cancel</button>
        </div>
    </div>
</div>

<!-- Excel Validation Popup -->
<div id="excelValidationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="validationTitle">Excel File Validation</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <div id="validationContent"></div>
        </div>
        <div class="modal-footer">
            <button type="button" id="confirmImportBtn" class="btn-primary" style="display: none;">Confirm Import</button>
            <button type="button" class="btn-secondary" onclick="document.getElementById('excelValidationModal').style.display='none'">Close</button>
        </div>
    </div>
</div>

<!-- Import DB Popup -->
<div id="importDbModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Import from Database</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <p>Select a database file from backup directory:</p>
            <div id="dbFilesList"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="document.getElementById('importDbModal').style.display='none'">Cancel</button>
        </div>
    </div>
</div>

