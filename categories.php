<?php
require_once __DIR__ . '/config/path.php';
$pageTitle = 'Quản lý danh mục';
$additionalScripts = ['assets/js/main.js'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2>Quản lý danh mục</h2>
        <button class="btn btn-primary" id="addCategoryBtn">+ Thêm danh mục</button>
    </div>

    <div class="categories-grid" id="categoriesGrid">
        <div class="loading">Đang tải...</div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalTitle">Thêm danh mục</h2>
        <form id="categoryForm">
            <input type="hidden" id="categoryId">
            
            <div class="form-group">
                <label for="categoryName">Tên danh mục *</label>
                <input type="text" id="categoryName" required maxlength="100">
            </div>
            
            <div class="form-group">
                <label for="categoryIcon">Icon (emoji)</label>
                <input type="text" id="categoryIcon" maxlength="10" placeholder="💰">
                <small>Ví dụ: 🍔, 🚗, 🛍️</small>
            </div>
            
            <div class="form-group">
                <label for="categoryColor">Màu sắc</label>
                <input type="color" id="categoryColor" value="#3498db">
            </div>
            
            <div id="categoryErrorMessage" class="error-message" style="display: none;"></div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="cancelCategory">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

