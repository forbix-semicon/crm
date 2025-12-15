/**
 * CRM System JavaScript
 */

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializePage();
});

function initializePage() {
    // Set default date and time
    if (document.getElementById('date')) {
        const today = new Date();
        const dateStr = today.toISOString().split('T')[0];
        document.getElementById('date').value = dateStr;
    }
    
    if (document.getElementById('time')) {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('time').value = hours + ':' + minutes;
    }
    
    // Get next customer ID
    if (document.getElementById('customer_id')) {
        fetchNextCustomerID();
    }
    
    // Setup event listeners
    setupEventListeners();
}

function fetchNextCustomerID() {
    fetch('api.php?action=get_next_customer_id')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('customer_id').value = data.customer_id;
            }
        })
        .catch(error => console.error('Error:', error));
}

function setupEventListeners() {
    // Agent form submission
    const customerForm = document.getElementById('customerForm');
    if (customerForm) {
        customerForm.addEventListener('submit', handleCustomerSubmit);
    }
    
    // Cancel button
    const cancelBtn = document.getElementById('cancelBtn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', clearForm);
    }
    
    // Edit Customer ID button
    const editCustomerIdBtn = document.getElementById('editCustomerIdBtn');
    if (editCustomerIdBtn) {
        editCustomerIdBtn.addEventListener('click', toggleCustomerIdEdit);
    }
    
    // Load customer when customer ID is entered (for updates)
    const customerIdInput = document.getElementById('customer_id');
    if (customerIdInput) {
        customerIdInput.addEventListener('blur', function() {
            const customerId = this.value.trim();
            if (customerId && customerId !== 'AUTO' && customerId.length >= 5) {
                loadCustomerData(customerId);
            }
        });
    }
    
    // Save Comments button
    const saveCommentsBtn = document.getElementById('saveCommentsBtn');
    if (saveCommentsBtn) {
        saveCommentsBtn.addEventListener('click', saveComments);
    }
    
    // Report buttons (empty for now)
    const shortReportBtn = document.getElementById('shortReportBtn');
    if (shortReportBtn) {
        shortReportBtn.addEventListener('click', () => showMessage('Short Report', 'Feature coming soon...'));
    }
    
    const expandedReportBtn = document.getElementById('expandedReportBtn');
    if (expandedReportBtn) {
        expandedReportBtn.addEventListener('click', () => showMessage('Expanded Report', 'Feature coming soon...'));
    }
    
    // Backup button
    const backupBtn = document.getElementById('backupBtn');
    if (backupBtn) {
        backupBtn.addEventListener('click', createBackup);
    }
    
    // List All button
    const listAllBtn = document.getElementById('listAllBtn');
    if (listAllBtn) {
        listAllBtn.addEventListener('click', showListAll);
    }
    
    // Show Databases button
    const showDatabasesBtn = document.getElementById('showDatabasesBtn');
    if (showDatabasesBtn) {
        showDatabasesBtn.addEventListener('click', showDatabases);
    }
    
    // Export Excel button
    const exportExcelBtn = document.getElementById('exportExcelBtn');
    if (exportExcelBtn) {
        exportExcelBtn.addEventListener('click', exportToExcel);
    }
    
    // Import Excel button
    const importExcelBtn = document.getElementById('importExcelBtn');
    if (importExcelBtn) {
        importExcelBtn.addEventListener('click', showImportExcel);
    }
    
    // Import DB button
    const importDbBtn = document.getElementById('importDbBtn');
    if (importDbBtn) {
        importDbBtn.addEventListener('click', showImportDb);
    }
    
    // Search button
    const searchBtn = document.getElementById('searchBtn');
    if (searchBtn) {
        searchBtn.addEventListener('click', performSearch);
    }
    
    // Admin form submissions
    const createAgentForm = document.getElementById('createAgentForm');
    if (createAgentForm) {
        createAgentForm.addEventListener('submit', handleCreateAgent);
    }
    
    const addProductCategoryForm = document.getElementById('addProductCategoryForm');
    if (addProductCategoryForm) {
        addProductCategoryForm.addEventListener('submit', handleAddProductCategory);
    }
    
    const addCustomerTypeForm = document.getElementById('addCustomerTypeForm');
    if (addCustomerTypeForm) {
        addCustomerTypeForm.addEventListener('submit', handleAddCustomerType);
    }
    
    const addStatusForm = document.getElementById('addStatusForm');
    if (addStatusForm) {
        addStatusForm.addEventListener('submit', handleAddStatus);
    }
    
    const addSourceForm = document.getElementById('addSourceForm');
    if (addSourceForm) {
        addSourceForm.addEventListener('submit', handleAddSource);
    }
    
    // Modal close handlers
    setupModalHandlers();
}

function setupModalHandlers() {
    const modals = document.querySelectorAll('.modal');
    const closeButtons = document.querySelectorAll('.close');
    
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });
    
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });
}

// Customer Form Handling
function handleCustomerSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = {};
    
    // Convert FormData to object
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    // Product category already single select
    
    // Validate email
    if (data.email_id) {
        const emails = data.email_id.split('\n').map(e => e.trim()).filter(e => e);
        for (let email of emails) {
            if (!isValidEmail(email)) {
                showMessage('Validation Error', 'Invalid email address: ' + email + '\nEmail must contain @ and .');
                return;
            }
        }
    }
    
    // If customer_id is AUTO, remove it (will be generated server-side)
    if (data.customer_id === 'AUTO') {
        delete data.customer_id;
    }
    
    const formDataToSend = new FormData();
    for (let key in data) {
        formDataToSend.append(key, data[key]);
    }
    formDataToSend.append('action', 'save_customer');
    
    fetch('api.php', {
        method: 'POST',
        body: formDataToSend
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('Success', result.message);
            // Update customer ID if new customer
            if (result.customer_id) {
                document.getElementById('customer_id').value = result.customer_id;
                // Update to allow updates
                document.getElementById('customer_id').readOnly = false;
            }
            // Refresh next customer ID for next entry
            fetchNextCustomerID();
        } else {
            showMessage('Error', result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred while saving');
    });
}

