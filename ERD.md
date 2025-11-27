# Sơ Đồ ERD (Entity Relationship Diagram)

## Cơ Sở Dữ Liệu: expense_manager

### Mô Tả Tổng Quan
Hệ thống quản lý chi tiêu sử dụng cơ sở dữ liệu quan hệ với 3 bảng chính: `users`, `categories`, và `expenses`.

---

## Sơ Đồ ERD

```
┌─────────────────┐
│     USERS       │
├─────────────────┤
│ PK id (INT)      │
│    username      │
│    email         │
│    password      │
│    created_at    │
└────────┬─────────┘
         │
         │ 1:N (One-to-Many)
         │
         ├──────────────────┐
         │                  │
         ▼                  ▼
┌─────────────────┐  ┌─────────────────┐
│   CATEGORIES    │  │    EXPENSES     │
├─────────────────┤  ├─────────────────┤
│ PK id (INT)      │  │ PK id (INT)      │
│ FK user_id       │──│ FK user_id       │──┐
│    name          │  │ FK category_id   │──┼──┘
│    icon          │  │    amount        │
│    color         │  │    description   │
│    created_at    │  │    expense_date │
└─────────────────┘  │    created_at    │
                     └─────────────────┘
```

---

## Chi Tiết Các Bảng

### 1. Bảng: `users`
**Mục đích**: Lưu trữ thông tin người dùng

| Cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|-----|-------------|-----------|-------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | ID duy nhất của người dùng |
| `username` | VARCHAR(50) | NOT NULL, UNIQUE | Tên đăng nhập (3-50 ký tự) |
| `email` | VARCHAR(100) | NOT NULL, UNIQUE | Email (định dạng hợp lệ) |
| `password` | VARCHAR(255) | NOT NULL | Mật khẩu đã hash (bcrypt) |
| `created_at` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Thời gian tạo tài khoản |

**Indexes:**
- PRIMARY KEY trên `id`
- UNIQUE INDEX trên `username`
- UNIQUE INDEX trên `email`

**Quan hệ:**
- 1 user có nhiều categories (1:N)
- 1 user có nhiều expenses (1:N)

---

### 2. Bảng: `categories`
**Mục đích**: Lưu trữ danh mục chi tiêu của từng người dùng

| Cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|-----|-------------|-----------|-------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | ID duy nhất của danh mục |
| `user_id` | INT | NOT NULL, FOREIGN KEY | ID người dùng sở hữu danh mục |
| `name` | VARCHAR(100) | NOT NULL | Tên danh mục |
| `icon` | VARCHAR(50) | DEFAULT '💰' | Icon emoji cho danh mục |
| `color` | VARCHAR(7) | DEFAULT '#3498db' | Màu sắc hiển thị (hex) |
| `created_at` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Thời gian tạo danh mục |

**Indexes:**
- PRIMARY KEY trên `id`
- INDEX `idx_user_id` trên `user_id` (để tối ưu truy vấn)
- FOREIGN KEY `user_id` REFERENCES `users(id)` ON DELETE CASCADE

**Quan hệ:**
- N categories thuộc về 1 user (N:1)
- 1 category có nhiều expenses (1:N)

**Ràng buộc:**
- Khi xóa user, tất cả categories của user đó cũng bị xóa (CASCADE)
- Mỗi user có thể có nhiều categories với tên khác nhau

---

### 3. Bảng: `expenses`
**Mục đích**: Lưu trữ các khoản chi tiêu của người dùng

| Cột | Kiểu dữ liệu | Ràng buộc | Mô tả |
|-----|-------------|-----------|-------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | ID duy nhất của chi tiêu |
| `user_id` | INT | NOT NULL, FOREIGN KEY | ID người dùng sở hữu chi tiêu |
| `category_id` | INT | NOT NULL, FOREIGN KEY | ID danh mục của chi tiêu |
| `amount` | DECIMAL(10,2) | NOT NULL | Số tiền (tối đa 99,999,999.99) |
| `description` | TEXT | NULL | Mô tả chi tiết (tùy chọn) |
| `expense_date` | DATE | NOT NULL | Ngày chi tiêu |
| `created_at` | DATETIME | DEFAULT CURRENT_TIMESTAMP | Thời gian tạo bản ghi |

