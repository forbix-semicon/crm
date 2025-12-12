/**
 * Admin Panel JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and contents
            tabButtons.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            document.getElementById(targetTab + '-tab').classList.add('active');
            
            // Load data for the active tab
            loadTabData(targetTab);
        });
    });
    
    // Load initial tab data
    loadTabData('agents');
    
    // Agent form handling
    setupAgentForm();
    
    // Category form handling
    setupCategoryForm();
    
    // Type form handling
    setupTypeForm();
    
    // Status form handling
    setupStatusForm();
});

function loadTabData(tab) {
    switch(tab) {
        case 'agents':
            loadAgents();
            break;
        case 'categories':
            loadCategories();
            break;
        case 'types':
            loadTypes();
            break;
        case 'statuses':
            loadStatuses();
            break;
    }
}

function loadAgents() {
    fetch(BASE_PATH + '/modules/admin_agents.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAgents(data.agents);
            }
        })
        .catch(error => console.error('Error loading agents:', error));
}

function displayAgents(agents) {
    const container = document.getElementById('agentsList');
    if (agents.length === 0) {
        container.innerHTML = '<div class="empty-state"><p>No agents found. Create one above.</p></div>';
        return;
    }
    
    container.innerHTML = agents.map(agent => `
        <div class="admin-list-item">
            <div class="item-info">
                <strong>${escapeHtml(agent.username)}</strong>
                <small>Created: ${new Date(agent.created_at).toLocaleDateString()}</small>
            </div>
            <div class="item-actions">
                <button class="btn btn-edit" onclick="editAgent(${agent.id}, '${escapeHtml(agent.username)}')">Edit</button>
                <button class="btn btn-danger" onclick="deleteAgent(${agent.id}, '${escapeHtml(agent.username)}')">Delete</button>
            </div>
        </div>
    `).join('');
}

function setupAgentForm() {
    const form = document.getElementById('agentForm');
    const submitBtn = document.getElementById('agentSubmitBtn');
    const cancelBtn = document.getElementById('agentCancelBtn');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        formData.append('action', document.getElementById('agentId').value ? 'update' : 'create');
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
        
        fetch(BASE_PATH + '/modules/admin_agents.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                form.reset();
                document.getElementById('agentId').value = '';
                submitBtn.textContent = 'Create Agent';
                cancelBtn.style.display = 'none';
                loadAgents();
            } else {
                alert('Error: ' + data.message);
            }
            submitBtn.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
            submitBtn.disabled = false;
        });
    });
    
    cancelBtn.addEventListener('click', function() {
        form.reset();
        document.getElementById('agentId').value = '';
        submitBtn.textContent = 'Create Agent';
        this.style.display = 'none';
    });
}

function editAgent(id, username) {
    document.getElementById('agentId').value = id;
    document.getElementById('agentUsername').value = username;
    document.getElementById('agentPassword').value = '';
    document.getElementById('agentPassword').required = false;
    document.getElementById('agentSubmitBtn').textContent = 'Update Agent';
    document.getElementById('agentCancelBtn').style.display = 'inline-block';
}

function deleteAgent(id, username) {
    if (confirm(`Are you sure you want to delete agent "${username}"?`)) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        
        fetch(BASE_PATH + '/modules/admin_agents.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                loadAgents();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    }
}

function loadCategories() {
    fetch(BASE_PATH + '/modules/admin_categories.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayCategories(data.categories);
            }
        })
        .catch(error => console.error('Error loading categories:', error));
}

function displayCategories(categories) {
    const container = document.getElementById('categoriesList');
    if (categories.length === 0) {
        container.innerHTML = '<div class="empty-state"><p>No categories found. Add one above.</p></div>';
        return;
    }
    
    container.innerHTML = categories.map(cat => `
        <div class="admin-list-item">
            <div class="item-info">
                <strong>${escapeHtml(cat.name)}</strong>
            </div>
            <div class="item-actions">
                <button class="btn btn-edit" onclick="editCategory(${cat.id}, '${escapeHtml(cat.name)}')">Edit</button>
                <button class="btn btn-danger" onclick="deleteCategory(${cat.id}, '${escapeHtml(cat.name)}')">Delete</button>
            </div>
        </div>
    `).join('');
}

function setupCategoryForm() {
    const form = document.getElementById('categoryForm');
    const submitBtn = document.getElementById('categorySubmitBtn');
    const cancelBtn = document.getElementById('categoryCancelBtn');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        formData.append('action', document.getElementById('categoryId').value ? 'update' : 'create');
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
        
        fetch(BASE_PATH + '/modules/admin_categories.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                form.reset();
                document.getElementById('categoryId').value = '';
                submitBtn.textContent = 'Add Category';
                cancelBtn.style.display = 'none';
                loadCategories();
            } else {
                alert('Error: ' + data.message);
            }
            submitBtn.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
            submitBtn.disabled = false;
        });
    });
    
    cancelBtn.addEventListener('click', function() {
        form.reset();
        document.getElementById('categoryId').value = '';
        submitBtn.textContent = 'Add Category';
        this.style.display = 'none';
    });
}

function editCategory(id, name) {
    document.getElementById('categoryId').value = id;
    document.getElementById('categoryName').value = name;
    document.getElementById('categorySubmitBtn').textContent = 'Update Category';
    document.getElementById('categoryCancelBtn').style.display = 'inline-block';
}

function deleteCategory(id, name) {
    if (confirm(`Are you sure you want to delete category "${name}"?`)) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        
        fetch(BASE_PATH + '/modules/admin_categories.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                loadCategories();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    }
}

function loadTypes() {
    fetch(BASE_PATH + '/modules/admin_types.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTypes(data.types);
            }
        })
        .catch(error => console.error('Error loading types:', error));
}

function displayTypes(types) {
    const container = document.getElementById('typesList');
    if (types.length === 0) {
        container.innerHTML = '<div class="empty-state"><p>No types found. Add one above.</p></div>';
        return;
    }
    
    container.innerHTML = types.map(type => `
        <div class="admin-list-item">
            <div class="item-info">
                <strong>${escapeHtml(type.name)}</strong>
            </div>
            <div class="item-actions">
                <button class="btn btn-edit" onclick="editType(${type.id}, '${escapeHtml(type.name)}')">Edit</button>
                <button class="btn btn-danger" onclick="deleteType(${type.id}, '${escapeHtml(type.name)}')">Delete</button>
            </div>
        </div>
    `).join('');
}

function setupTypeForm() {
    const form = document.getElementById('typeForm');
    const submitBtn = document.getElementById('typeSubmitBtn');
    const cancelBtn = document.getElementById('typeCancelBtn');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        formData.append('action', document.getElementById('typeId').value ? 'update' : 'create');
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
        
        fetch(BASE_PATH + '/modules/admin_types.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                form.reset();
                document.getElementById('typeId').value = '';
                submitBtn.textContent = 'Add Type';
                cancelBtn.style.display = 'none';
                loadTypes();
            } else {
                alert('Error: ' + data.message);
            }
            submitBtn.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
            submitBtn.disabled = false;
        });
    });
    
    cancelBtn.addEventListener('click', function() {
        form.reset();
        document.getElementById('typeId').value = '';
        submitBtn.textContent = 'Add Type';
        this.style.display = 'none';
    });
}

function editType(id, name) {
    document.getElementById('typeId').value = id;
    document.getElementById('typeName').value = name;
    document.getElementById('typeSubmitBtn').textContent = 'Update Type';
    document.getElementById('typeCancelBtn').style.display = 'inline-block';
}

function deleteType(id, name) {
    if (confirm(`Are you sure you want to delete type "${name}"?`)) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        
        fetch(BASE_PATH + '/modules/admin_types.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                loadTypes();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    }
}

function loadStatuses() {
    fetch(BASE_PATH + '/modules/admin_statuses.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayStatuses(data.statuses);
            }
        })
        .catch(error => console.error('Error loading statuses:', error));
}

function displayStatuses(statuses) {
    const container = document.getElementById('statusesList');
    if (statuses.length === 0) {
        container.innerHTML = '<div class="empty-state"><p>No statuses found. Add one above.</p></div>';
        return;
    }
    
    container.innerHTML = statuses.map(status => `
        <div class="admin-list-item">
            <div class="item-info">
                <strong>${escapeHtml(status.name)}</strong>
            </div>
            <div class="item-actions">
                <button class="btn btn-edit" onclick="editStatus(${status.id}, '${escapeHtml(status.name)}')">Edit</button>
                <button class="btn btn-danger" onclick="deleteStatus(${status.id}, '${escapeHtml(status.name)}')">Delete</button>
            </div>
        </div>
    `).join('');
}

function setupStatusForm() {
    const form = document.getElementById('statusForm');
    const submitBtn = document.getElementById('statusSubmitBtn');
    const cancelBtn = document.getElementById('statusCancelBtn');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        formData.append('action', document.getElementById('statusId').value ? 'update' : 'create');
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
        
        fetch(BASE_PATH + '/modules/admin_statuses.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                form.reset();
                document.getElementById('statusId').value = '';
                submitBtn.textContent = 'Add Status';
                cancelBtn.style.display = 'none';
                loadStatuses();
            } else {
                alert('Error: ' + data.message);
            }
            submitBtn.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
            submitBtn.disabled = false;
        });
    });
    
    cancelBtn.addEventListener('click', function() {
        form.reset();
        document.getElementById('statusId').value = '';
        submitBtn.textContent = 'Add Status';
        this.style.display = 'none';
    });
}

function editStatus(id, name) {
    document.getElementById('statusId').value = id;
    document.getElementById('statusName').value = name;
    document.getElementById('statusSubmitBtn').textContent = 'Update Status';
    document.getElementById('statusCancelBtn').style.display = 'inline-block';
}

function deleteStatus(id, name) {
    if (confirm(`Are you sure you want to delete status "${name}"?`)) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        
        fetch(BASE_PATH + '/modules/admin_statuses.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                loadStatuses();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}