function isValidEmail(email) {
    return email.includes('@') && email.includes('.') && 
           email.indexOf('@') < email.lastIndexOf('.') &&
           email.indexOf('@') > 0 &&
           email.lastIndexOf('.') < email.length - 1;
}

function toggleCustomerIdEdit() {
    const customerIdInput = document.getElementById('customer_id');
    const editBtn = document.getElementById('editCustomerIdBtn');
    
    if (customerIdInput.readOnly) {
        customerIdInput.readOnly = false;
        if (customerIdInput.value === 'AUTO') {
            customerIdInput.value = '';
        }
        editBtn.textContent = 'Auto';
        editBtn.title = 'Switch back to auto-generated ID';
    } else {
        customerIdInput.readOnly = true;
        if (!customerIdInput.value || customerIdInput.value.trim() === '') {
            customerIdInput.value = 'AUTO';
            fetchNextCustomerID();
        }
        editBtn.textContent = 'Edit';
        editBtn.title = 'Edit customer ID to update existing customer';
    }
}

function clearForm() {
    const form = document.getElementById('customerForm');
    if (form) {
        form.reset();
        // Reset date and time to current
        const today = new Date();
        document.getElementById('date').value = today.toISOString().split('T')[0];
        const hours = String(today.getHours()).padStart(2, '0');
        const minutes = String(today.getMinutes()).padStart(2, '0');
        document.getElementById('time').value = hours + ':' + minutes;
        
        // Reset customer ID
        const customerIdInput = document.getElementById('customer_id');
        customerIdInput.value = 'AUTO';
        customerIdInput.readOnly = true;
        const editBtn = document.getElementById('editCustomerIdBtn');
        if (editBtn) {
            editBtn.textContent = 'Edit';
        }
        fetchNextCustomerID();
    }
}

function saveComments() {
    const customerId = document.getElementById('customer_id').value;
    const comments = document.getElementById('comments').value;
    
    if (customerId === 'AUTO' || !customerId) {
        showMessage('Error', 'Please save customer first before saving comments');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'update_customer_field');
    formData.append('customer_id', customerId);
    formData.append('field', 'comments');
    formData.append('value', comments);
    
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('Success', 'Comments saved successfully');
        } else {
            showMessage('Error', result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred while saving comments');
    });
}

function createBackup() {
    const formData = new FormData();
    formData.append('action', 'create_backup');
    
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('Backup Created', 'Backup created successfully: ' + result.file);
        } else {
            showMessage('Error', result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred while creating backup');
    });
}

function showListAll() {
    fetch('api.php?action=get_all_customers')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                displayAllCustomers(result.data);
                document.getElementById('listAllModal').style.display = 'block';
            } else {
                showMessage('Error', result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error', 'An error occurred while fetching customers');
        });
}

function showDatabases() {
    fetch('api.php?action=list_backup_files')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                displayBackupFiles(result.data);
                document.getElementById('showDatabasesModal').style.display = 'block';
            } else {
                showMessage('Error', result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error', 'An error occurred while fetching backup files');
        });
}

