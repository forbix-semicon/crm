/**
 * Main JavaScript for CRM System
 */

// Email validation
function validateEmail(emailText) {
    if (!emailText || emailText.trim() === '') {
        return true; // Empty is allowed (handled by required attribute)
    }
    
    const emails = emailText.split('\n').map(e => e.trim()).filter(e => e);
    for (let email of emails) {
        if (email.indexOf('@') === -1) {
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
    const backupBtn = document.getElementById('backupBtn');
    const shortReportBtn = document.getElementById('shortReportBtn');
    const expandedReportBtn = document.getElementById('expandedReportBtn');
    const searchBtn = document.getElementById('searchBtn');
    
    // Email validation on blur
    if (emailField) {
        emailField.addEventListener('blur', function() {
            if (!validateEmail(this.value)) {
                emailError.textContent = 'Invalid email format. Email must contain @ symbol.';
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
                emailError.textContent = 'Invalid email format. Email must contain @ symbol.';
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
    
    // Backup button
    if (backupBtn) {
        backupBtn.addEventListener('click', function() {
            if (confirm('Create backup of all CRM data?')) {
                this.disabled = true;
                this.textContent = 'Creating Backup...';
                
                fetch(BASE_PATH + '/modules/backup.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Backup created successfully!\nFilename: ' + data.filename);
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
