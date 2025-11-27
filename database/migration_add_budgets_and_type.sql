-- Migration: Thêm bảng budgets và cột type cho expenses
-- Chạy file này sau khi đã có database cơ bản

USE expense_manager;

-- Thêm cột type vào bảng expenses (income/expense)
ALTER TABLE expenses 
ADD COLUMN type ENUM('income', 'expense') NOT NULL DEFAULT 'expense' AFTER amount,
ADD INDEX idx_type (type);

-- Tạo bảng budgets
CREATE TABLE IF NOT EXISTS budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    period ENUM('daily', 'weekly', 'monthly', 'yearly') NOT NULL DEFAULT 'monthly',
    start_date DATE NOT NULL,
    end_date DATE NULL,
    alert_threshold DECIMAL(5,2) DEFAULT 80.00 COMMENT 'Ngưỡng cảnh báo (%)',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_category_id (category_id),
    INDEX idx_period (period),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm cột type vào categories (để phân biệt danh mục thu/chi)
ALTER TABLE categories 
ADD COLUMN category_type ENUM('income', 'expense', 'both') NOT NULL DEFAULT 'expense' AFTER name;