function displayBackupFiles(files) {
    const container = document.getElementById('databasesContent');
    
    if (files.length === 0) {
        container.innerHTML = '<p style="text-align: center; padding: 20px;">No backup files found.</p>';
        return;
    }
    
    let html = '<div style="margin-bottom: 15px; padding: 10px; background: #e7f3ff; border-radius: 5px; border-left: 4px solid #667eea;">';
    html += '<strong>Total Files: ' + files.length + '</strong>';
    html += '</div>';
    
    html += '<table class="data-table" style="width: 100%; border-collapse: collapse;">';
    html += '<thead><tr>';
    html += '<th style="padding: 10px; text-align: left; background: #667eea; color: white;">Filename</th>';
    html += '<th style="padding: 10px; text-align: left; background: #667eea; color: white;">Type</th>';
    html += '<th style="padding: 10px; text-align: left; background: #667eea; color: white;">Size</th>';
    html += '<th style="padding: 10px; text-align: left; background: #667eea; color: white;">Modified</th>';
    html += '</tr></thead><tbody>';
    
    files.forEach(file => {
        const sizeKB = (file.size / 1024).toFixed(2);
        html += '<tr>';
        html += '<td style="padding: 8px; border-bottom: 1px solid #ddd;">' + escapeHtml(file.filename) + '</td>';
        html += '<td style="padding: 8px; border-bottom: 1px solid #ddd;">' + escapeHtml(file.type.toUpperCase()) + '</td>';
        html += '<td style="padding: 8px; border-bottom: 1px solid #ddd;">' + sizeKB + ' KB</td>';
        html += '<td style="padding: 8px; border-bottom: 1px solid #ddd;">' + escapeHtml(file.modified) + '</td>';
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

function exportToExcel() {
    if (!confirm('Export all customers to Excel file?')) {
        return;
    }
    
    fetch('api.php?action=export_excel', {
        method: 'POST'
    })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showMessage('Success', 'Excel file exported successfully: ' + result.filename);
                // Refresh backup files list if modal is open
                const modal = document.getElementById('showDatabasesModal');
                if (modal && modal.style.display === 'block') {
                    showDatabases();
                }
            } else {
                showMessage('Error', result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error', 'An error occurred while exporting Excel file');
        });
}

function showImportExcel() {
    fetch('api.php?action=list_backup_files')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const excelFiles = result.data.filter(f => f.type === 'excel' || f.type === 'csv');
                displayImportExcelFiles(excelFiles);
                document.getElementById('importExcelModal').style.display = 'block';
            } else {
                showMessage('Error', result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error', 'An error occurred while fetching Excel files');
        });
}

function displayImportExcelFiles(files) {
    const container = document.getElementById('excelFilesList');
    
    if (files.length === 0) {
        container.innerHTML = '<p style="text-align: center; padding: 20px; color: #666;">No Excel or CSV files found in backup directory.</p>';
        return;
    }
    
    let html = '<div style="max-height: 400px; overflow-y: auto;">';
    files.forEach(file => {
        const sizeKB = (file.size / 1024).toFixed(2);
        const fileTypeLabel = file.type === 'csv' ? 'CSV' : 'Excel';
        html += '<div style="padding: 10px; margin: 5px 0; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9;">';
        html += '<div style="display: flex; justify-content: space-between; align-items: center;">';
        html += '<div>';
        html += '<strong>' + escapeHtml(file.filename) + '</strong> <span style="color: #667eea; font-size: 0.9em;">(' + fileTypeLabel + ')</span><br>';
        html += '<small style="color: #666;">Size: ' + sizeKB + ' KB | Modified: ' + escapeHtml(file.modified) + '</small>';
        html += '</div>';
        html += '<button type="button" class="btn-primary" onclick="importExcelFile(\'' + escapeHtml(file.filename) + '\')" style="margin-left: 10px;">Import</button>';
        html += '</div>';
        html += '</div>';
    });
    html += '</div>';
    container.innerHTML = html;
}

function importExcelFile(filename) {
    // First validate the Excel file
    validateExcelFile(filename);
}

function validateExcelFile(filename) {
    // Show loading
    const validationContent = document.getElementById('validationContent');
    validationContent.innerHTML = '<p style="text-align: center; padding: 20px;">Validating Excel file... Please wait.</p>';
    document.getElementById('excelValidationModal').style.display = 'block';
    document.getElementById('confirmImportBtn').style.display = 'none';
    
    // Validate the file
    fetch('api.php?action=validate_excel&filename=' + encodeURIComponent(filename))
        .then(response => response.json())
        .then(result => {
            displayValidationResult(result, filename);
        })
        .catch(error => {
            console.error('Error:', error);
            validationContent.innerHTML = '<div style="padding: 20px; background: #f8d7da; color: #721c24; border-radius: 5px;">' +
                '<strong>Error:</strong> Failed to validate Excel file. ' + error.message +
                '</div>';
        });
}

function displayValidationResult(result, filename) {
    const validationContent = document.getElementById('validationContent');
    const validationTitle = document.getElementById('validationTitle');
    const confirmBtn = document.getElementById('confirmImportBtn');
    
    let html = '';
    
    if (result.valid) {
        validationTitle.textContent = '✓ Excel File is OK to Import';
        html += '<div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 15px;">';
        html += '<strong>✓ File is valid and ready to import!</strong>';
        html += '</div>';
    } else {
        validationTitle.textContent = '✗ Excel File has ERRORS';
        html += '<div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 5px; margin-bottom: 15px;">';
        html += '<strong>✗ File has errors and cannot be imported!</strong>';
        html += '</div>';
    }
    
    // Statistics
    html += '<div style="margin-bottom: 15px;">';
    html += '<strong>File Statistics:</strong><br>';
    html += 'Total Rows: ' + (result.totalRows || 0) + '<br>';
    html += 'Valid Rows: ' + (result.validRows || 0) + '<br>';
    if (result.needsAutoCID > 0) {
        html += 'Rows needing Auto CID: ' + result.needsAutoCID + '<br>';
    }
    html += '</div>';
    
    // Preview
    if (result.preview && result.preview.length > 0) {
        html += '<div style="margin-bottom: 15px;">';
        html += '<strong>Preview (first 5 rows):</strong><br>';
        html += '<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">';
        html += '<thead><tr style="background: #667eea; color: white;"><th style="padding: 8px; text-align: left;">Row</th><th style="padding: 8px; text-align: left;">Customer ID</th><th style="padding: 8px; text-align: left;">Customer Name</th><th style="padding: 8px; text-align: left;">Company</th></tr></thead>';
        html += '<tbody>';
        result.preview.forEach(item => {
            html += '<tr style="border-bottom: 1px solid #ddd;">';
            html += '<td style="padding: 8px;">' + item.row + '</td>';
            html += '<td style="padding: 8px;">' + escapeHtml(item.customer_id) + '</td>';
            html += '<td style="padding: 8px;">' + escapeHtml(item.customer_name) + '</td>';
            html += '<td style="padding: 8px;">' + escapeHtml(item.company_name) + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
        html += '</div>';
    }
    
    // Errors
    if (result.errors && result.errors.length > 0) {
        html += '<div style="margin-bottom: 15px;">';
        html += '<strong style="color: #721c24;">Errors (' + result.errors.length + '):</strong><br>';
        html += '<div style="max-height: 200px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 10px;">';
        result.errors.forEach(error => {
            html += '<div style="padding: 5px; color: #721c24;">• ' + escapeHtml(error) + '</div>';
        });
        html += '</div>';
        html += '</div>';
    }
    
    // Warnings
    if (result.warnings && result.warnings.length > 0) {
        html += '<div style="margin-bottom: 15px;">';
        html += '<strong style="color: #856404;">Warnings (' + result.warnings.length + '):</strong><br>';
        html += '<div style="max-height: 150px; overflow-y: auto; background: #fff3cd; padding: 10px; border-radius: 5px; margin-top: 10px;">';
        result.warnings.forEach(warning => {
            html += '<div style="padding: 5px; color: #856404;">• ' + escapeHtml(warning) + '</div>';
        });
        html += '</div>';
        html += '</div>';
    }
    
    validationContent.innerHTML = html;
    
    // Show confirm button only if valid
    if (result.valid) {
        confirmBtn.style.display = 'inline-block';
        confirmBtn.onclick = function() {
            performImport(filename);
        };
    } else {
        confirmBtn.style.display = 'none';
    }
}

function performImport(filename) {
    if (!confirm('Import customers from ' + filename + '? This will add new rows to the database.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'import_excel');
    formData.append('filename', filename);
    
    // Show loading
    const validationContent = document.getElementById('validationContent');
    validationContent.innerHTML = '<p style="text-align: center; padding: 20px;">Importing... Please wait.</p>';
    
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(result => {
            document.getElementById('excelValidationModal').style.display = 'none';
            document.getElementById('importExcelModal').style.display = 'none';
            if (result.success) {
                showMessage('Success', result.message);
            } else {
                showMessage('Error', result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('excelValidationModal').style.display = 'none';
            showMessage('Error', 'An error occurred while importing Excel file');
        });
}

function showImportDb() {
    fetch('api.php?action=list_backup_files')
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const dbFiles = result.data.filter(f => f.type === 'database');
                displayImportDbFiles(dbFiles);
                document.getElementById('importDbModal').style.display = 'block';
            } else {
                showMessage('Error', result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error', 'An error occurred while fetching database files');
        });
}

function displayImportDbFiles(files) {
    const container = document.getElementById('dbFilesList');
    
    if (files.length === 0) {
        container.innerHTML = '<p style="text-align: center; padding: 20px; color: #666;">No database files found in backup directory.</p>';
        return;
    }
    
    let html = '<div style="max-height: 400px; overflow-y: auto;">';
    files.forEach(file => {
        const sizeKB = (file.size / 1024).toFixed(2);
        html += '<div style="padding: 10px; margin: 5px 0; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9;">';
        html += '<div style="display: flex; justify-content: space-between; align-items: center;">';
        html += '<div>';
        html += '<strong>' + escapeHtml(file.filename) + '</strong><br>';
        html += '<small style="color: #666;">Size: ' + sizeKB + ' KB | Modified: ' + escapeHtml(file.modified) + '</small>';
        html += '</div>';
        html += '<button type="button" class="btn-primary" onclick="importDbFile(\'' + escapeHtml(file.filename) + '\')" style="margin-left: 10px;">Import</button>';
        html += '</div>';
        html += '</div>';
    });
    html += '</div>';
    container.innerHTML = html;
}

function importDbFile(filename) {
    if (!confirm('Import customers from ' + filename + '? This will add new rows to the database.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'import_db');
    formData.append('filename', filename);
    
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showMessage('Success', result.message);
                document.getElementById('importDbModal').style.display = 'none';
            } else {
                showMessage('Error', result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Error', 'An error occurred while importing database');
        });
}

function displayAllCustomers(customers) {
    const container = document.getElementById('listAllContent');
    
    if (customers.length === 0) {
        container.innerHTML = '<p style="text-align: center; padding: 20px;">No customers found.</p>';
        return;
    }
    
    // Show summary
    let summaryHtml = '<div style="margin-bottom: 15px; padding: 10px; background: #e7f3ff; border-radius: 5px; border-left: 4px solid #667eea;">';
    summaryHtml += '<strong>Total Customers: ' + customers.length + '</strong> | ';
    summaryHtml += 'Click on any cell to edit. Changes save automatically when you click outside the field. Scroll horizontally if fields are hidden.';
    summaryHtml += '</div>';
    
    // Allowed values for dropdowns
    const allowedProductCategories = Array.isArray(window.allowedProductCategories) ? window.allowedProductCategories : [];
    const allowedCustomerTypes = Array.isArray(window.allowedCustomerTypes) ? window.allowedCustomerTypes : [];
    const allowedStatuses = Array.isArray(window.allowedStatuses) ? window.allowedStatuses : [];
    const allowedSources = Array.isArray(window.allowedSources) ? window.allowedSources : [];
    const statusColorMap = window.statusColors || {};

    // Define column headers with required order
    const columns = [
        { key: 'customer_id', label: 'Cust ID', editable: false, type: 'text' },
        { key: 'customer_name', label: 'Cust Name', editable: true, type: 'textarea' },
        { key: 'company_name', label: 'Company Name', editable: true, type: 'textarea' },
        { key: 'status', label: 'Status', editable: true, type: 'select', options: allowedStatuses },
        { key: 'requirement', label: 'Requirement', editable: true, type: 'textarea' },
        { key: 'comments', label: 'Comments', editable: true, type: 'textarea', callout: true },
        { key: 'primary_contact', label: 'Primary Contact', editable: true, type: 'text' },
        { key: 'email_id', label: 'Email ID', editable: true, type: 'textarea' },
        { key: 'product_category', label: 'Product Category', editable: true, type: 'select', options: allowedProductCategories },
        { key: 'customer_type', label: 'Customer Type', editable: true, type: 'select', options: allowedCustomerTypes },
        { key: 'source', label: 'Source', editable: true, type: 'select', options: allowedSources },
        { key: 'assigned_to', label: 'Assigned To', editable: true, type: 'text' },
        { key: 'city', label: 'City', editable: true, type: 'text' },
        { key: 'date', label: 'Date', editable: false, type: 'date' },
        { key: 'time', label: 'Time', editable: false, type: 'time' }
    ];
    
    // Build table HTML
    let tableHtml = '<div class="table-container-list-all">';
    tableHtml += '<table class="data-table list-all-table" id="listAllTable">';
    tableHtml += '<thead>';
    tableHtml += '<tr>';
    
    columns.forEach(col => {
        tableHtml += '<th>' + escapeHtml(col.label) + '</th>';
    });
    
    tableHtml += '</tr>';
    tableHtml += '</thead>';
    tableHtml += '<tbody>';
    
    customers.forEach(customer => {
        const statusValue = (customer.status || '').trim();
        const rowColor = statusColorMap[statusValue] || '';
        const textColor = rowColor ? getReadableTextColor(rowColor) : '';
        const rowStyle = rowColor ? ' style="background-color: ' + escapeHtml(rowColor) + '; color: ' + escapeHtml(textColor) + ';"' : '';
        const cellStyle = rowColor ? ' style="background-color: ' + escapeHtml(rowColor) + '; color: ' + escapeHtml(textColor) + ';"' : '';
        
        tableHtml += '<tr' + rowStyle + '>';
        columns.forEach(col => {
            let value = customer[col.key] || '';
            const customerId = customer.customer_id || '';
            const dateInfo = (customer.date || '') + (customer.time ? ' ' + customer.time : '');
            
            // Format date and time for display
            if (col.key === 'date' && value) {
                // Format date as YYYY-MM-DD if it's in a different format
                if (value.includes(' ')) {
                    value = value.split(' ')[0];
                }
            }
            if (col.key === 'time' && value) {
                // Format time as HH:MM if it's in a different format
                if (value.includes(' ')) {
                    value = value.split(' ')[1] || value;
                }
                // Remove seconds if present
                if (value.includes(':') && value.split(':').length > 2) {
                    const parts = value.split(':');
                    value = parts[0] + ':' + parts[1];
                }
            }
            
            tableHtml += '<td class="editable-cell"' + cellStyle + ' data-customer-id="' + escapeHtml(customerId) + '" data-field="' + escapeHtml(col.key) + '">';
            
            if (col.type === 'textarea') {
                const extraClass = col.key === 'comments' ? ' wide-cell' : (col.key === 'requirement' ? ' wide-cell' : '');
                tableHtml += '<textarea class="editable-field' + extraClass + '" rows="2">' + escapeHtml(value) + '</textarea>';
            } else if (col.type === 'select') {
                tableHtml += '<select class="editable-field">';
                tableHtml += '<option value="">Select</option>';
                (col.options || []).forEach(opt => {
                    const selected = value === opt ? ' selected' : '';
                    tableHtml += '<option value="' + escapeHtml(opt) + '"' + selected + '>' + escapeHtml(opt) + '</option>';
                });
                tableHtml += '</select>';
            } else if (!col.editable) {
                tableHtml += '<span class="non-editable-field">' + escapeHtml(value) + '</span>';
            } else {
                tableHtml += '<input type="text" class="editable-field" value="' + escapeHtml(value) + '">';
            }

            if (col.callout) {
                const latestComments = (customer.comments || '').trim() || 'No comments yet.';
                const commentsEscaped = escapeHtml(latestComments).replace(/\n/g, '<br>');
                tableHtml += '<span class="callout-dot" title="View latest comments"></span>';
                tableHtml += '<div class="callout-bubble">';
                tableHtml += '<div class="callout-title">Latest Comments</div>';
                tableHtml += '<div class="callout-meta">' + escapeHtml(dateInfo || 'No timestamp') + '</div>';
                tableHtml += '<div>' + commentsEscaped + '</div>';
                tableHtml += '</div>';
            }
            
            tableHtml += '</td>';
        });
        tableHtml += '</tr>';
    });
    
    tableHtml += '</tbody>';
    tableHtml += '</table>';
    tableHtml += '</div>';
    
    container.innerHTML = summaryHtml + tableHtml;
    
    // Add scroll position indicator and force table display properties
    const table = document.getElementById('listAllTable');
    if (table) {
        table.parentElement.scrollTop = 0;
        
        // Force table display properties as inline styles (fallback for production server)
        table.style.display = 'table';
        table.style.width = '100%';
        table.style.borderCollapse = 'collapse';
        table.style.tableLayout = 'auto';
        
        // Force thead, tbody, tr, th, td display properties
        const thead = table.querySelector('thead');
        if (thead) {
            thead.style.display = 'table-header-group';
        }
        
        const tbody = table.querySelector('tbody');
        if (tbody) {
            tbody.style.display = 'table-row-group';
        }
        
        const rows = table.querySelectorAll('tr');
        rows.forEach(row => {
            row.style.display = 'table-row';
        });
        
        const cells = table.querySelectorAll('th, td');
        cells.forEach(cell => {
            cell.style.display = 'table-cell';
        });
    }
    
    // Setup editable cell handlers
    setupEditableCells();
}

function setupEditableCells() {
    const cells = document.querySelectorAll('.editable-cell');
    cells.forEach(cell => {
        const field = cell.dataset.field;
        const customerId = cell.dataset.customerId;
        
        // Skip non-editable fields
        if (field === 'customer_id' || field === 'date' || field === 'time') {
            return;
        }
        
        const input = cell.querySelector('.editable-field');
        const callout = cell.querySelector('.callout-bubble');
        const showCallout = () => {
            if (callout) {
                cell.classList.add('show-callout');
            }
        };
        const hideCallout = () => {
            if (callout) {
                cell.classList.remove('show-callout');
            }
        };
        if (input) {
            const tagName = input.tagName.toUpperCase();
            const saveHandler = () => {
                const value = getInputValueForSave(input);
                if (customerId && field) {
                    saveCellValue(customerId, field, value);
                }
            };

            if (tagName === 'SELECT') {
                input.addEventListener('change', saveHandler);
                input.addEventListener('focus', showCallout);
                input.addEventListener('blur', hideCallout);
            } else {
                // Save on blur (when user clicks away)
                input.addEventListener('blur', function() {
                    saveHandler();
                    hideCallout();
                });
                
                // Save on Enter key (for text inputs only, not textareas)
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' && tagName !== 'TEXTAREA') {
                        e.preventDefault();
                        this.blur();
                    }
                });
                
                // For textareas, add Ctrl+Enter to save
                if (tagName === 'TEXTAREA') {
                    input.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' && e.ctrlKey) {
                            e.preventDefault();
                            this.blur();
                        }
                    });
                }
                input.addEventListener('focus', showCallout);
            }
            cell.addEventListener('mouseleave', hideCallout);
            const dot = cell.querySelector('.callout-dot');
            if (dot) {
                dot.addEventListener('mouseenter', showCallout);
                dot.addEventListener('mouseleave', hideCallout);
            }
        }
    });
}

function getInputValueForSave(input) {
    const tagName = input.tagName.toUpperCase();
    if (tagName === 'SELECT' && input.multiple) {
        const values = Array.from(input.selectedOptions).map(o => o.value.trim()).filter(Boolean);
        return values.join(', ');
    }
    return input.value.trim();
}

// Determine readable text color (black/white) based on background
function getReadableTextColor(hexColor) {
    if (!hexColor) return '#000';
    // Normalize hex
    let hex = hexColor.replace('#', '');
    if (hex.length === 3) {
        hex = hex.split('').map(c => c + c).join('');
    }
    if (hex.length !== 6) return '#000';
    const r = parseInt(hex.substr(0,2), 16);
    const g = parseInt(hex.substr(2,2), 16);
    const b = parseInt(hex.substr(4,2), 16);
    // YIQ formula
    const yiq = (r*299 + g*587 + b*114) / 1000;
    return yiq >= 128 ? '#000' : '#fff';
}

function saveCellValue(customerId, field, value) {
    if (!customerId || !field) {
        return;
    }
    
    // Find the cell to add visual feedback
    const cell = document.querySelector(`.editable-cell[data-customer-id="${customerId}"][data-field="${field}"]`);
    const input = cell ? cell.querySelector('.editable-field') : null;
    
    // Validate email if needed
    if (field === 'email_id' && value) {
        const emails = value.split('\n').map(e => e.trim()).filter(e => e);
        for (let email of emails) {
            if (!isValidEmail(email)) {
                if (input) {
                    input.style.borderColor = '#dc3545';
                    setTimeout(() => {
                        input.style.borderColor = '';
                    }, 2000);
                }
                showMessage('Validation Error', 'Invalid email address: ' + email);
                return;
            }
        }
    }
    
    // Show saving indicator
    if (input) {
        input.style.borderColor = '#ffc107';
        input.style.backgroundColor = '#fffbf0';
    }
    
    const formData = new FormData();
    formData.append('action', 'update_customer_field');
    formData.append('customer_id', customerId);
    formData.append('field', field);
    formData.append('value', value);
    
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (input) {
            if (result.success) {
                input.style.borderColor = '#28a745';
                input.style.backgroundColor = '#f0fff4';
                if (field === 'status') {
                    applyRowStatusColor(customerId, value);
                }
                setTimeout(() => {
                    input.style.borderColor = '';
                    input.style.backgroundColor = '';
                }, 1500);
            } else {
                input.style.borderColor = '#dc3545';
                input.style.backgroundColor = '#fff5f5';
                setTimeout(() => {
                    input.style.borderColor = '';
                    input.style.backgroundColor = '';
                }, 2000);
                showMessage('Error', result.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (input) {
            input.style.borderColor = '#dc3545';
            input.style.backgroundColor = '#fff5f5';
            setTimeout(() => {
                input.style.borderColor = '';
                input.style.backgroundColor = '';
            }, 2000);
        }
        showMessage('Error', 'An error occurred while saving');
    });
}

function applyRowStatusColor(customerId, statusValue) {
    const row = document.querySelector(`tr td[data-customer-id="${customerId}"]`)?.parentElement;
    if (!row) return;
    const map = window.statusColors || {};
    const color = map[statusValue] || '';
    if (color) {
        const textColor = getReadableTextColor(color);
        row.style.backgroundColor = color;
        row.style.color = textColor;
        row.querySelectorAll('td').forEach(td => {
            td.style.backgroundColor = color;
            td.style.color = textColor;
        });
    } else {
        row.style.backgroundColor = '';
        row.style.color = '';
        row.querySelectorAll('td').forEach(td => {
            td.style.backgroundColor = '';
            td.style.color = '';
        });
    }
}

function loadCustomerData(customerId) {
    fetch('api.php?action=get_customer&customer_id=' + encodeURIComponent(customerId))
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data) {
                const customer = result.data;
                
                // Populate form fields
                if (document.getElementById('date')) document.getElementById('date').value = customer.date || '';
                if (document.getElementById('time')) document.getElementById('time').value = customer.time || '';
                if (document.getElementById('customer_name')) document.getElementById('customer_name').value = customer.customer_name || '';
                if (document.getElementById('company_name')) document.getElementById('company_name').value = customer.company_name || '';
                if (document.getElementById('primary_contact')) document.getElementById('primary_contact').value = customer.primary_contact || '';
                if (document.getElementById('email_id')) document.getElementById('email_id').value = customer.email_id || '';
                if (document.getElementById('city')) document.getElementById('city').value = customer.city || '';
                if (document.getElementById('requirement')) document.getElementById('requirement').value = customer.requirement || '';
                if (document.getElementById('source')) document.getElementById('source').value = customer.source || '';
                if (document.getElementById('assigned_to')) document.getElementById('assigned_to').value = customer.assigned_to || '';
                if (document.getElementById('status')) document.getElementById('status').value = customer.status || '';
                if (document.getElementById('comments')) document.getElementById('comments').value = customer.comments || '';
                
                // Handle product categories (checkboxes)
                if (customer.product_category && document.getElementById('product_category_single')) {
                    document.getElementById('product_category_single').value = customer.product_category;
                }
                
                // Handle customer type (radio buttons)
                if (customer.customer_type) {
                    const radio = document.querySelector('input[name="customer_type"][value="' + customer.customer_type + '"]');
                    if (radio) radio.checked = true;
                }
                
                showMessage('Customer Loaded', 'Customer data loaded successfully. You can now update the information.');
            } else {
                showMessage('Not Found', 'Customer ID not found. Creating new customer entry.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Don't show error - just allow user to create new entry
        });
}

function performSearch() {
    const searchType = document.getElementById('searchType').value;
    const searchTerm = document.getElementById('searchInput').value.trim();
    
    if (!searchTerm) {
        showMessage('Search Error', 'Please enter a search term');
        return;
    }
    
    // Search functionality - placeholder for now
    showMessage('Search', 'Search functionality coming soon...');
}

// Admin Functions
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Activate button
    event.target.classList.add('active');
    
    // Load database stats if Database tab is selected
    if (tabName === 'database') {
        loadDatabaseStats();
    }
}

