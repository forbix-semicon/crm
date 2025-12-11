<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';
require_once 'modules/auth.php';

// Check if user is logged in FIRST (before database connection)
if (!isLoggedIn()) {
    // Show login popup
    $showLogin = true;
    include 'modules/login_popup.php';
    exit;
}

// Only load database modules after login check
require_once 'modules/db.php';
require_once 'modules/customer_id_generator.php';

// Initialize database schema (only creates if doesn't exist)
require_once 'modules/db_setup.php';
createDatabaseSchema();

// Get next customer ID
$nextCustomerId = getNextCustomerId();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM System</title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>CRM System</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="<?php echo BASE_PATH; ?>/modules/logout.php" class="logout-btn">Logout</a>
            </div>
        </header>

        <div class="main-content">
            <!-- Search Section -->
            <div class="search-section">
                <div class="search-box">
                    <select id="searchType" class="search-dropdown">
                        <option value="customer_id">Customer ID</option>
                        <option value="phone">Phone</option>
                        <option value="email">Email</option>
                    </select>
                    <input type="text" id="searchInput" class="search-input" placeholder="Search...">
                    <button id="searchBtn" class="search-btn">Search</button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button id="shortReportBtn" class="btn btn-secondary">Short Report</button>
                <button id="expandedReportBtn" class="btn btn-secondary">Expanded Report</button>
                <button id="backupBtn" class="btn btn-primary">Backup</button>
            </div>

            <!-- CRM Form -->
            <form id="crmForm" class="crm-form">
                <div class="form-section">
                    <h2>Customer Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customerId">Customer ID:</label>
                            <input type="text" id="customerId" name="customer_id" value="<?php echo htmlspecialchars($nextCustomerId); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="date">Date:</label>
                            <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="time">Time (IST):</label>
                            <input type="time" id="time" name="time" value="<?php echo date('H:i'); ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="customerName">Customer Name:</label>
                            <textarea id="customerName" name="customer_name" rows="2" required></textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="companyName">Company Name:</label>
                            <textarea id="companyName" name="company_name" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="primaryContact">Primary Contact:</label>
                            <textarea id="primaryContact" name="primary_contact" rows="2"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="secondaryContact">Secondary Contact:</label>
                            <textarea id="secondaryContact" name="secondary_contact" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="emailId">Email ID:</label>
                            <textarea id="emailId" name="email_id" rows="2" required></textarea>
                            <small class="error-message" id="emailError"></small>
                        </div>
                        
                        <div class="form-group">
                            <label for="city">City:</label>
                            <textarea id="city" name="city" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label>Product Category: (Multiple Selection)</label>
                            <div class="checkbox-group">
                                <label><input type="checkbox" name="product_category[]" value="Nurse Call"> Nurse Call</label>
                                <label><input type="checkbox" name="product_category[]" value="Panic Alarm"> Panic Alarm</label>
                                <label><input type="checkbox" name="product_category[]" value="Peon Call"> Peon Call</label>
                                <label><input type="checkbox" name="product_category[]" value="Long Range"> Long Range</label>
                                <label><input type="checkbox" name="product_category[]" value="Token Display"> Token Display</label>
                                <label><input type="checkbox" name="product_category[]" value="Motor Control"> Motor Control</label>
                                <label><input type="checkbox" name="product_category[]" value="Air Monitor"> Air Monitor</label>
                                <label><input type="checkbox" name="product_category[]" value="Transmitter Receiver"> Transmitter Receiver</label>
                                <label><input type="checkbox" name="product_category[]" value="Customized Solution"> Customized Solution</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="requirement">Requirement:</label>
                            <textarea id="requirement" name="requirement" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Customer Type:</label>
                            <div class="radio-group">
                                <label><input type="radio" name="customer_type" value="New Customer" checked> New Customer</label>
                                <label><input type="radio" name="customer_type" value="Existing Customer"> Existing Customer</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="source">Source:</label>
                            <select id="source" name="source" required>
                                <option value="">Select Source</option>
                                <option value="Phone Call">Phone Call</option>
                                <option value="Whatsup">Whatsup</option>
                                <option value="Website">Website</option>
                                <option value="Email">Email</option>
                                <option value="Social Media">Social Media</option>
                                <option value="Not Sure">Not Sure</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status:</label>
                            <select id="status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="1st Email">1st Email</option>
                                <option value="Talks going on">Talks going on</option>
                                <option value="Quotation sent">Quotation sent</option>
                                <option value="Demo Requested">Demo Requested</option>
                                <option value="PO released">PO released</option>
                                <option value="Waiting for Payment">Waiting for Payment</option>
                                <option value="Converted">Converted</option>
                                <option value="Rejected">Rejected</option>
                                <option value="Follow-up soon">Follow-up soon</option>
                                <option value="Follow-up later">Follow-up later</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="form-section">
                    <h2>Comments</h2>
                    <div id="commentsContainer">
                        <div class="comment-row">
                            <label>Comment 1:</label>
                            <textarea name="comments[]" rows="2"></textarea>
                            <button type="button" class="btn-remove-comment" onclick="removeComment(this)">Remove</button>
                        </div>
                    </div>
                    <button type="button" id="addCommentBtn" class="btn btn-secondary">Add Comment</button>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" id="cancelBtn" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?php echo BASE_PATH; ?>/assets/js/main.js"></script>
    <script>
        // Set base path for JavaScript
        const BASE_PATH = '<?php echo BASE_PATH; ?>';
    </script>
</body>
</html>
