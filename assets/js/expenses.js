let currentPage = 1;
let categories = [];

// Load categories for dropdowns
async function loadCategories() {
    try {
        const basePath = window.BASE_PATH || '';
        const response = await fetch(basePath + '/api/categories.php');
        const result = await response.json();
        
        if (result.success) {
            categories = result.data;
            populateCategoryDropdowns();
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Populate category dropdowns
function populateCategoryDropdowns() {
    const categorySelect = document.getElementById('category');
    const filterCategorySelect = document.getElementById('filterCategory');
    const editCategorySelect = document.getElementById('editCategory');
    
    const options = categories.map(cat => 
        `<option value="${cat.id}" data-icon="${cat.icon}" data-color="${cat.color}">${cat.icon} ${cat.name}</option>`
    ).join('');
    
    if (categorySelect) {
        categorySelect.innerHTML = '<option value="">Chọn danh mục</option>' + options;
    }
    
    if (filterCategorySelect) {
        filterCategorySelect.innerHTML = '<option value="">Tất cả</option>' + options;
    }
    
    if (editCategorySelect) {
        editCategorySelect.innerHTML = '<option value="">Chọn danh mục</option>' + options;
    }
}

// Load expenses
async function loadExpenses(page = 1) {
    const search = document.getElementById('searchExpense')?.value || '';
    const categoryId = document.getElementById('filterCategory')?.value || '';
    const type = document.getElementById('filterType')?.value || '';
    const startDate = document.getElementById('filterStartDate')?.value || '';
    const endDate = document.getElementById('filterEndDate')?.value || '';
    
    const params = new URLSearchParams({
        page: page,
        limit: 20
    });
    
    if (search) params.append('search', search);
    if (categoryId) params.append('category_id', categoryId);
    if (type) params.append('type', type);
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    
    try {
        const basePath = window.BASE_PATH || '';
        const response = await fetch(basePath + `/api/expenses.php?${params}`);
        const result = await response.json();
        
        if (result.success) {
            renderExpenses(result.data);
            renderPagination(result.pagination);
            updateSummary(result.data);
            loadDashboardSummary(); // Load full summary for dashboard
            loadBudgetAlerts(); // Load budget alerts
            currentPage = page;
        }
    } catch (error) {
        console.error('Error loading expenses:', error);
        document.getElementById('expensesList').innerHTML = '<div class="error-message">Có lỗi xảy ra khi tải dữ liệu</div>';
    }
}

// Render expenses list
function renderExpenses(expenses) {
    const list = document.getElementById('expensesList');
    
    if (!list) return;
    
    if (expenses.length === 0) {
        list.innerHTML = '<div class="empty-state"><p>Không có chi tiêu nào. Hãy thêm chi tiêu mới!</p></div>';
        return;
    }
    
    list.innerHTML = expenses.map(expense => {
        const typeClass = expense.type === 'income' ? 'income' : 'expense';
        const typeIcon = expense.type === 'income' ? '<i class="fas fa-arrow-up"></i>' : '<i class="fas fa-arrow-down"></i>';
        const typeText = expense.type === 'income' ? 'Thu' : 'Chi';
        const amountClass = expense.type === 'income' ? 'income-amount' : 'expense-amount';
        
        return `
        <div class="expense-item">
            <div class="expense-info">
                <div class="expense-icon" style="background-color: ${expense.category_color}20; color: ${expense.category_color}">
                    ${expense.category_icon || '💰'}
                </div>
                <div class="expense-details">
                    <h4>
                        ${expense.description || 'Không có mô tả'}
                        <span class="expense-type-badge ${typeClass}">${typeIcon} ${typeText}</span>
                    </h4>
                    <p>${expense.category_name} • ${formatDate(expense.expense_date)}</p>
                </div>
            </div>
            <div style="display: flex; align-items: center;">
                <div class="expense-amount ${amountClass}">${expense.type === 'income' ? '+' : '-'}${formatCurrency(expense.amount)}</div>
                <div class="expense-actions">
                    <button class="btn btn-sm btn-primary" onclick="editExpense(${expense.id})"><i class="fas fa-edit"></i> Sửa</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteExpense(${expense.id})"><i class="fas fa-trash"></i> Xóa</button>
                </div>
            </div>
        </div>
        `;
    }).join('');
}

// Render pagination
function renderPagination(pagination) {
    const paginationEl = document.getElementById('pagination');
    if (!paginationEl) return;
    
    if (pagination.pages <= 1) {
        paginationEl.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // Previous button
    html += `<button ${pagination.page === 1 ? 'disabled' : ''} onclick="loadExpenses(${pagination.page - 1})">‹ Trước</button>`;
    
    // Page numbers
    for (let i = 1; i <= pagination.pages; i++) {
        if (i === 1 || i === pagination.pages || (i >= pagination.page - 2 && i <= pagination.page + 2)) {
            html += `<button class="${i === pagination.page ? 'active' : ''}" onclick="loadExpenses(${i})">${i}</button>`;
        } else if (i === pagination.page - 3 || i === pagination.page + 3) {
            html += `<button disabled>...</button>`;
        }
    }
    
    // Next button
    html += `<button ${pagination.page === pagination.pages ? 'disabled' : ''} onclick="loadExpenses(${pagination.page + 1})">Sau ›</button>`;
    
    paginationEl.innerHTML = html;
}

// Update summary (for current page)
function updateSummary(expenses) {
    if (document.getElementById('totalTransactions')) {
        document.getElementById('totalTransactions').textContent = expenses.length;
    }
}

// Load full dashboard summary (income, expense, balance)
async function loadDashboardSummary() {
    try {
        const basePath = window.BASE_PATH || '';
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        
        const params = new URLSearchParams({
            start_date: firstDay.toISOString().split('T')[0],
            end_date: lastDay.toISOString().split('T')[0],
            limit: 10000
        });
        
        const response = await fetch(basePath + `/api/expenses.php?${params}`);
        const result = await response.json();
        
        if (result.success) {
            const income = result.data
                .filter(e => e.type === 'income')
                .reduce((sum, e) => sum + parseFloat(e.amount), 0);
            
            const expense = result.data
                .filter(e => e.type === 'expense')
                .reduce((sum, e) => sum + parseFloat(e.amount), 0);
            
            const balance = income - expense;
            const totalTransactions = result.data.length;
            
            if (document.getElementById('totalIncome')) {
                document.getElementById('totalIncome').textContent = formatCurrency(income);
            }
            if (document.getElementById('totalExpense')) {
                document.getElementById('totalExpense').textContent = formatCurrency(expense);
            }
            if (document.getElementById('balance')) {
                const balanceEl = document.getElementById('balance');
                balanceEl.textContent = formatCurrency(Math.abs(balance));
                balanceEl.style.color = balance >= 0 ? 'var(--income-color)' : 'var(--expense-color)';
            }
            if (document.getElementById('totalTransactions')) {
                document.getElementById('totalTransactions').textContent = totalTransactions;
            }
        }
    } catch (error) {
        console.error('Error loading dashboard summary:', error);
    }
}

// Load budget alerts
async function loadBudgetAlerts() {
    try {
        const basePath = window.BASE_PATH || '';
        const response = await fetch(basePath + '/api/budgets.php');
        const result = await response.json();
        
        if (result.success && result.data) {
            const alertsContainer = document.getElementById('budgetAlerts');
            if (!alertsContainer) return;
            
            const alerts = result.data.filter(budget => {
                return budget.status === 'warning' || budget.status === 'exceeded';
            });
            
            if (alerts.length === 0) {
                alertsContainer.innerHTML = '';
                return;
            }
            
            alertsContainer.innerHTML = alerts.map(budget => {
                const statusClass = budget.status === 'exceeded' ? 'exceeded' : 'warning';
                const statusIcon = budget.status === 'exceeded' ? 'fa-exclamation-triangle' : 'fa-exclamation-circle';
                const statusText = budget.status === 'exceeded' ? 'Vượt ngân sách' : 'Sắp vượt ngân sách';
                
                return `
                    <div class="budget-alert ${statusClass}">
                        <div class="budget-alert-icon">
                            <i class="fas ${statusIcon}"></i>
                        </div>
                        <div class="budget-alert-content">
                            <h4>${statusText}: ${budget.category_name} ${budget.category_icon}</h4>
                            <p>Đã chi: ${formatCurrency(budget.spent_amount)} / ${formatCurrency(budget.amount)} (${budget.percentage.toFixed(1)}%)</p>
                            <div class="budget-progress">
                                <div class="budget-progress-bar" style="width: ${Math.min(budget.percentage, 100)}%"></div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }
    } catch (error) {
        console.error('Error loading budget alerts:', error);
    }
}

// Add expense form
if (document.getElementById('addExpenseForm')) {
    document.getElementById('addExpenseForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        errorMessage.style.display = 'none';
        successMessage.style.display = 'none';
        
        const type = document.querySelector('input[name="type"]:checked')?.value || 'expense';
        
        const formData = {
            category_id: parseInt(document.getElementById('category').value),
            amount: parseFloat(document.getElementById('amount').value),
            type: type,
            description: document.getElementById('description').value.trim(),
            expense_date: document.getElementById('expenseDate').value
        };
        
        if (!formData.category_id || !formData.amount || !formData.expense_date) {
            showMessage('errorMessage', 'Vui lòng điền đầy đủ thông tin');
            return;
        }
        
        try {
            const basePath = window.BASE_PATH || '';
            const response = await fetch(basePath + '/api/expenses.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showMessage('successMessage', 'Thêm chi tiêu thành công!', false);
                this.reset();
                document.getElementById('expenseDate').value = new Date().toISOString().split('T')[0];
                
                setTimeout(() => {
                    window.location.href = basePath + '/dashboard.php';
                }, 1500);
            } else {
                showMessage('errorMessage', result.message || 'Có lỗi xảy ra');
            }
        } catch (error) {
            console.error('Error adding expense:', error);
            showMessage('errorMessage', 'Có lỗi xảy ra khi thêm chi tiêu');
        }
    });
}

// Edit expense
window.editExpense = async function(expenseId) {
    try {
        const basePath = window.BASE_PATH || '';
        const response = await fetch(basePath + `/api/expenses.php?page=1&limit=1000`);
        const result = await response.json();
        
        if (result.success) {
            const expense = result.data.find(e => e.id === expenseId);
            if (expense) {
                document.getElementById('editExpenseId').value = expense.id;
                document.getElementById('editAmount').value = expense.amount;
                document.getElementById('editCategory').value = expense.category_id;
                document.getElementById('editDescription').value = expense.description || '';
                document.getElementById('editExpenseDate').value = expense.expense_date;
                
                // Set type radio button
                const typeRadio = document.querySelector(`input[name="editType"][value="${expense.type || 'expense'}"]`);
                if (typeRadio) typeRadio.checked = true;
                
                hideMessage('editErrorMessage');
                openModal('editExpenseModal');
            }
        }
    } catch (error) {
        console.error('Error loading expense:', error);
        alert('Có lỗi xảy ra khi tải thông tin chi tiêu');
    }
};

// Update expense
if (document.getElementById('editExpenseForm')) {
    document.getElementById('editExpenseForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        hideMessage('editErrorMessage');
        
        const expenseId = parseInt(document.getElementById('editExpenseId').value);
        const type = document.querySelector('input[name="editType"]:checked')?.value || 'expense';
        
        const formData = {
            id: expenseId,
            category_id: parseInt(document.getElementById('editCategory').value),
            amount: parseFloat(document.getElementById('editAmount').value),
            type: type,
            description: document.getElementById('editDescription').value.trim(),
            expense_date: document.getElementById('editExpenseDate').value
        };
        
        try {
            const basePath = window.BASE_PATH || '';
            const response = await fetch(basePath + '/api/expenses.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                closeModal('editExpenseModal');
                loadExpenses(currentPage);
            } else {
                showMessage('editErrorMessage', result.message || 'Có lỗi xảy ra');
            }
        } catch (error) {
            console.error('Error updating expense:', error);
            showMessage('editErrorMessage', 'Có lỗi xảy ra khi cập nhật chi tiêu');
        }
    });
}

// Delete expense
window.deleteExpense = async function(expenseId) {
    if (!confirm('Bạn có chắc chắn muốn xóa chi tiêu này?')) {
        return;
    }
    
    try {
        const basePath = window.BASE_PATH || '';
        const response = await fetch(basePath + `/api/expenses.php?id=${expenseId}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            loadExpenses(currentPage);
        } else {
            alert(result.message || 'Có lỗi xảy ra');
        }
    } catch (error) {
        console.error('Error deleting expense:', error);
        alert('Có lỗi xảy ra khi xóa chi tiêu');
    }
};

// Filter handlers
if (document.getElementById('searchExpense')) {
    let searchTimeout;
    document.getElementById('searchExpense').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadExpenses(1);
        }, 500);
    });
}

if (document.getElementById('filterCategory')) {
    document.getElementById('filterCategory').addEventListener('change', function() {
        loadExpenses(1);
    });
}

if (document.getElementById('filterStartDate')) {
    document.getElementById('filterStartDate').addEventListener('change', function() {
        loadExpenses(1);
    });
}

if (document.getElementById('filterEndDate')) {
    document.getElementById('filterEndDate').addEventListener('change', function() {
        loadExpenses(1);
    });
}

if (document.getElementById('filterType')) {
    document.getElementById('filterType').addEventListener('change', function() {
        loadExpenses(1);
    });
}

if (document.getElementById('clearFilters')) {
    document.getElementById('clearFilters').addEventListener('click', function() {
        document.getElementById('searchExpense').value = '';
        document.getElementById('filterType').value = '';
        document.getElementById('filterCategory').value = '';
        document.getElementById('filterStartDate').value = '';
        document.getElementById('filterEndDate').value = '';
        loadExpenses(1);
    });
}

// Close modal handlers
if (document.getElementById('cancelEdit')) {
    document.getElementById('cancelEdit').addEventListener('click', function() {
        closeModal('editExpenseModal');
    });
}

document.querySelectorAll('.close').forEach(btn => {
    btn.addEventListener('click', function() {
        this.closest('.modal').classList.remove('show');
    });
});

// Initialize
if (document.getElementById('expensesList')) {
    loadCategories();
    loadExpenses(1);
}

if (document.getElementById('addExpenseForm')) {
    loadCategories();
}

