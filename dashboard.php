<?php
require_once __DIR__ . '/config/path.php';
$pageTitle = 'Trang chủ';
$additionalScripts = ['assets/js/expenses.js'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2><i class="fas fa-chart-line"></i> Dashboard - Quản Lý Tài Chính</h2>
        <div style="display: flex; gap: 10px;">
            <a href="<?php echo base_url('add_expense.php'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm giao dịch
            </a>
            <a href="<?php echo base_url('budgets.php'); ?>" class="btn btn-secondary">
                <i class="fas fa-piggy-bank"></i> Ngân sách
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-card">
        <div class="filter-row">
            <div class="filter-group">
                <label for="searchExpense">Tìm kiếm</label>
                <input type="text" id="searchExpense" placeholder="Tìm theo mô tả hoặc danh mục...">
            </div>
            
            <div class="filter-group">
                <label for="filterType">Loại</label>
                <select id="filterType">
                    <option value="">Tất cả</option>
                    <option value="income">Thu nhập</option>
                    <option value="expense">Chi tiêu</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="filterCategory">Danh mục</label>
                <select id="filterCategory">
                    <option value="">Tất cả</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="filterStartDate">Từ ngày</label>
                <input type="date" id="filterStartDate">
            </div>
            
            <div class="filter-group">
                <label for="filterEndDate">Đến ngày</label>
                <input type="date" id="filterEndDate">
            </div>
            
            <div class="filter-group">
                <button type="button" class="btn btn-secondary" id="clearFilters">Xóa bộ lọc</button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card income-card">
            <div class="summary-icon income-icon">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="summary-content">
                <h3>Tổng thu nhập</h3>
                <p class="summary-amount" id="totalIncome">0 đ</p>
            </div>
        </div>
        <div class="summary-card expense-card">
            <div class="summary-icon expense-icon">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="summary-content">
                <h3>Tổng chi tiêu</h3>
                <p class="summary-amount" id="totalExpense">0 đ</p>
            </div>
        </div>
        <div class="summary-card balance-card">
            <div class="summary-icon balance-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="summary-content">
                <h3>Số dư</h3>
                <p class="summary-amount" id="balance">0 đ</p>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-list"></i>
            </div>
            <div class="summary-content">
                <h3>Tổng giao dịch</h3>
                <p class="summary-amount" id="totalTransactions">0</p>
            </div>
        </div>
    </div>
    
    <!-- Budget Alerts -->
    <div id="budgetAlerts" class="budget-alerts"></div>

    <!-- Expenses List -->
    <div class="expenses-list" id="expensesList">
        <div class="loading">Đang tải...</div>
    </div>

    <!-- Pagination -->
    <div class="pagination" id="pagination"></div>
</div>

<!-- Edit Expense Modal -->
<div id="editExpenseModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Sửa chi tiêu</h2>
        <form id="editExpenseForm">
            <input type="hidden" id="editExpenseId">
            
            <div class="form-group">
                <label for="editTransactionType">Loại giao dịch *</label>
                <div class="type-selector">
                    <label class="type-option">
                        <input type="radio" name="editType" value="expense" id="editTypeExpense">
                        <span class="type-label expense-type">
                            <i class="fas fa-arrow-down"></i> Chi tiêu
                        </span>
                    </label>
                    <label class="type-option">
                        <input type="radio" name="editType" value="income" id="editTypeIncome">
                        <span class="type-label income-type">
                            <i class="fas fa-arrow-up"></i> Thu nhập
                        </span>
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="editAmount">Số tiền (đ)</label>
                <input type="number" id="editAmount" step="0.01" min="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="editCategory">Danh mục</label>
                <select id="editCategory" required></select>
            </div>
            
            <div class="form-group">
                <label for="editDescription">Mô tả</label>
                <textarea id="editDescription" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label for="editExpenseDate">Ngày</label>
                <input type="date" id="editExpenseDate" required>
            </div>
            
            <div id="editErrorMessage" class="error-message" style="display: none;"></div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="cancelEdit">Hủy</button>
                <button type="submit" class="btn btn-primary">Cập nhật</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

