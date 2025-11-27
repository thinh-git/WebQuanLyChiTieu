# Hướng Dẫn Triển Khai (Deployment Guide)

## Mục Lục
1. [Yêu Cầu Hệ Thống](#yêu-cầu-hệ-thống)
2. [Chuẩn Bị](#chuẩn-bị)
3. [Triển Khai Lên Shared Hosting](#triển-khai-lên-shared-hosting)
4. [Triển Khai Lên VPS](#triển-khai-lên-vps)
5. [Cấu Hình Domain](#cấu-hình-domain)
6. [Kiểm Tra Sau Triển Khai](#kiểm-tra-sau-triển-khai)
7. [Tối Ưu Hiệu Suất](#tối-ưu-hiệu-suất)

---

## Yêu Cầu Hệ Thống

### Server Requirements
- **PHP**: 7.4 trở lên (khuyến nghị PHP 8.0+)
- **MySQL**: 5.7 trở lên hoặc MariaDB 10.2+
- **Apache**: 2.4+ với mod_rewrite enabled
- **Disk Space**: Tối thiểu 50MB
- **Memory**: Tối thiểu 128MB PHP memory limit

### PHP Extensions Cần Thiết
- mysqli
- session
- json
- mbstring
- openssl

---

## Chuẩn Bị

### 1. Chuẩn Bị Files
- [x] Tất cả source code
- [x] File `database/schema.sql`
- [x] File `.htaccess`
- [x] File `config/database.php` (sẽ cập nhật sau)

### 2. Backup Database (nếu có)
```sql
mysqldump -u username -p expense_manager > backup.sql
```

### 3. Chuẩn Bị Thông Tin
- Database host, username, password
- Domain name hoặc subdomain
- FTP/SSH credentials

---

## Triển Khai Lên Shared Hosting

### Bước 1: Upload Files
1. Kết nối FTP/SFTP đến hosting
2. Upload tất cả files vào thư mục `public_html` hoặc `www`
3. Đảm bảo cấu trúc thư mục được giữ nguyên

### Bước 2: Tạo Database
1. Đăng nhập vào cPanel
2. Tạo MySQL Database mới:
   - Database name: `expense_manager` (hoặc tên khác)
   - Database user: Tạo user mới
   - Database password: Mật khẩu mạnh
3. Ghi lại thông tin: `host`, `username`, `password`, `database_name`

### Bước 3: Import Database
1. Vào phpMyAdmin trong cPanel
2. Chọn database vừa tạo
3. Import file `database/schema.sql`

### Bước 4: Cấu Hình
1. Sửa file `config/database.php`:
```php
define('DB_HOST', 'localhost'); // hoặc IP của database server
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
define('DB_NAME', 'expense_manager');
```

2. Kiểm tra file `.htaccess` đã được upload

### Bước 5: Phân Quyền
- Đảm bảo thư mục có quyền 755
- Files có quyền 644

---

## Triển Khai Lên VPS

### Bước 1: Cài Đặt LAMP/LEMP Stack

#### Ubuntu/Debian với Apache:
```bash
sudo apt update
sudo apt install apache2 mysql-server php php-mysql libapache2-mod-php
sudo systemctl enable apache2
sudo systemctl start apache2
```

#### Ubuntu/Debian với Nginx:
```bash
sudo apt update
sudo apt install nginx mysql-server php-fpm php-mysql
sudo systemctl enable nginx
sudo systemctl start nginx
```

### Bước 2: Cấu Hình Database
```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE expense_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'expense_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON expense_manager.* TO 'expense_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
mysql -u expense_user -p expense_manager < database/schema.sql
```

### Bước 3: Upload Files
```bash
# Sử dụng SCP hoặc SFTP
scp -r WebQuanLyChiTieu/ user@your-server:/var/www/html/

# Hoặc clone từ Git
cd /var/www/html
git clone your-repo-url WebQuanLyChiTieu
```

### Bước 4: Cấu Hình Apache

Tạo virtual host `/etc/apache2/sites-available/expense-manager.conf`:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/html/WebQuanLyChiTieu
    
    <Directory /var/www/html/WebQuanLyChiTieu>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/expense-manager-error.log
    CustomLog ${APACHE_LOG_DIR}/expense-manager-access.log combined
</VirtualHost>
```

Kích hoạt:
```bash
sudo a2ensite expense-manager.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

### Bước 5: Cấu Hình Nginx

Tạo file `/etc/nginx/sites-available/expense-manager`:
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/html/WebQuanLyChiTieu;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Kích hoạt:
```bash
sudo ln -s /etc/nginx/sites-available/expense-manager /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Bước 6: Cấu Hình PHP
Sửa `/etc/php/8.0/apache2/php.ini` (hoặc `php-fpm/php.ini`):
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
```

### Bước 7: Phân Quyền
```bash
sudo chown -R www-data:www-data /var/www/html/WebQuanLyChiTieu
sudo chmod -R 755 /var/www/html/WebQuanLyChiTieu
sudo chmod -R 644 /var/www/html/WebQuanLyChiTieu/*.php
```

---

## Cấu Hình Domain

### 1. Trỏ Domain về Server
- **A Record**: Trỏ domain về IP của server
- **CNAME**: Trỏ www về domain chính

### 2. SSL Certificate (HTTPS)
Sử dụng Let's Encrypt (miễn phí):
```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

Hoặc với Nginx:
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### 3. Cập Nhật .htaccess
Uncomment dòng force HTTPS trong `.htaccess`:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## Kiểm Tra Sau Triển Khai

### 1. Kiểm Tra Cơ Bản
- [ ] Truy cập trang chủ thành công
- [ ] Đăng ký tài khoản mới
- [ ] Đăng nhập thành công
- [ ] Thêm chi tiêu
- [ ] Xem thống kê

### 2. Kiểm Tra Bảo Mật
- [ ] HTTPS hoạt động (nếu có)
- [ ] Không thể truy cập trực tiếp vào `/config/`
- [ ] Session hoạt động đúng
- [ ] SQL injection được bảo vệ

### 3. Kiểm Tra Hiệu Suất
- [ ] Thời gian tải trang < 3 giây
- [ ] Database queries được tối ưu
- [ ] CSS/JS được cache
- [ ] Images được tối ưu

### 4. Kiểm Tra Responsive
- [ ] Hiển thị đúng trên desktop
- [ ] Hiển thị đúng trên tablet
- [ ] Hiển thị đúng trên mobile

---

## Tối Ưu Hiệu Suất

### 1. Database
- [x] Indexes đã được tạo
- [ ] Kiểm tra slow queries
- [ ] Tối ưu queries phức tạp

### 2. Caching
- [ ] Enable browser caching (đã có trong .htaccess)
- [ ] Cân nhắc sử dụng OPcache cho PHP
- [ ] Cân nhắc Redis/Memcached cho session

### 3. CDN (Tùy chọn)
- Upload static files (CSS, JS, images) lên CDN
- Cập nhật đường dẫn trong code

### 4. Monitoring
- Cài đặt monitoring tool (New Relic, Datadog)
- Thiết lập error logging
- Thiết lập backup tự động

---

## Backup

### Database Backup (Cron Job)
```bash
# Tạo script backup
#!/bin/bash
mysqldump -u expense_user -p'password' expense_manager > /backup/expense_manager_$(date +%Y%m%d).sql

# Thêm vào crontab (chạy mỗi ngày lúc 2h sáng)
0 2 * * * /path/to/backup-script.sh
```

### Files Backup
```bash
# Backup toàn bộ files
tar -czf /backup/files_$(date +%Y%m%d).tar.gz /var/www/html/WebQuanLyChiTieu
```

---

## Troubleshooting

### Lỗi 500 Internal Server Error
- Kiểm tra file `.htaccess`
- Kiểm tra PHP error logs
- Kiểm tra phân quyền files

### Lỗi Kết Nối Database
- Kiểm tra thông tin trong `config/database.php`
- Kiểm tra firewall
- Kiểm tra database user có quyền truy cập

### Lỗi Session
- Kiểm tra thư mục session có quyền ghi
- Kiểm tra `session.save_path` trong php.ini

---

## Liên Hệ Hỗ Trợ

Nếu gặp vấn đề trong quá trình triển khai, vui lòng:
1. Kiểm tra logs (Apache/Nginx error logs, PHP error logs)
2. Kiểm tra file README.md và các tài liệu khác
3. Liên hệ support của hosting provider