**Indexes:**
- PRIMARY KEY trên `id`
- INDEX `idx_user_id` trên `user_id` (để tối ưu truy vấn theo user)
- INDEX `idx_category_id` trên `category_id` (để tối ưu truy vấn theo danh mục)
- INDEX `idx_expense_date` trên `expense_date` (để tối ưu truy vấn theo ngày)
- FOREIGN KEY `user_id` REFERENCES `users(id)` ON DELETE CASCADE
- FOREIGN KEY `category_id` REFERENCES `categories(id)` ON DELETE CASCADE

**Quan hệ:**
- N expenses thuộc về 1 user (N:1)
- N expenses thuộc về 1 category (N:1)

**Ràng buộc:**
- Khi xóa user, tất cả expenses của user đó cũng bị xóa (CASCADE)
- Khi xóa category, tất cả expenses của category đó cũng bị xóa (CASCADE)
- `amount` phải > 0

---

## Quan Hệ Giữa Các Bảng

### 1. Users ↔ Categories (1:N)
- **Một** người dùng có thể có **nhiều** danh mục
- Mỗi danh mục chỉ thuộc về **một** người dùng
- **Foreign Key**: `categories.user_id` → `users.id`
- **CASCADE**: Khi xóa user, tất cả categories của user đó cũng bị xóa

### 2. Users ↔ Expenses (1:N)
- **Một** người dùng có thể có **nhiều** chi tiêu
- Mỗi chi tiêu chỉ thuộc về **một** người dùng
- **Foreign Key**: `expenses.user_id` → `users.id`
- **CASCADE**: Khi xóa user, tất cả expenses của user đó cũng bị xóa

### 3. Categories ↔ Expenses (1:N)
- **Một** danh mục có thể có **nhiều** chi tiêu
- Mỗi chi tiêu chỉ thuộc về **một** danh mục
- **Foreign Key**: `expenses.category_id` → `categories.id`
- **CASCADE**: Khi xóa category, tất cả expenses của category đó cũng bị xóa

---

## Tối Ưu Hóa Database

### Indexes
1. **Primary Keys**: Tự động tạo index cho tất cả PRIMARY KEY
2. **Foreign Keys**: Tự động tạo index cho tất cả FOREIGN KEY
3. **Custom Indexes**:
   - `idx_user_id` trên `categories` và `expenses` (truy vấn theo user thường xuyên)
   - `idx_category_id` trên `expenses` (truy vấn theo category)
   - `idx_expense_date` trên `expenses` (truy vấn theo ngày, dùng cho báo cáo)

### Normalization
- Database đã được chuẩn hóa ở dạng **3NF (Third Normal Form)**
- Không có dữ liệu trùng lặp
- Mỗi bảng có một mục đích rõ ràng
- Foreign keys đảm bảo tính toàn vẹn dữ liệu

### Constraints
- **NOT NULL**: Đảm bảo dữ liệu bắt buộc không được để trống
- **UNIQUE**: Đảm bảo username và email không trùng lặp
- **FOREIGN KEY**: Đảm bảo tính toàn vẹn tham chiếu
- **CASCADE**: Tự động xóa dữ liệu liên quan khi xóa parent record

---

## Migration và Schema

File `database/schema.sql` chứa:
- Tạo database với charset UTF-8
- Tạo tất cả các bảng với đầy đủ constraints
- Tạo tất cả các indexes
- Thiết lập ENGINE=InnoDB (hỗ trợ transactions và foreign keys)

---

## Bảo Mật Database

1. **Prepared Statements**: Tất cả queries sử dụng prepared statements để chống SQL Injection
2. **Password Hashing**: Mật khẩu được hash bằng `password_hash()` (bcrypt)
3. **Input Validation**: Tất cả input được validate trước khi lưu vào database
4. **Access Control**: Mỗi user chỉ có thể truy cập dữ liệu của chính mình (kiểm tra `user_id`)

