/**
 * Godspeed Inventory Management App JS
 * Enhanced version with modern features and better UX
 */

// Configuration
const CONFIG = {
    LOW_STOCK_THRESHOLD: 5,
    API_ENDPOINTS: {
        ITEMS: 'items_api.php',
        AUTH: 'auth_api.php'
    },
    ANIMATION_DURATION: 300
};

// Utility Functions
const Utils = {
    showLoading: () => {
        const spinner = document.getElementById('loading-spinner');
        if (spinner) spinner.style.display = 'block';
    },

    hideLoading: () => {
        const spinner = document.getElementById('loading-spinner');
        if (spinner) spinner.style.display = 'none';
    },

    showAlert: (message, type = 'info') => {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.container-fluid') || document.body;
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    },

    formatNumber: (num) => {
        return new Intl.NumberFormat().format(num);
    },

    debounce: (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// API Service
const API = {
    async request(endpoint, options = {}) {
        try {
            Utils.showLoading();
            const response = await fetch(endpoint, {
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                ...options
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API request failed:', error);
            Utils.showAlert(`Request failed: ${error.message}`, 'danger');
            throw error;
        } finally {
            Utils.hideLoading();
        }
    },

    async getItems() {
        return await this.request(CONFIG.API_ENDPOINTS.ITEMS, { method: 'GET' });
    },

    async addItem(itemData) {
        return await this.request(CONFIG.API_ENDPOINTS.ITEMS, {
            method: 'POST',
            body: JSON.stringify({ action: 'add', ...itemData })
        });
    },

    async updateItem(itemData) {
        return await this.request(CONFIG.API_ENDPOINTS.ITEMS, {
            method: 'POST',
            body: JSON.stringify({ action: 'update', ...itemData })
        });
    },

    async deleteItem(id) {
        return await this.request(CONFIG.API_ENDPOINTS.ITEMS, {
            method: 'POST',
            body: JSON.stringify({ action: 'delete', id })
        });
    }
};

// Dashboard Functions
async function renderDashboard() {
    try {
        const items = await API.getItems();
        renderSummaryCards(items);
        renderDashboardSummary(items);
    } catch (error) {
        console.error('Failed to render dashboard:', error);
    }
}

function renderSummaryCards(items) {
    const summaryCardsContainer = document.getElementById('summary-cards');
    if (!summaryCardsContainer) return;

    const totalItems = items.length;
    const totalQuantity = items.reduce((sum, item) => sum + Number(item.quantity), 0);
    const lowStockItems = items.filter(item => 
        Number(item.quantity) > 0 && Number(item.quantity) < CONFIG.LOW_STOCK_THRESHOLD
    );
    const outOfStockItems = items.filter(item => Number(item.quantity) === 0);

    const cardsHTML = `
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="card-value">${Utils.formatNumber(totalItems)}</div>
                        <div class="card-label">Total Items</div>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="card-value">${Utils.formatNumber(totalQuantity)}</div>
                        <div class="card-label">Total Quantity</div>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card ${lowStockItems.length > 0 ? 'warning' : 'success'}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="card-value">${lowStockItems.length}</div>
                        <div class="card-label">Low Stock Items</div>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card ${outOfStockItems.length > 0 ? 'danger' : 'success'}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="card-value">${outOfStockItems.length}</div>
                        <div class="card-label">Out of Stock</div>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    `;

    summaryCardsContainer.innerHTML = cardsHTML;
}

function renderDashboardSummary(items) {
    const summaryDiv = document.getElementById('dashboard-summary');
    if (!summaryDiv) return;

    const totalItems = items.length;
    const totalQuantity = items.reduce((sum, item) => sum + Number(item.quantity), 0);
    const lowStockItems = items.filter(item => 
        Number(item.quantity) > 0 && Number(item.quantity) < CONFIG.LOW_STOCK_THRESHOLD
    );
    const outOfStockItems = items.filter(item => Number(item.quantity) === 0);

    // Determine overall status
    let statusClass = 'success';
    let statusText = 'Stock Level OK';
    let statusIcon = 'fas fa-check-circle';

    if (outOfStockItems.length > 0) {
        statusClass = 'danger';
        statusText = 'Out of Stock Items Present';
        statusIcon = 'fas fa-times-circle';
    } else if (lowStockItems.length > 0) {
        statusClass = 'warning';
        statusText = 'Low Stock Warning';
        statusIcon = 'fas fa-exclamation-triangle';
    }

    let lowStockHTML = '';
    if (lowStockItems.length > 0) {
        lowStockHTML = `
            <div class="mt-4">
                <h6 class="text-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>Low Stock Items
                </h6>
                <div class="row">
                    ${lowStockItems.map(item => `
                        <div class="col-md-6 mb-2">
                            <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                <span class="fw-medium">${item.name}</span>
                                <span class="badge bg-warning text-dark">${item.quantity} left</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    let outOfStockHTML = '';
    if (outOfStockItems.length > 0) {
        outOfStockHTML = `
            <div class="mt-4">
                <h6 class="text-danger">
                    <i class="fas fa-times-circle me-2"></i>Out of Stock Items
                </h6>
                <div class="row">
                    ${outOfStockItems.map(item => `
                        <div class="col-md-6 mb-2">
                            <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                <span class="fw-medium">${item.name}</span>
                                <span class="badge bg-danger">Out of Stock</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    summaryDiv.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-boxes me-3 text-primary" style="font-size: 2rem;"></i>
                    <div>
                        <h4 class="mb-0">${Utils.formatNumber(totalItems)}</h4>
                        <small class="text-muted">Total Items</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-layer-group me-3 text-success" style="font-size: 2rem;"></i>
                    <div>
                        <h4 class="mb-0">${Utils.formatNumber(totalQuantity)}</h4>
                        <small class="text-muted">Total Quantity</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <div class="d-flex align-items-center">
                <i class="${statusIcon} me-2 text-${statusClass}" style="font-size: 1.2rem;"></i>
                <span class="fw-medium text-${statusClass}">${statusText}</span>
            </div>
        </div>
        
        ${lowStockHTML}
        ${outOfStockHTML}
    `;
}

// Items Management Functions
async function renderItemsPage() {
    const container = document.getElementById('items-container');
    if (!container) return;

    try {
        const items = await API.getItems();
        renderItemsForm(container);
        renderItemsTable(container, items);
        setupEventListeners(container, items);
    } catch (error) {
        console.error('Failed to render items page:', error);
    }
}

function renderItemsForm(container) {
    const formHTML = `
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-plus me-2 text-primary"></i>Add / Edit Item
                </h5>
            </div>
            <div class="card-body">
                <form id="item-form" class="row g-3">
                    <input type="hidden" id="item-id" value="">
                    <div class="col-md-6">
                        <label for="item-name" class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="item-name" required 
                               placeholder="Enter item name">
                    </div>
                    <div class="col-md-4">
                        <label for="item-quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="item-quantity" required 
                               min="0" placeholder="0">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-1"></i>Save
                        </button>
                    </div>
                    <div class="col-12" id="cancel-edit-container" style="display: none;">
                        <button type="button" class="btn btn-outline-secondary" id="cancel-edit">
                            <i class="fas fa-times me-1"></i>Cancel Edit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;

    container.innerHTML = formHTML + container.innerHTML;
}

function renderItemsTable(container, items) {
    const tableHTML = `
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2 text-primary"></i>Items List
                </h5>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="search-items" 
                           placeholder="Search items..." style="width: 200px;">
                    <button class="btn btn-outline-primary btn-sm" onclick="exportItems()">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="items-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${items.map(item => `
                                <tr data-item-id="${item.id}">
                                    <td class="fw-medium">${item.name}</td>
                                    <td>
                                        <span class="fw-bold">${item.quantity}</span>
                                    </td>
                                    <td>
                                        ${getStatusBadge(item.quantity)}
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            ${new Date(item.updated_at).toLocaleDateString()}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" data-edit="${item.id}" 
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" data-delete="${item.id}" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;

    // Find the existing table and replace it
    const existingTable = container.querySelector('.card:last-child');
    if (existingTable) {
        existingTable.outerHTML = tableHTML;
    } else {
        container.insertAdjacentHTML('beforeend', tableHTML);
    }
}

function getStatusBadge(quantity) {
    const qty = Number(quantity);
    if (qty === 0) {
        return '<span class="status-badge danger">Out of Stock</span>';
    } else if (qty < CONFIG.LOW_STOCK_THRESHOLD) {
        return '<span class="status-badge warning">Low Stock</span>';
    } else {
        return '<span class="status-badge success">In Stock</span>';
    }
}

function setupEventListeners(container, items) {
    // Form submission
    const form = document.getElementById('item-form');
    const cancelBtn = document.getElementById('cancel-edit');
    const cancelContainer = document.getElementById('cancel-edit-container');
    
    form.onsubmit = async function(e) {
        e.preventDefault();
        await handleFormSubmission();
    };

    cancelBtn.onclick = function() {
        resetForm();
    };

    // Search functionality
    const searchInput = document.getElementById('search-items');
    if (searchInput) {
        searchInput.addEventListener('input', Utils.debounce(function() {
            filterItems(this.value, items);
        }, 300));
    }

    // Edit/Delete handlers
    container.querySelectorAll('button[data-edit]').forEach(btn => {
        btn.onclick = function() {
            const id = btn.getAttribute('data-edit');
            const item = items.find(item => item.id == id);
            if (item) {
                populateForm(item);
            }
        };
    });

    container.querySelectorAll('button[data-delete]').forEach(btn => {
        btn.onclick = function() {
            const id = btn.getAttribute('data-delete');
            handleDeleteItem(id);
        };
    });
}

async function handleFormSubmission() {
    const id = document.getElementById('item-id').value;
    const name = document.getElementById('item-name').value.trim();
    const quantity = Number(document.getElementById('item-quantity').value);

    if (!name) {
        Utils.showAlert('Please enter an item name', 'warning');
        return;
    }

    try {
        if (id) {
            await API.updateItem({ id, name, quantity });
            Utils.showAlert('Item updated successfully!', 'success');
        } else {
            await API.addItem({ name, quantity });
            Utils.showAlert('Item added successfully!', 'success');
        }
        
        resetForm();
        renderItemsPage();
    } catch (error) {
        console.error('Failed to save item:', error);
    }
}

function populateForm(item) {
    document.getElementById('item-id').value = item.id;
    document.getElementById('item-name').value = item.name;
    document.getElementById('item-quantity').value = item.quantity;
    document.getElementById('cancel-edit-container').style.display = 'block';
    
    // Scroll to form
    document.getElementById('item-form').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('item-form').reset();
    document.getElementById('item-id').value = '';
    document.getElementById('cancel-edit-container').style.display = 'none';
}

async function handleDeleteItem(id) {
    if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        try {
            await API.deleteItem(id);
            Utils.showAlert('Item deleted successfully!', 'success');
            renderItemsPage();
        } catch (error) {
            console.error('Failed to delete item:', error);
        }
    }
}

function filterItems(searchTerm, items) {
    const tbody = document.querySelector('#items-table tbody');
    if (!tbody) return;

    const filteredItems = items.filter(item => 
        item.name.toLowerCase().includes(searchTerm.toLowerCase())
    );

    tbody.innerHTML = filteredItems.map(item => `
        <tr data-item-id="${item.id}">
            <td class="fw-medium">${item.name}</td>
            <td>
                <span class="fw-bold">${item.quantity}</span>
            </td>
            <td>
                ${getStatusBadge(item.quantity)}
            </td>
            <td>
                <small class="text-muted">
                    ${new Date(item.updated_at).toLocaleDateString()}
                </small>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" data-edit="${item.id}" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-danger" data-delete="${item.id}" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');

    // Re-attach event listeners
    tbody.querySelectorAll('button[data-edit]').forEach(btn => {
        btn.onclick = function() {
            const id = btn.getAttribute('data-edit');
            const item = items.find(item => item.id == id);
            if (item) {
                populateForm(item);
            }
        };
    });

    tbody.querySelectorAll('button[data-delete]').forEach(btn => {
        btn.onclick = function() {
            const id = btn.getAttribute('data-delete');
            handleDeleteItem(id);
        };
    });
}

function exportItems() {
    Utils.showAlert('Export feature coming soon!', 'info');
}

// Initialize based on current page
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    
    if (currentPage === 'index.html' || currentPage === '' || currentPage === 'index.php') {
        renderDashboard();
    } else if (currentPage === 'items.html') {
        renderItemsPage();
    }
});
