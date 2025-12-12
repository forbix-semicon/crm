/**
 * Main JavaScript for CRM System - Agent Interface
 */

// Email validation (must contain @ and .)
function validateEmail(emailText) {
    if (!emailText || emailText.trim() === '') {
        return true; // Empty is allowed (handled by required attribute)
    }
    
    const emails = emailText.split('\n').map(e => e.trim()).filter(e => e);
    for (let email of emails) {
        if (email.indexOf('@') === -1 || email.indexOf('.') === -1) {
            return false;
        }
    }
    return true;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('crmForm');
    const emailField = document.getElementById('emailId');
    const emailError = document.getElementById('emailError');
    const cancelBtn = document.getElementById('cancelBtn');
    const addCommentBtn = document.getElementById('addCommentBtn');
    const saveCommentsBtn = document.getElementById('saveCommentsBtn');
    const listAllBtn = document.getElementById('listAllBtn');
    const backupBtn = document.getElementById('backupBtn');
    const exportBtn = document.getElementById('exportBtn');
    const importDbBtn = document.getElementById('importDbBtn');
    const importCsvBtn = document.getElementById('importCsvBtn');
    const shortReportBtn = document.getElementById('shortReportBtn');
    const expandedReportBtn = document.getElementById('expandedReportBtn');
    const searchBtn = document.getElementById('searchBtn');
    
    // Modal handling
    const listAllModal = document.getElementById('listAllModal');
    const importDbModal = document.getElementById('importDbModal');
    const importCsvModal = document.getElementById('importCsvModal');
    const importDbForm = document.getElementById('importDbForm');
    const importCsvForm = document.getElementById('importCsvForm');
    
    // Close modal when clicking X or outside
    document.querySelectorAll('.modal-close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });
    
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });
    
    // Email validation on blur
    if (emailField) {
        emailField.addEventListener('blur', function() {
            if (!validateEmail(this.value)) {
                emailError.textContent = 'Invalid email format. Email must contain @ and . symbols.';
                emailError.style.display = 'block';
                this.style.borderColor = '#dc3545';
            } else {
                emailError.textContent = '';
                emailError.style.display = 'none';
                this.style.borderColor = '#ddd';
            }
        });
        
        emailField.addEventListener('input', function() {
            if (validateEmail(this.value)) {
                emailError.textContent = '';
                emailError.style.display = 'none';
                this.style.borderColor = '#ddd';
            }
        });
    }
    
    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate email before submission
            if (!validateEmail(emailField.value)) {
                emailError.textContent = 'Invalid email format. Email must contain @ and . symbols.';
                emailError.style.display = 'block';
                emailField.focus();
                return;
            }
            
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            
            // Prepare form data
            const formData = new FormData(form);
            
            // Send AJAX request
            fetch(BASE_PATH + '/modules/form_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Data saved successfully!');
                    // Reset form or reload page
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving data.');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });
    }
    
    // Cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to clear all fields?')) {
                form.reset();
                // Reset customer ID to next available
                location.reload();
            }
        });
    }
    
    // Add comment button
    if (addCommentBtn) {
        addCommentBtn.addEventListener('click', function() {
            const container = document.getElementById('commentsContainer');
            const commentCount = container.children.length + 1;
            
            const commentRow = document.createElement('div');
            commentRow.className = 'comment-row';
            commentRow.innerHTML = `
                <label>Comment ${commentCount}:</label>
                <textarea name="comments[]" rows="2"></textarea>
                <button type="button" class="btn-remove-comment" onclick="removeComment(this)">Remove</button>
            `;
            
            container.appendChild(commentRow);
        });
    }
    
    // Save/Update Comments button
    if (saveCommentsBtn) {
        saveCommentsBtn.addEventListener('click', function() {
            const customerId = document.getElementById('customerId').value;
            const comments = [];
            const commentTextareas = document.querySelectorAll('#commentsContainer textarea[name="comments[]"]');
            
            commentTextareas.forEach(textarea => {
                const comment = textarea.value.trim();
                if (comment) {
                    comments.push(comment);
                }
            });
            
            if (comments.length === 0) {
                alert('Please enter at least one comment.');
                return;
            }
            
            this.disabled = true;
            this.textContent = 'Saving...';
            
            const formData = new FormData();
            formData.append('customer_id', customerId);
            comments.forEach((comment, index) => {
                formData.append('comments[]', comment);
            });
            
            fetch(BASE_PATH + '/modules/comments_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Comments saved successfully!');
                } else {
                    alert('Error: ' + data.message);
                }
                this.disabled = false;
                this.textContent = 'Save/Update Comments';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving comments.');
                this.disabled = false;
                this.textContent = 'Save/Update Comments';
            });
        });
    }
    
    // List All button
    if (listAllBtn) {
        listAllBtn.addEventListener('click', function() {
            listAllModal.style.display = 'block';
            loadCustomersTable();
        });
    }
    
    // Backup button (creates both CSV and DB)
    if (backupBtn) {
        backupBtn.addEventListener('click', function() {
            if (confirm('Create backup of all CRM data (CSV and Database)?')) {
                this.disabled = true;
                this.textContent = 'Creating Backup...';
                
                fetch(BASE_PATH + '/modules/backup_all.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Backup created successfully!\nCSV: ' + data.csv_file + '\nDatabase: ' + data.db_file);
                    } else {
                        alert('Backup failed: ' + data.message);
                    }
                    this.disabled = false;
                    this.textContent = 'Backup';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while creating backup.');
                    this.disabled = false;
                    this.textContent = 'Backup';
                });
            }
        });
    }
    
    // Export CSV button
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            if (confirm('Export all CRM data as CSV file?')) {
                // Create a form and submit it to trigger download
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = BASE_PATH + '/modules/export_csv.php';
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }
        });
    }
    
    // Import DB button
    if (importDbBtn) {
        importDbBtn.addEventListener('click', function() {
            if (importDbModal) {
                importDbModal.style.display = 'block';
            }
        });
    }
    
    // Import CSV button
    if (importCsvBtn) {
        importCsvBtn.addEventListener('click', function() {
            if (importCsvModal) {
                importCsvModal.style.display = 'block';
            }
        });
    }
    
    // Import DB form submission
    if (importDbForm) {
        importDbForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Importing...';
            
            fetch(BASE_PATH + '/modules/import_db.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Database imported successfully!\n' + data.message);
                    if (importDbModal) {
                        importDbModal.style.display = 'none';
                    }
                    importDbForm.reset();
                    // Reload page to reflect changes
                    location.reload();
                } else {
                    alert('Import failed: ' + data.message);
                }
                submitBtn.disabled = false;
                submitBtn.textContent = 'Import';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while importing database.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Import';
            });
        });
    }
    
    // Import CSV form submission
    if (importCsvForm) {
        importCsvForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Importing...';
            
            fetch(BASE_PATH + '/modules/import_csv.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let message = data.message;
                    if (data.errors && data.errors.length > 0) {
                        message += '\n\nSome errors occurred during import.';
                    }
                    alert(message);
                    if (importCsvModal) {
                        importCsvModal.style.display = 'none';
                    }
                    importCsvForm.reset();
                    // Reload page to reflect changes
                    location.reload();
                } else {
                    alert('Import failed: ' + data.message);
                }
                submitBtn.disabled = false;
                submitBtn.textContent = 'Import';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while importing CSV.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Import';
            });
        });
    }
    
    // Short Report button (empty for now)
    if (shortReportBtn) {
        shortReportBtn.addEventListener('click', function() {
            alert('Short Report feature coming soon!');
        });
    }
    
    // Expanded Report button (empty for now)
    if (expandedReportBtn) {
        expandedReportBtn.addEventListener('click', function() {
            alert('Expanded Report feature coming soon!');
        });
    }
    
    // Search button (empty for now)
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            const searchType = document.getElementById('searchType').value;
            const searchInput = document.getElementById('searchInput').value;
            
            if (!searchInput.trim()) {
                alert('Please enter a search term.');
                return;
            }
            
            alert('Search feature coming soon!\nSearch Type: ' + searchType + '\nSearch Term: ' + searchInput);
        });
    }
});