function handleCreateAgent(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('action', 'create_agent');
    
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('Success', result.message);
            e.target.reset();
            setTimeout(() => location.reload(), 1500);
        } else {
            showMessage('Error', result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred');
    });
}

function handleAddProductCategory(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('action', 'add_product_category');
    
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('Success', result.message);
            e.target.reset();
            setTimeout(() => location.reload(), 1500);
        } else {
            showMessage('Error', result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred');
    });
}

function deleteProductCategory(name) {
    // Legacy function - kept for compatibility
    console.warn('deleteProductCategory(name) is deprecated, use deleteProductCategoryById(id, name)');
}

function deleteProductCategoryById(id, name) {
    if (!confirm('Are you sure you want to delete product category "' + name + '"?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete_product_category');
    formData.append('id', id);
    
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('Success', result.message);
            // Remove the row from the table without reloading the page
            const row = document.querySelector(`button[data-id="${id}"]`)?.closest('tr');
            if (row) {
                row.remove();
            } else {
                // Fallback: reload if row not found
                setTimeout(() => location.reload(), 1500);
            }
            // Refresh database statistics if Database tab is visible
            const dbTab = document.getElementById('database-tab');
            if (dbTab && dbTab.classList.contains('active')) {
                loadDatabaseStats();
            }
        } else {
            showMessage('Error', result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred');
    });
}

