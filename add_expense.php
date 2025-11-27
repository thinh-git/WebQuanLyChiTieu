<?php
require_once __DIR__ . '/config/path.php';
$pageTitle = 'Thêm chi tiêu';
$additionalScripts = ['assets/js/expenses.js'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2><i class="fas fa-plus-circle"></i> Thêm giao dịch mới</h2>
        <a href="<?php echo base_url('dashboard.php'); ?>" class="btn btn-secondary">← Quay lại</a>
    </div>

    <div class="form-card">
        <form id="addExpenseForm">
            <div class="form-group">
                <label for="transactionType">Loại giao dịch *</label>
                <div class="type-selector">
                    <label class="type-option">
                        <input type="radio" name="type" value="expense" id="typeExpense" checked>
                        <span class="type-label expense-type">
                            <i class="fas fa-arrow-down"></i> Chi tiêu
                        </span>
                    </label>
                    <label class="type-option">
                        <input type="radio" name="type" value="income" id="typeIncome">
                        <span class="type-label income-type">
                            <i class="fas fa-arrow-up"></i> Thu nhập
                        </span>
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="amount">Số tiền (đ) *</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0.01" required placeholder="Nhập số tiền">
            </div>
            
            <div class="form-group">
                <label for="category">Danh mục *</label>
                <select id="category" name="category_id" required>
                    <option value="">Chọn danh mục</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea id="description" name="description" rows="4" placeholder="Nhập mô tả (tùy chọn)"></textarea>
            </div>
            
            <div class="form-group">
                <label for="expenseDate">Ngày *</label>
                <input type="date" id="expenseDate" name="expense_date" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div id="errorMessage" class="error-message" style="display: none;"></div>
            <div id="successMessage" class="success-message" style="display: none;"></div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='<?php echo base_url('dashboard.php'); ?>'">Hủy</button>
                <button type="submit" class="btn btn-primary">Thêm chi tiêu</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

