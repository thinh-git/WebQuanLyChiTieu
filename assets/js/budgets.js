// Budgets Management

// Load budgets
async function loadBudgets() {
    try {
        const basePath = window.BASE_PATH || '';
        const response = await fetch(basePath + '/api/budgets.php');
        const result = await response.json();
        
        if (result.success) {
            renderBudgets(result.data);
        } else {
            document.getElementById('budgetsList').innerHTML = '<div class="error-message">Có lỗi xảy ra khi tải dữ liệu</div>';
        }
    } catch (error) {
        console.error('Error loading budgets:', error);
        document.getElementById('budgetsList').innerHTML = '<div class="error-message">Có lỗi xảy ra khi tải dữ liệu</div>';
    }
}

// Render budgets list
function renderBudgets(budgets) {
    const list = document.getElementById('budgetsList');
    if (!list) return;
    
    if (budgets.length === 0) {
        list.innerHTML = '<div class="empty-state"><p>Chưa có ngân sách nào. Hãy thêm ngân sách mới!</p></div>';
        return;
    }
    
    list.innerHTML = budgets.map(budget => {
        const statusClass = budget.status === 'exceeded' ? 'exceeded' : (budget.status === 'warning' ? 'warning' : 'normal');
        const statusIcon = budget.status === 'exceeded' ? 'fa-exclamation-triangle' : (budget.status === 'warning' ? 'fa-exclamation-circle' : 'fa-check-circle');
        const statusColor = budget.status === 'exceeded' ? 'var(--danger-color)' : (budget.status === 'warning' ? 'var(--warning-color)' : 'var(--secondary-color)');
        const periodText = {
            'daily': 'Hàng ngày',
            'weekly': 'Hàng tuần',
            'monthly': 'Hàng tháng',
            'yearly': 'Hàng năm'
        }[budget.period] || budget.period;
        
        return `
            <div class="budget-card ${statusClass}">
                <div class="budget-header">
                    <div class="budget-category">
                        <div class="budget-category-icon" style="background-color: ${budget.category_color}20; color: ${budget.category_color}">
                            ${budget.category_icon || '💰'}
                        </div>
                        <div>
                            <h3>${budget.category_name}</h3>
                            <p>${periodText}</p>
                        </div>
                    </div>
                    <div class="budget-status" style="color: ${statusColor}">
                        <i class="fas ${statusIcon}"></i>
                    </div>
                </div>
                
                <div class="budget-amounts">
                    <div class="budget-amount-item">
                        <span>Ngân sách:</span>
                        <strong>${formatCurrency(budget.amount)}</strong>
                    </div>
                    <div class="budget-amount-item">
                        <span>Đã chi:</span>
                        <strong style="color: ${budget.status === 'exceeded' ? 'var(--danger-color)' : 'var(--text-color)'}">${formatCurrency(budget.spent_amount)}</strong>
                    </div>
                    <div class="budget-amount-item">
                        <span>Còn lại:</span>
                        <strong style="color: ${budget.remaining > 0 ? 'var(--secondary-color)' : 'var(--danger-color)'}">${formatCurrency(budget.remaining)}</strong>
                    </div>
                </div>
                
                <div class="budget-progress-container">
                    <div class="budget-progress-info">
                        <span>Tiến độ: ${budget.percentage.toFixed(1)}%</span>
                        <span>Ngưỡng cảnh báo: ${budget.alert_threshold}%</span>
                    </div>
                    <div class="budget-progress">
                        <div class="budget-progress-bar" style="width: ${Math.min(budget.percentage, 100)}%; background: ${budget.status === 'exceeded' ? 'var(--danger-color)' : (budget.status === 'warning' ? 'var(--warning-color)' : 'var(--secondary-color)')}"></div>
                    </div>
                </div>
                
                <div class="budget-actions">
                    <button class="btn btn-sm btn-primary" onclick="editBudget(${budget.id})">
                        <i class="fas fa-edit"></i> Sửa
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteBudget(${budget.id})">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

// Load categories for budget form
async function loadCategoriesForBudget() {
    try {
        const basePath = window.BASE_PATH || '';
        const response = await fetch(basePath + '/api/categories.php');
        const result = await response.json();
        
        if (result.success) {
            const select = document.getElementById('budgetCategory');
            if (select) {
                const options = result.data
                    .filter(cat => cat.category_type === 'expense' || cat.category_type === 'both')
                    .map(cat => `<option value="${cat.id}">${cat.icon} ${cat.name}</option>`)
                    .join('');
                select.innerHTML = '<option value="">Chọn danh mục</option>' + options;
            }
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Add budget
if (document.getElementById('addBudgetBtn')) {
    document.getElementById('addBudgetBtn').addEventListener('click', function() {
        document.getElementById('modalTitle').textContent = 'Thêm ngân sách';
        document.getElementById('budgetForm').reset();
        document.getElementById('budgetId').value = '';
        document.getElementById('budgetStartDate').value = new Date().toISOString().split('T')[0];
        document.getElementById('budgetAlertThreshold').value = 80;
        hideMessage('budgetErrorMessage');
        loadCategoriesForBudget();
        openModal('budgetModal');
    });
}

// Edit budget
window.editBudget = async function(budgetId) {
    try {
        const basePath = window.BASE_PATH || '';
        const response = await fetch(basePath + '/api/budgets.php');
        const result = await response.json();
        
        if (result.success) {
            const budget = result.data.find(b => b.id === budgetId);
            if (budget) {
                document.getElementById('modalTitle').textContent = 'Sửa ngân sách';
                document.getElementById('budgetId').value = budget.id;
                document.getElementById('budgetCategory').value = budget.category_id;
                document.getElementById('budgetAmount').value = budget.amount;
                document.getElementById('budgetPeriod').value = budget.period;
                document.getElementById('budgetStartDate').value = budget.start_date;
                document.getElementById('budgetEndDate').value = budget.end_date || '';
                document.getElementById('budgetAlertThreshold').value = budget.alert_threshold;
                
                hideMessage('budgetErrorMessage');
                loadCategoriesForBudget();
                // Set category after categories are loaded
                setTimeout(() => {
                    document.getElementById('budgetCategory').value = budget.category_id;
                }, 100);
                openModal('budgetModal');
            }
        }
    } catch (error) {
        console.error('Error loading budget:', error);
        alert('Có lỗi xảy ra khi tải thông tin ngân sách');
    }
};

// Delete budget
window.deleteBudget = async function(budgetId) {
    if (!confirm('Bạn có chắc chắn muốn xóa ngân sách này?')) {
        return;
    }
    
    try {
        const basePath = window.BASE_PATH || '';
        const response = await fetch(basePath + `/api/budgets.php?id=${budgetId}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            loadBudgets();
        } else {
            alert(result.message || 'Có lỗi xảy ra');
        }
    } catch (error) {
        console.error('Error deleting budget:', error);
        alert('Có lỗi xảy ra khi xóa ngân sách');
    }
};

