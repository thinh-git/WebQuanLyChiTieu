# Sơ đồ Trang Web (Sitemap)

## Cấu trúc Website Quản Lý Chi Tiêu

```
Web Quản Lý Chi Tiêu
│
├── Trang Công Khai (Public)
│   ├── /index.php (Trang đăng nhập)
│   └── /register.php (Trang đăng ký)
│
└── Trang Nội Bộ (Protected - Yêu cầu đăng nhập)
    ├── /dashboard.php (Trang chủ - Danh sách chi tiêu)
    ├── /add_expense.php (Thêm chi tiêu mới)
    ├── /categories.php (Quản lý danh mục)
    └── /reports.php (Thống kê và báo cáo)
    
    └── API Endpoints (/api/)
        ├── /api/auth.php (Xác thực: đăng ký, đăng nhập, đăng xuất)
        ├── /api/categories.php (CRUD danh mục)
        ├── /api/expenses.php (CRUD chi tiêu)
        └── /api/reports.php (Báo cáo và thống kê)
```

## Mô tả các Trang

### 1. Trang Đăng Nhập (/index.php)
- **Mục đích**: Xác thực người dùng
- **Chức năng**: 
  - Đăng nhập bằng username/email và mật khẩu
  - Chuyển hướng đến dashboard nếu đã đăng nhập
  - Link đến trang đăng ký

### 2. Trang Đăng Ký (/register.php)
- **Mục đích**: Tạo tài khoản mới
- **Chức năng**:
  - Đăng ký với username, email, mật khẩu
  - Validation phía client và server
  - Tự động tạo danh mục mặc định khi đăng ký thành công

### 3. Dashboard (/dashboard.php)
- **Mục đích**: Trang chủ hiển thị danh sách chi tiêu
- **Chức năng**:
  - Hiển thị danh sách chi tiêu với phân trang
  - Tìm kiếm và lọc chi tiêu (theo danh mục, ngày)
  - Thống kê tổng chi tiêu tháng này
  - Sửa và xóa chi tiêu
  - Thêm chi tiêu mới

### 4. Thêm Chi Tiêu (/add_expense.php)
- **Mục đích**: Form thêm chi tiêu mới
- **Chức năng**:
  - Nhập số tiền, danh mục, mô tả, ngày
  - Validation đầy đủ
  - Chuyển hướng về dashboard sau khi thêm thành công

### 5. Quản Lý Danh Mục (/categories.php)
- **Mục đích**: Quản lý danh mục chi tiêu
- **Chức năng**:
  - Xem danh sách danh mục
  - Thêm danh mục mới (tên, icon, màu sắc)
  - Sửa danh mục
  - Xóa danh mục (chỉ khi không có chi tiêu)

### 6. Thống Kê và Báo Cáo (/reports.php)
- **Mục đích**: Phân tích và thống kê chi tiêu
- **Chức năng**:
  - Biểu đồ chi tiêu theo danh mục (Pie Chart)
  - Biểu đồ chi tiêu theo ngày trong tháng (Line Chart)
  - Biểu đồ xu hướng 6 tháng gần nhất (Line Chart)
  - Top 5 danh mục chi tiêu nhiều nhất
  - Tổng hợp số liệu (tổng chi tiêu, số giao dịch, trung bình/ngày)

## Luồng Người Dùng

### Người dùng mới:
1. Truy cập `/index.php` → Đăng ký tại `/register.php`
2. Sau khi đăng ký → Tự động chuyển đến `/index.php` để đăng nhập
3. Sau khi đăng nhập → Chuyển đến `/dashboard.php`

### Người dùng đã có tài khoản:
1. Truy cập `/index.php` → Đăng nhập
2. Sau khi đăng nhập → `/dashboard.php`
3. Có thể:
   - Xem danh sách chi tiêu tại dashboard
   - Thêm chi tiêu mới tại `/add_expense.php`
   - Quản lý danh mục tại `/categories.php`
   - Xem thống kê tại `/reports.php`
   - Đăng xuất từ menu

## Bảo Mật

- Tất cả các trang nội bộ (dashboard, add_expense, categories, reports) yêu cầu đăng nhập
- Nếu chưa đăng nhập, tự động chuyển hướng về `/index.php`
- Mỗi người dùng chỉ xem/sửa dữ liệu của chính mình
- Session được quản lý an toàn

## Responsive Design

- Tất cả các trang đều responsive
- Hỗ trợ mobile, tablet, desktop
- Navigation menu tự động điều chỉnh trên màn hình nhỏ

