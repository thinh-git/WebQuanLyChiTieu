# Web Quản Lý Chi Tiêu

Ứng dụng web quản lý chi tiêu đầy đủ chức năng được xây dựng với PHP và MySQL.

## 📋 Mục Lục

1. [Giới thiệu](#giới-thiệu)
2. [Mục tiêu và Đối tượng sử dụng](#mục-tiêu-và-đối-tượng-sử-dụng)
3. [Sơ đồ trang web (Sitemap)](#sơ-đồ-trang-web-sitemap)
4. [Công nghệ sử dụng](#công-nghệ-sử-dụng)
5. [Kế hoạch thực hiện](#kế-hoạch-thực-hiện)
6. [Tính năng](#tính-năng)
7. [Cài đặt](#cài-đặt)
8. [Cấu trúc dự án](#cấu-trúc-dự-án)
9. [Bảo mật](#bảo-mật)

## 🎯 Giới thiệu

Web Quản Lý Chi Tiêu là một ứng dụng web hiện đại giúp người dùng theo dõi và quản lý chi tiêu cá nhân một cách hiệu quả. Ứng dụng cung cấp các công cụ phân tích và thống kê trực quan để người dùng có cái nhìn tổng quan về tình hình tài chính của mình.

## 🎯 Mục tiêu và Đối tượng sử dụng

### Mục tiêu
- **Mục tiêu chính**: Xây dựng một hệ thống quản lý chi tiêu cá nhân dễ sử dụng, an toàn và hiệu quả
- **Mục tiêu phụ**: 
  - Cung cấp công cụ phân tích chi tiêu trực quan
  - Hỗ trợ người dùng quản lý ngân sách tốt hơn
  - Tạo giao diện thân thiện, responsive trên mọi thiết bị

### Đối tượng sử dụng
- **Người dùng cá nhân**: Muốn theo dõi chi tiêu hàng ngày
- **Gia đình**: Quản lý chi tiêu chung
- **Sinh viên**: Quản lý ngân sách học tập và sinh hoạt
- **Người làm việc tự do**: Theo dõi thu chi cá nhân

## 🗺️ Sơ đồ trang web (Sitemap)

Xem chi tiết tại file [SITEMAP.md](SITEMAP.md)

### Tóm tắt:
```
Trang Công Khai:
├── /index.php (Đăng nhập)
└── /register.php (Đăng ký)

Trang Nội Bộ (Yêu cầu đăng nhập):
├── /dashboard.php (Trang chủ)
├── /add_expense.php (Thêm chi tiêu)
├── /categories.php (Quản lý danh mục)
└── /reports.php (Thống kê)

API Endpoints:
├── /api/auth.php
├── /api/categories.php
├── /api/expenses.php
└── /api/reports.php
```

## 🛠️ Công nghệ sử dụng

### Frontend
- **HTML5**: Cấu trúc semantic, accessible
- **CSS3**: 
  - Flexbox và Grid Layout
  - CSS Variables cho theme
  - Responsive Design với Media Queries
  - Animations và Transitions
- **JavaScript (ES6+)**:
  - Async/Await cho API calls
  - DOM Manipulation
  - Event Handling
  - Form Validation
- **Chart.js**: Thư viện vẽ biểu đồ trực quan

### Backend
- **PHP 7.4+**: 
  - Object-oriented programming
  - Session management
  - Error handling
- **MySQL 5.7+**: 
  - Relational database
  - Foreign keys và constraints
  - Indexes để tối ưu performance

### Framework và Thư viện
- **Không sử dụng framework**: Xây dựng từ đầu để hiểu rõ cơ chế hoạt động
- **Chart.js**: Thư viện JavaScript cho biểu đồ

### Hosting và Domain
- **Local Development**: XAMPP (Apache + MySQL + PHP)
- **Production**: Có thể deploy lên:
  - Shared hosting (cPanel)
  - VPS (Linux + Apache/Nginx)
  - Cloud hosting (AWS, Google Cloud, Azure)

## 📅 Kế hoạch thực hiện

### Giai đoạn 1: Phân tích và Thiết kế (Tuần 1-2)
- [x] Xác định yêu cầu và mục tiêu
- [x] Thiết kế sơ đồ database (ERD)
- [x] Thiết kế sơ đồ trang web (Sitemap)
- [x] Lựa chọn công nghệ

### Giai đoạn 2: Xây dựng Backend (Tuần 3-4)
- [x] Tạo database schema
- [x] Xây dựng API endpoints
- [x] Implement authentication và authorization
- [x] Xử lý bảo mật (SQL injection, XSS)

### Giai đoạn 3: Xây dựng Frontend (Tuần 5-6)
- [x] Thiết kế giao diện (HTML/CSS)
- [x] Implement responsive design
- [x] Xây dựng JavaScript cho tương tác
- [x] Tích hợp Chart.js cho biểu đồ

### Giai đoạn 4: Tối ưu và Kiểm thử (Tuần 7)
- [x] Tối ưu database queries
- [x] Kiểm thử chức năng
- [x] Kiểm tra responsive trên nhiều thiết bị
- [x] Tối ưu SEO

### Giai đoạn 5: Triển khai (Tuần 8)
- [ ] Deploy lên hosting
- [ ] Kiểm tra hiệu suất production
- [ ] Tài liệu hướng dẫn sử dụng

## Tính năng

- ✅ Đăng ký và đăng nhập người dùng
- ✅ Quản lý chi tiêu (thêm, sửa, xóa)
- ✅ Quản lý danh mục chi tiêu
- ✅ Tìm kiếm và lọc chi tiêu
- ✅ Thống kê và báo cáo với biểu đồ
- ✅ Giao diện responsive, thân thiện với người dùng

## Yêu cầu hệ thống

- PHP 7.4 trở lên
- MySQL 5.7 trở lên (hoặc MariaDB)
- Web server (Apache/Nginx) với mod_rewrite (tùy chọn)
- Trình duyệt hiện đại hỗ trợ ES6+

## Cài đặt

### 1. Clone repository

```bash
git clone <repository-url>
cd WebQuanLyChiTieu
```

### 2. Cấu hình database

Mở file `config/database.php` và cập nhật thông tin kết nối:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'expense_manager');
```

### 3. Tạo database

Import file `database/schema.sql` vào MySQL:

```bash
mysql -u root -p < database/schema.sql
```

Hoặc sử dụng phpMyAdmin:
- Tạo database mới tên `expense_manager`
- Import file `database/schema.sql`

### 4. Cấu hình web server

#### Apache

Đảm bảo mod_rewrite được bật và tạo file `.htaccess` trong thư mục gốc:

```apache
RewriteEngine On
RewriteBase /

# Redirect to index.php if file doesn't exist
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Nginx

Thêm cấu hình sau vào server block:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 5. Phân quyền thư mục

Đảm bảo web server có quyền đọc/ghi vào thư mục:

```bash
chmod -R 755 .
```

## Sử dụng

1. Truy cập ứng dụng qua trình duyệt: `http://localhost/WebQuanLyChiTieu`
2. Đăng ký tài khoản mới
3. Đăng nhập và bắt đầu quản lý chi tiêu

## Cấu trúc dự án

```
WebQuanLyChiTieu/
├── api/                  # API endpoints
│   ├── auth.php          # Xác thực (login, register, logout)
│   ├── categories.php    # CRUD danh mục
│   ├── expenses.php      # CRUD chi tiêu
│   └── reports.php       # Báo cáo và thống kê
├── assets/               # Tài nguyên tĩnh
│   ├── css/
│   │   └── style.css     # Stylesheet chính
│   ├── js/
│   │   ├── auth.js       # Xử lý đăng nhập/đăng ký
│   │   ├── expenses.js    # Xử lý chi tiêu
│   │   ├── charts.js     # Xử lý biểu đồ
│   │   └── main.js       # Utilities và categories
│   └── images/           # Hình ảnh
├── config/               # Cấu hình
│   ├── database.php      # Cấu hình database
│   └── path.php          # Cấu hình base path
├── database/             # Database
│   └── schema.sql        # Schema và migration
├── includes/             # Components tái sử dụng
│   ├── header.php        # Header chung
│   ├── footer.php        # Footer chung
│   └── session.php      # Quản lý session
├── index.php             # Trang đăng nhập
├── register.php          # Trang đăng ký
├── dashboard.php         # Trang chủ (danh sách chi tiêu)
├── add_expense.php       # Thêm chi tiêu mới
├── categories.php        # Quản lý danh mục
├── reports.php           # Thống kê và báo cáo
├── README.md             # Tài liệu chính
├── SITEMAP.md            # Sơ đồ trang web
└── ERD.md                # Sơ đồ cơ sở dữ liệu
```

## Bảo mật

- Mật khẩu được hash bằng `password_hash()`
- Sử dụng prepared statements để chống SQL injection
- Session-based authentication
- Kiểm tra quyền truy cập (user chỉ xem/sửa dữ liệu của mình)
- Validate input phía server và client

## Công nghệ sử dụng

- **Frontend:** HTML5, CSS3, JavaScript (ES6+), Chart.js
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Security:** Password hashing, Prepared statements, Session management

## License

MIT License

## Tác giả

Web Quản Lý Chi Tiêu - Dự án học tập