// Budget form submit
if (document.getElementById('budgetForm')) {
    document.getElementById('budgetForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        hideMessage('budgetErrorMessage');
        
        const budgetId = document.getElementById('budgetId').value;
        const categoryId = parseInt(document.getElementById('budgetCategory').value);
        const amount = parseFloat(document.getElementById('budgetAmount').value);
        const period = document.getElementById('budgetPeriod').value;
        const startDate = document.getElementById('budgetStartDate').value;
        const endDate = document.getElementById('budgetEndDate').value || null;
        const alertThreshold = parseFloat(document.getElementById('budgetAlertThreshold').value);
        
        if (!categoryId || !amount || !startDate) {
            showMessage('budgetErrorMessage', 'Vui lòng điền đầy đủ thông tin');
            return;
        }
        
        try {
            const basePath = window.BASE_PATH || '';
            const url = basePath + '/api/budgets.php';
            const method = budgetId ? 'PUT' : 'POST';
            const data = {
                id: budgetId || undefined,
                category_id: categoryId,
                amount: amount,
                period: period,
                start_date: startDate,
                end_date: endDate,
                alert_threshold: alertThreshold
            };
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                closeModal('budgetModal');
                loadBudgets();
            } else {
                showMessage('budgetErrorMessage', result.message || 'Có lỗi xảy ra');
            }
        } catch (error) {
            console.error('Error saving budget:', error);
            showMessage('budgetErrorMessage', 'Có lỗi xảy ra khi lưu ngân sách');
        }
    });
}

// Close modal buttons
if (document.getElementById('cancelBudget')) {
    document.getElementById('cancelBudget').addEventListener('click', function() {
        closeModal('budgetModal');
    });
}

// Close modal X button
document.querySelectorAll('.close').forEach(btn => {
    btn.addEventListener('click', function() {
        this.closest('.modal').classList.remove('show');
    });
});

// Initialize
if (document.getElementById('budgetsList')) {
    loadBudgets();
}