function handleAddCustomerType(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('action', 'add_customer_type');
    
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('Success', result.message);
            e.target.reset();
            setTimeout(() => location.reload(), 1500);
        } else {
            showMessage('Error', result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred');
    });
}

function deleteCustomerType(name) {
    // Legacy function - kept for compatibility
    console.warn('deleteCustomerType(name) is deprecated, use deleteCustomerTypeById(id, name)');
}

function deleteCustomerTypeById(id, name) {
    if (!confirm('Are you sure you want to delete customer type "' + name + '"?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete_customer_type');
    formData.append('id', id);
    
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('Success', result.message);
            // Remove the row from the table without reloading the page
            const row = document.querySelector(`button[data-id="${id}"]`)?.closest('tr');
            if (row) {
                row.remove();
            } else {
                // Fallback: reload if row not found
                setTimeout(() => location.reload(), 1500);
            }
            // Refresh database statistics if Database tab is visible
            const dbTab = document.getElementById('database-tab');
            if (dbTab && dbTab.classList.contains('active')) {
                loadDatabaseStats();
            }
        } else {
            showMessage('Error', result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred');
    });
}

function handleAddStatus(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('action', 'add_status');
    
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('Success', result.message);
            e.target.reset();
            setTimeout(() => location.reload(), 1500);
        } else {
            showMessage('Error', result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred');
    });
}

