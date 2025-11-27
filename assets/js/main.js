// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function showMessage(elementId, message, isError = true) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = message;
        element.style.display = 'block';
        element.className = isError ? 'error-message' : 'success-message';
        
        if (!isError) {
            setTimeout(() => {
                element.style.display = 'none';
            }, 5000);
        }
    }
}

function hideMessage(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.style.display = 'none';
    }
}

// Modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
    }
});

// Categories management (for categories.php)
if (document.getElementById('categoriesGrid')) {
    let categories = [];

    // Load categories
    async function loadCategories() {
        try {
            const basePath = window.BASE_PATH || '';
            const response = await fetch(basePath + '/api/categories.php');
            const result = await response.json();
            
            if (result.success) {
                categories = result.data;
                renderCategories();
            }
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    }

    // Render categories
    function renderCategories() {
        const grid = document.getElementById('categoriesGrid');
        if (categories.length === 0) {
            grid.innerHTML = '<div class="empty-state"><p>Chưa có danh mục nào. Hãy thêm danh mục mới!</p></div>';
            return;
        }

        grid.innerHTML = categories.map(cat => `
            <div class="category-card" style="border-top: 4px solid ${cat.color}">
                <div class="category-icon">${cat.icon || '💰'}</div>
                <div class="category-name">${cat.name}</div>
                <div class="category-count">${cat.expense_count || 0} chi tiêu</div>
                <div class="category-actions">
                    <button class="btn btn-sm btn-primary" onclick="editCategory(${cat.id})">Sửa</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteCategory(${cat.id})" ${cat.expense_count > 0 ? 'disabled title="Không thể xóa danh mục có chi tiêu"' : ''}>Xóa</button>
                </div>
            </div>
        `).join('');
    }

    // Add category
    if (document.getElementById('addCategoryBtn')) {
        document.getElementById('addCategoryBtn').addEventListener('click', function() {
            document.getElementById('modalTitle').textContent = 'Thêm danh mục';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryId').value = '';
            document.getElementById('categoryColor').value = '#3498db';
            document.getElementById('categoryIcon').value = '💰';
            hideMessage('categoryErrorMessage');
            openModal('categoryModal');
        });
    }

    // Edit category
    window.editCategory = function(categoryId) {
        const category = categories.find(c => c.id === categoryId);
        if (category) {
            document.getElementById('modalTitle').textContent = 'Sửa danh mục';
            document.getElementById('categoryId').value = category.id;
            document.getElementById('categoryName').value = category.name;
            document.getElementById('categoryIcon').value = category.icon || '💰';
            document.getElementById('categoryColor').value = category.color || '#3498db';
            hideMessage('categoryErrorMessage');
            openModal('categoryModal');
        }
    };

    // Delete category
    window.deleteCategory = async function(categoryId) {
        if (!confirm('Bạn có chắc chắn muốn xóa danh mục này?')) {
            return;
        }

        try {
            const basePath = window.BASE_PATH || '';
            const response = await fetch(basePath + `/api/categories.php?id=${categoryId}`, {
                method: 'DELETE'
            });
            const result = await response.json();

            if (result.success) {
                loadCategories();
            } else {
                alert(result.message || 'Có lỗi xảy ra');
            }
        } catch (error) {
            console.error('Error deleting category:', error);
            alert('Có lỗi xảy ra khi xóa danh mục');
        }
    };

    // Category form submit
    if (document.getElementById('categoryForm')) {
        document.getElementById('categoryForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            hideMessage('categoryErrorMessage');

            const categoryId = document.getElementById('categoryId').value;
            const name = document.getElementById('categoryName').value.trim();
            const icon = document.getElementById('categoryIcon').value.trim() || '💰';
            const color = document.getElementById('categoryColor').value;

            if (!name) {
                showMessage('categoryErrorMessage', 'Vui lòng nhập tên danh mục');
                return;
            }

            try {
                const basePath = window.BASE_PATH || '';
                const url = basePath + '/api/categories.php';
                const method = categoryId ? 'PUT' : 'POST';
                const data = {
                    id: categoryId || undefined,
                    name: name,
                    icon: icon,
                    color: color
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
                    closeModal('categoryModal');
                    loadCategories();
                } else {
                    showMessage('categoryErrorMessage', result.message || 'Có lỗi xảy ra');
                }
            } catch (error) {
                console.error('Error saving category:', error);
                showMessage('categoryErrorMessage', 'Có lỗi xảy ra khi lưu danh mục');
            }
        });
    }

    // Close modal buttons
    if (document.getElementById('cancelCategory')) {
        document.getElementById('cancelCategory').addEventListener('click', function() {
            closeModal('categoryModal');
        });
    }

    // Close modal X button
    document.querySelectorAll('.close').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.modal').classList.remove('show');
        });
    });

    // Load categories on page load
    loadCategories();
}

