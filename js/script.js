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
        if (key === 'product_category[]') {
            if (!data.product_category) data.product_category = [];
            data.product_category.push(value);
        } else {
            data[key] = value;
        }
    }
    
    // Convert product_category array to comma-separated string
    if (data.product_category) {
        data.product_category = data.product_category.join(', ');
    }
    
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

function displayAllCustomers(customers) {
    const container = document.getElementById('listAllContent');
    
    if (customers.length === 0) {
        container.innerHTML = '<p style="text-align: center; padding: 20px;">No customers found.</p>';
        return;
    }
    
    // Show summary
    let summaryHtml = '<div style="margin-bottom: 15px; padding: 10px; background: #e7f3ff; border-radius: 5px; border-left: 4px solid #667eea;">';
    summaryHtml += '<strong>Total Customers: ' + customers.length + '</strong> | ';
    summaryHtml += 'Click on any cell to edit. Changes save automatically when you click outside the field.';
    summaryHtml += '</div>';
    
    // Define column headers with better labels
    const columns = [
        { key: 'customer_id', label: 'Customer ID', editable: false, type: 'text' },
        { key: 'date', label: 'Date', editable: false, type: 'date' },
        { key: 'time', label: 'Time', editable: false, type: 'time' },
        { key: 'customer_name', label: 'Customer Name', editable: true, type: 'textarea' },
        { key: 'company_name', label: 'Company Name', editable: true, type: 'textarea' },
        { key: 'primary_contact', label: 'Primary Contact', editable: true, type: 'text' },
        { key: 'secondary_contact', label: 'Secondary Contact', editable: true, type: 'text' },
        { key: 'email_id', label: 'Email ID', editable: true, type: 'textarea' },
        { key: 'city', label: 'City', editable: true, type: 'text' },
        { key: 'product_category', label: 'Product Category', editable: true, type: 'text' },
        { key: 'requirement', label: 'Requirement', editable: true, type: 'textarea' },
        { key: 'source', label: 'Source', editable: true, type: 'text' },
        { key: 'assigned_to', label: 'Assigned To', editable: true, type: 'text' },
        { key: 'customer_type', label: 'Customer Type', editable: true, type: 'text' },
        { key: 'status', label: 'Status', editable: true, type: 'text' },
        { key: 'comments', label: 'Comments', editable: true, type: 'textarea' }
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
        tableHtml += '<tr>';
        columns.forEach(col => {
            let value = customer[col.key] || '';
            const customerId = customer.customer_id || '';
            
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
            
            tableHtml += '<td class="editable-cell" data-customer-id="' + escapeHtml(customerId) + '" data-field="' + escapeHtml(col.key) + '">';
            
            if (col.type === 'textarea') {
                tableHtml += '<textarea class="editable-field" rows="2">' + escapeHtml(value) + '</textarea>';
            } else if (!col.editable) {
                tableHtml += '<span class="non-editable-field">' + escapeHtml(value) + '</span>';
            } else {
                tableHtml += '<input type="text" class="editable-field" value="' + escapeHtml(value) + '">';
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
        if (input) {
            // Save on blur (when user clicks away)
            input.addEventListener('blur', function() {
                const value = this.value.trim();
                if (customerId && field) {
                    saveCellValue(customerId, field, value);
                }
            });
            
            // Save on Enter key (for text inputs only, not textareas)
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && this.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    this.blur();
                }
            });
            
            // For textareas, add Ctrl+Enter to save
            if (input.tagName === 'TEXTAREA') {
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && e.ctrlKey) {
                        e.preventDefault();
                        this.blur();
                    }
                });
            }
        }
    });
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
                if (document.getElementById('secondary_contact')) document.getElementById('secondary_contact').value = customer.secondary_contact || '';
                if (document.getElementById('email_id')) document.getElementById('email_id').value = customer.email_id || '';
                if (document.getElementById('city')) document.getElementById('city').value = customer.city || '';
                if (document.getElementById('requirement')) document.getElementById('requirement').value = customer.requirement || '';
                if (document.getElementById('source')) document.getElementById('source').value = customer.source || '';
                if (document.getElementById('assigned_to')) document.getElementById('assigned_to').value = customer.assigned_to || '';
                if (document.getElementById('status')) document.getElementById('status').value = customer.status || '';
                if (document.getElementById('comments')) document.getElementById('comments').value = customer.comments || '';
                
                // Handle product categories (checkboxes)
                if (customer.product_category) {
                    const categories = customer.product_category.split(',').map(c => c.trim());
                    document.querySelectorAll('input[name="product_category[]"]').forEach(checkbox => {
                        checkbox.checked = categories.includes(checkbox.value);
                    });
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

function deleteProductCategory(id) {
    if (!confirm('Are you sure you want to delete this product category?')) {
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

function deleteCustomerType(id) {
    if (!confirm('Are you sure you want to delete this customer type?')) {
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

function deleteStatus(id) {
    if (!confirm('Are you sure you want to delete this status?')) {
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

