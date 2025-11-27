<?php
require_once __DIR__ . '/config/path.php';
$pageTitle = 'Quản lý ngân sách';
$additionalScripts = ['assets/js/budgets.js'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2><i class="fas fa-piggy-bank"></i> Quản Lý Ngân Sách</h2>
        <button class="btn btn-primary" id="addBudgetBtn">
            <i class="fas fa-plus"></i> Thêm ngân sách
        </button>
    </div>

    <!-- Budgets List -->
    <div class="budgets-list" id="budgetsList">
        <div class="loading">Đang tải...</div>
    </div>
</div>

<!-- Add/Edit Budget Modal -->
<div id="budgetModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalTitle">Thêm ngân sách</h2>
        <form id="budgetForm">
            <input type="hidden" id="budgetId">
            
            <div class="form-group">
                <label for="budgetCategory">Danh mục *</label>
                <select id="budgetCategory" required>
                    <option value="">Chọn danh mục</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="budgetAmount">Số tiền ngân sách (đ) *</label>
                <input type="number" id="budgetAmount" step="0.01" min="0.01" required placeholder="Nhập số tiền">
            </div>
            
            <div class="form-group">
                <label for="budgetPeriod">Chu kỳ *</label>
                <select id="budgetPeriod" required>
                    <option value="daily">Hàng ngày</option>
                    <option value="weekly">Hàng tuần</option>
                    <option value="monthly" selected>Hàng tháng</option>
                    <option value="yearly">Hàng năm</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="budgetStartDate">Ngày bắt đầu *</label>
                <input type="date" id="budgetStartDate" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label for="budgetEndDate">Ngày kết thúc (tùy chọn)</label>
                <input type="date" id="budgetEndDate">
                <small>Để trống nếu không giới hạn</small>
            </div>
            
            <div class="form-group">
                <label for="budgetAlertThreshold">Ngưỡng cảnh báo (%)</label>
                <input type="number" id="budgetAlertThreshold" step="0.01" min="0" max="100" value="80" required>
                <small>Cảnh báo khi chi tiêu đạt % này (mặc định: 80%)</small>
            </div>
            
            <div id="budgetErrorMessage" class="error-message" style="display: none;"></div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="cancelBudget">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

