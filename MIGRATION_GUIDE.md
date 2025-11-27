# Hướng Dẫn Migration Database

## Cập Nhật Database Cho Tính Năng Mới

Project đã được cập nhật với các tính năng mới:
- ✅ Hỗ trợ Thu nhập và Chi tiêu (income/expense)
- ✅ Quản lý Ngân sách (budgets)
- ✅ Cảnh báo ngân sách tự động

## Bước 1: Backup Database

**QUAN TRỌNG**: Luôn backup database trước khi migration!

```sql
-- Backup database
mysqldump -u root -p expense_manager > backup_$(date +%Y%m%d).sql
```

Hoặc trong phpMyAdmin:
1. Chọn database `expense_manager`
2. Vào tab **Export**
3. Chọn **Quick** hoặc **Custom**
4. Nhấn **Go** để export

## Bước 2: Chạy Migration

### Cách 1: Sử dụng phpMyAdmin (Khuyến nghị)

1. Mở phpMyAdmin: `http://localhost/phpmyadmin`
2. Chọn database `expense_manager`
3. Vào tab **SQL**
4. Copy và paste nội dung file `database/migration_add_budgets_and_type.sql`
5. Nhấn **Go** để thực thi

### Cách 2: Sử dụng MySQL Command Line

```bash
mysql -u root -p expense_manager < database/migration_add_budgets_and_type.sql
```

## Bước 3: Kiểm Tra

Sau khi migration, kiểm tra:

1. **Bảng expenses có cột `type`**:
```sql
DESCRIBE expenses;
-- Phải thấy cột `type` với kiểu ENUM('income', 'expense')
```

2. **Bảng budgets đã được tạo**:
```sql
SHOW TABLES;
-- Phải thấy bảng `budgets`
```

3. **Bảng categories có cột `category_type`**:
```sql
DESCRIBE categories;
-- Phải thấy cột `category_type`
```

## Bước 4: Cập Nhật Dữ Liệu Hiện Có

Nếu bạn đã có dữ liệu cũ, tất cả expenses sẽ mặc định là `expense`. Nếu muốn đổi một số thành `income`:

```sql
-- Ví dụ: Đổi expense có id = 1 thành income
UPDATE expenses SET type = 'income' WHERE id = 1;
```

## Lưu Ý

- Migration sẽ **KHÔNG** xóa dữ liệu hiện có
- Tất cả expenses cũ sẽ mặc định là `type = 'expense'`
- Bạn có thể tạo ngân sách mới sau khi migration
- Nếu gặp lỗi, restore từ backup và kiểm tra lại

## Troubleshooting

### Lỗi: "Duplicate column name 'type'"
- Cột `type` đã tồn tại, bỏ qua bước thêm cột này

### Lỗi: "Table 'budgets' already exists"
- Bảng `budgets` đã tồn tại, có thể bỏ qua hoặc xóa bảng cũ trước

### Lỗi: "Foreign key constraint fails"
- Kiểm tra dữ liệu trong bảng `categories` và `users`
- Đảm bảo không có dữ liệu orphan

## Rollback (Nếu cần)

Nếu muốn rollback, restore từ backup:

```bash
mysql -u root -p expense_manager < backup_YYYYMMDD.sql
```

Hoặc trong phpMyAdmin:
1. Chọn database
2. Vào tab **Import**
3. Chọn file backup
4. Nhấn **Go**

---

**Chúc bạn migration thành công!** 🎉