function updateStatusColor(id, color) {
    const formData = new FormData();
    formData.append('action', 'update_status_color');
    formData.append('id', id);
    formData.append('color', color);
    
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (!result.success) {
            showMessage('Error', result.message || 'Failed to update color');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred');
    });
}

function handleAddSource(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('action', 'add_source');
    
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('Success', result.message);
            e.target.reset();
            setTimeout(() => location.reload(), 1500);
        } else {
            showMessage('Error', result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred');
    });
}

function deleteStatus(name) {
    // Legacy function - kept for compatibility
    console.warn('deleteStatus(name) is deprecated, use deleteStatusById(id, name)');
}

function deleteStatusById(id, name) {
    if (!confirm('Are you sure you want to delete status "' + name + '"?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete_status');
    formData.append('id', id);
    
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('Success', result.message);
            // Remove the row from the table without reloading the page
            const row = document.querySelector(`button[data-id="${id}"]`)?.closest('tr');
            if (row) {
                row.remove();
            } else {
                // Fallback: reload if row not found
                setTimeout(() => location.reload(), 1500);
            }
            // Refresh database statistics if Database tab is visible
            const dbTab = document.getElementById('database-tab');
            if (dbTab && dbTab.classList.contains('active')) {
                loadDatabaseStats();
            }
        } else {
            showMessage('Error', result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred');
    });
}