// Remove comment function
function removeComment(button) {
    const commentRow = button.closest('.comment-row');
    const container = document.getElementById('commentsContainer');
    
    if (container.children.length > 1) {
        commentRow.remove();
        // Renumber comments
        const comments = container.querySelectorAll('.comment-row');
        comments.forEach((row, index) => {
            row.querySelector('label').textContent = 'Comment ' + (index + 1) + ':';
        });
    } else {
        alert('At least one comment field is required.');
    }
}

// Global variable to store field options
let fieldOptions = {};

// Load field options
function loadFieldOptions() {
    return fetch(BASE_PATH + '/modules/get_field_options.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fieldOptions = data.options;
                return fieldOptions;
            }
            return {};
        })
        .catch(error => {
            console.error('Error loading field options:', error);
            return {};
        });
}

// Load customers table
function loadCustomersTable() {
    const tableHead = document.getElementById('tableHead');
    const tableBody = document.getElementById('tableBody');
    
    tableHead.innerHTML = '';
    tableBody.innerHTML = '<tr><td colspan="20" style="text-align: center; padding: 20px;">Loading...</td></tr>';
    
    // Load field options first, then load customers
    loadFieldOptions().then(() => {
        fetch(BASE_PATH + '/modules/list_all.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.customers.length > 0) {
                // Define columns
                const columns = [
                    'customer_id', 'date', 'time', 'customer_name', 'company_name',
                    'primary_contact', 'secondary_contact', 'email_id', 'city',
                    'product_category', 'requirement', 'source', 'assigned_to',
                    'customer_type', 'status'
                ];
                
                // Create header
                const headerRow = document.createElement('tr');
                columns.forEach(col => {
                    const th = document.createElement('th');
                    th.textContent = col.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    headerRow.appendChild(th);
                });
                // Add comments column
                const commentsTh = document.createElement('th');
                commentsTh.textContent = 'Comments';
                headerRow.appendChild(commentsTh);
                tableHead.appendChild(headerRow);
                
                // Create rows
                tableBody.innerHTML = '';
                data.customers.forEach(customer => {
                    const row = document.createElement('tr');
                    
                    columns.forEach(col => {
                        const td = document.createElement('td');
                        if (col !== 'customer_id') {
                            td.className = 'editable-cell';
                        } else {
                            td.className = 'readonly-cell';
                            td.style.backgroundColor = '#f0f0f0';
                            td.style.cursor = 'default';
                        }
                        td.setAttribute('data-field', col);
                        td.setAttribute('data-customer-id', customer.customer_id);
                        
                        const content = document.createElement('div');
                        content.className = 'cell-content';
                        content.textContent = customer[col] || '';
                        td.appendChild(content);
                        
                        row.appendChild(td);
                    });
                    
                    // Comments column
                    const commentsTd = document.createElement('td');
                    commentsTd.className = 'editable-cell';
                    commentsTd.setAttribute('data-field', 'comments');
                    commentsTd.setAttribute('data-customer-id', customer.customer_id);
                    const commentsContent = document.createElement('div');
                    commentsContent.className = 'cell-content';
                    commentsContent.textContent = (customer.comments || []).join('\n');
                    commentsTd.appendChild(commentsContent);
                    row.appendChild(commentsTd);
                    
                    tableBody.appendChild(row);
                });
                
                // Add click handlers for editing (skip customer_id)
                document.querySelectorAll('.editable-cell').forEach(cell => {
                    const field = cell.getAttribute('data-field');
                    if (field !== 'customer_id') {
                        cell.addEventListener('click', function() {
                            if (!this.classList.contains('editing')) {
                                editCell(this);
                            }
                        });
                    } else {
                        cell.style.cursor = 'default';
                        cell.style.backgroundColor = '#f0f0f0';
                    }
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="20" style="text-align: center; padding: 20px;">No customers found.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading customers:', error);
            tableBody.innerHTML = '<tr><td colspan="20" style="text-align: center; padding: 20px; color: red;">Error loading data.</td></tr>';
        });
    });
}

// Edit cell function
function editCell(cell) {
    const field = cell.getAttribute('data-field');
    const customerId = cell.getAttribute('data-customer-id');
    const currentValue = cell.querySelector('.cell-content').textContent;
    
    cell.classList.add('editing');
    
    let input;
    const container = document.createElement('div');
    container.style.width = '100%';
    
    // Handle product_category as checkboxes (multiple selection)
    if (field === 'product_category') {
        const currentValues = currentValue ? currentValue.split(', ').map(v => v.trim()) : [];
        const options = fieldOptions.product_category || [];
        
        options.forEach(option => {
            const label = document.createElement('label');
            label.style.display = 'block';
            label.style.marginBottom = '5px';
            label.style.cursor = 'pointer';
            
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.value = option;
            checkbox.checked = currentValues.includes(option);
            checkbox.style.marginRight = '8px';
            
            const span = document.createElement('span');
            span.textContent = option;
            
            label.appendChild(checkbox);
            label.appendChild(span);
            container.appendChild(label);
        });
        
        input = container; // Store container as input for value extraction
        input.getSelectedValues = function() {
            const checked = Array.from(container.querySelectorAll('input[type="checkbox"]:checked'));
            return checked.map(cb => cb.value).join(', ');
        };
    }
    // Handle customer_type, status, source, assigned_to as dropdowns (single selection)
    else if (field === 'customer_type' || field === 'status' || field === 'source' || field === 'assigned_to') {
        input = document.createElement('select');
        const options = fieldOptions[field] || [];
        
        // Add empty option
        const emptyOption = document.createElement('option');
        emptyOption.value = '';
        emptyOption.textContent = 'Select...';
        input.appendChild(emptyOption);
        
        options.forEach(option => {
            const opt = document.createElement('option');
            opt.value = option;
            opt.textContent = option;
            opt.selected = option === currentValue;
            input.appendChild(opt);
        });
    }
    // Handle date and time fields
    else if (field === 'date') {
        input = document.createElement('input');
        input.type = 'date';
        input.value = currentValue;
    }
    else if (field === 'time') {
        input = document.createElement('input');
        input.type = 'time';
        input.value = currentValue;
    }
    // Handle textarea fields
    else if (field === 'comments' || field === 'requirement' || field === 'customer_name' || field === 'company_name' || field === 'email_id' || field === 'city' || field === 'primary_contact' || field === 'secondary_contact') {
        input = document.createElement('textarea');
        input.value = currentValue;
        input.rows = 3;
        input.style.width = '100%';
    }
    // Default to text input
    else {
        input = document.createElement('input');
        input.type = 'text';
        input.value = currentValue;
    }
    
    const actions = document.createElement('div');
    actions.className = 'cell-actions';
    
    const saveBtn = document.createElement('button');
    saveBtn.className = 'btn-save';
    saveBtn.textContent = '✓';
    saveBtn.onclick = () => {
        let value;
        if (field === 'product_category' && input.getSelectedValues) {
            value = input.getSelectedValues();
        } else {
            value = input.value;
        }
        saveCell(cell, field, customerId, value);
    };
    
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'btn-cancel';
    cancelBtn.textContent = '✕';
    cancelBtn.onclick = () => cancelEdit(cell, currentValue);
    
    actions.appendChild(saveBtn);
    actions.appendChild(cancelBtn);
    
    cell.innerHTML = '';
    if (field === 'product_category') {
        cell.appendChild(container);
    } else {
        cell.appendChild(input);
    }
    cell.appendChild(actions);
    
    if (input.focus) {
        input.focus();
    }
}

// Save cell function
function saveCell(cell, field, customerId, value) {
    const formData = new FormData();
    formData.append('customer_id', customerId);
    formData.append('field', field);
    formData.append('value', value);
    
    fetch(BASE_PATH + '/modules/update_customer.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cell.classList.remove('editing');
            const content = document.createElement('div');
            content.className = 'cell-content';
            content.textContent = value;
            cell.innerHTML = '';
            cell.appendChild(content);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving.');
    });
}

// Cancel edit function
function cancelEdit(cell, originalValue) {
    cell.classList.remove('editing');
    const content = document.createElement('div');
    content.className = 'cell-content';
    content.textContent = originalValue;
    cell.innerHTML = '';
    cell.appendChild(content);
}