function deleteSourceById(id, name) {
    if (!confirm('Are you sure you want to delete inquiry source "' + name + '"?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete_source');
    formData.append('id', id);
    
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('Success', result.message);
            // Remove the row from the table without reloading the page
            const row = document.querySelector(`button[data-id="${id}"]`)?.closest('tr');
            if (row) {
                row.remove();
            } else {
                // Fallback: reload if row not found
                setTimeout(() => location.reload(), 1500);
            }
            // Refresh database statistics if Database tab is visible
            const dbTab = document.getElementById('database-tab');
            if (dbTab && dbTab.classList.contains('active')) {
                loadDatabaseStats();
            }
        } else {
            showMessage('Error', result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred');
    });
}

function deleteUser(id, username) {
    if (!confirm('Are you sure you want to delete user "' + username + '"?\n\nThis action cannot be undone.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete_user');
    formData.append('id', id);
    
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('Success', result.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showMessage('Error', result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred while deleting user');
    });
}

function clearDatabase() {
    // Double confirmation for safety
    const confirm1 = confirm('⚠️ WARNING: This will permanently delete ALL customer data from the database!\n\nThis action cannot be undone.\n\nAre you sure you want to proceed?');
    if (!confirm1) {
        return;
    }
    
    const confirm2 = confirm('⚠️ FINAL WARNING: You are about to delete all customer records!\n\nType "YES" in the next prompt to confirm, or click Cancel to abort.');
    if (!confirm2) {
        return;
    }
    
    const confirmText = prompt('Type "CLEAR DATABASE" (in all caps) to confirm deletion:');
    if (confirmText !== 'CLEAR DATABASE') {
        showMessage('Cancelled', 'Database clear operation cancelled. No data was deleted.');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'clear_database');
    
    fetch('api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showMessage('Success', result.message);
            // Reload stats after clearing
            setTimeout(() => {
                loadDatabaseStats();
            }, 1000);
        } else {
            showMessage('Error', result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Error', 'An error occurred while clearing the database');
    });
}

function loadDatabaseStats() {
    // Add cache-busting parameter to ensure fresh data
    const timestamp = new Date().getTime();
    fetch('api.php?action=get_database_stats&_t=' + timestamp)
        .then(response => response.json())
        .then(result => {
            const statsContainer = document.getElementById('databaseStats');
            if (result.success && result.data) {
                const stats = result.data;
                let html = '';
                
                // Database File Information
                html += '<div style="margin-bottom: 20px; padding: 15px; background: #e7f3ff; border-radius: 5px; border-left: 4px solid #2196F3;">';
                html += '<h4 style="margin-top: 0; color: #1976D2;">Database File</h4>';
                html += '<div style="font-family: monospace; color: #333;">';
                html += '<strong>Filename:</strong> ' + (stats.db_filename || 'crm.db') + '<br>';
                html += '<strong>Path:</strong> ' + (stats.db_path || 'data/crm.db');
                html += '</div>';
                html += '</div>';
                
                // Record Counts
                html += '<div style="margin-bottom: 20px;">';
                html += '<h4 style="color: #333; margin-bottom: 10px;">Record Counts</h4>';
                html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">';
                html += '<div style="padding: 15px; background: white; border-radius: 5px; border-left: 4px solid #667eea;">';
                html += '<div style="font-size: 24px; font-weight: bold; color: #667eea;">' + (stats.customers || 0) + '</div>';
                html += '<div style="color: #666; margin-top: 5px;">Customer Records</div>';
                html += '</div>';
                
                html += '<div style="padding: 15px; background: white; border-radius: 5px; border-left: 4px solid #28a745;">';
                html += '<div style="font-size: 24px; font-weight: bold; color: #28a745;">' + (stats.users || 0) + '</div>';
                html += '<div style="color: #666; margin-top: 5px;">Users</div>';
                html += '</div>';
                
                html += '<div style="padding: 15px; background: white; border-radius: 5px; border-left: 4px solid #ffc107;">';
                html += '<div style="font-size: 24px; font-weight: bold; color: #ffc107;">' + (stats.product_categories || 0) + '</div>';
                html += '<div style="color: #666; margin-top: 5px;">Product Categories</div>';
                html += '</div>';
                
                html += '<div style="padding: 15px; background: white; border-radius: 5px; border-left: 4px solid #17a2b8;">';
                html += '<div style="font-size: 24px; font-weight: bold; color: #17a2b8;">' + (stats.customer_types || 0) + '</div>';
                html += '<div style="color: #666; margin-top: 5px;">Customer Types</div>';
                html += '</div>';
                
                html += '<div style="padding: 15px; background: white; border-radius: 5px; border-left: 4px solid #9c27b0;">';
                html += '<div style="font-size: 24px; font-weight: bold; color: #9c27b0;">' + (stats.sources || 0) + '</div>';
                html += '<div style="color: #666; margin-top: 5px;">Inquiry Sources</div>';
                html += '</div>';
                
                html += '<div style="padding: 15px; background: white; border-radius: 5px; border-left: 4px solid #dc3545;">';
                html += '<div style="font-size: 24px; font-weight: bold; color: #dc3545;">' + (stats.statuses || 0) + '</div>';
                html += '<div style="color: #666; margin-top: 5px;">Status</div>';
                html += '</div>';
                
                html += '</div>';
                html += '</div>';
                
                // Table Field Names
                if (stats.table_fields) {
                    html += '<div style="margin-bottom: 20px;">';
                    html += '<h4 style="color: #333; margin-bottom: 10px;">Database Table Fields</h4>';
                    html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">';
                    
                    const tableNames = {
                        'customers': 'Customers',
                        'users': 'Users',
                        'product_categories': 'Product Categories',
                        'customer_types': 'Customer Types',
                        'sources': 'Inquiry Sources',
                        'statuses': 'Status'
                    };
                    
                    for (const [table, displayName] of Object.entries(tableNames)) {
                        if (stats.table_fields[table] && stats.table_fields[table].length > 0) {
                            html += '<div style="padding: 15px; background: white; border-radius: 5px; border: 1px solid #ddd;">';
                            html += '<strong style="color: #333; display: block; margin-bottom: 8px;">' + displayName + '</strong>';
                            html += '<div style="font-family: monospace; font-size: 12px; color: #666;">';
                            html += stats.table_fields[table].join(', ');
                            html += '</div>';
                            html += '</div>';
                        }
                    }
                    
                    html += '</div>';
                    html += '</div>';
                }
                
                // Backup Files
                if (stats.backup_files && stats.backup_files.length > 0) {
                    html += '<div style="margin-bottom: 20px;">';
                    html += '<h4 style="color: #333; margin-bottom: 10px;">Backup Files (' + stats.backup_files.length + ')</h4>';
                    html += '<div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px; background: white;">';
                    html += '<table style="width: 100%; border-collapse: collapse;">';
                    html += '<thead style="background: #f5f5f5; position: sticky; top: 0;">';
                    html += '<tr>';
                    html += '<th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Filename</th>';
                    html += '<th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Type</th>';
                    html += '<th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Size</th>';
                    html += '<th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Date</th>';
                    html += '</tr>';
                    html += '</thead>';
                    html += '<tbody>';
                    
                    stats.backup_files.forEach(file => {
                        html += '<tr style="border-bottom: 1px solid #eee;">';
                        html += '<td style="padding: 8px; font-family: monospace; font-size: 12px;">' + (file.filename || 'N/A') + '</td>';
                        html += '<td style="padding: 8px; color: #666;">' + (file.type || 'N/A') + '</td>';
                        html += '<td style="padding: 8px; color: #666;">' + (file.size || 'N/A') + '</td>';
                        html += '<td style="padding: 8px; color: #666;">' + (file.date || 'N/A') + '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody>';
                    html += '</table>';
                    html += '</div>';
                    html += '</div>';
                } else {
                    html += '<div style="margin-bottom: 20px; padding: 15px; background: #fff3cd; border-radius: 5px; border-left: 4px solid #ffc107;">';
                    html += '<h4 style="margin-top: 0; color: #856404;">Backup Files</h4>';
                    html += '<p style="color: #856404; margin: 0;">No backup files found.</p>';
                    html += '</div>';
                }
                
                statsContainer.innerHTML = html;
            } else {
                statsContainer.innerHTML = '<p style="color: #dc3545;">Failed to load database statistics: ' + (result.message || 'Unknown error') + '</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const statsContainer = document.getElementById('databaseStats');
            statsContainer.innerHTML = '<p style="color: #dc3545;">Error loading database statistics</p>';
        });
}

// Utility Functions
function showMessage(title, message) {
    const modal = document.getElementById('messageModal');
    document.getElementById('messageTitle').textContent = title;
    document.getElementById('messageText').textContent = message;
    modal.style.display = 'block';
}

function closeMessageModal() {
    document.getElementById('messageModal').style.display = 'none';
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

