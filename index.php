<?php
require_once __DIR__ . '/config/path.php';
require_once __DIR__ . '/includes/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . base_url('dashboard.php'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Đăng nhập vào hệ thống quản lý chi tiêu cá nhân">
    <meta name="keywords" content="đăng nhập, quản lý chi tiêu, expense tracker">
    <meta name="robots" content="noindex, nofollow">
    <title>Đăng nhập - Quản Lý Chi Tiêu</title>
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
    <script>
        // Make base path available to JavaScript
        window.BASE_PATH = '<?php echo BASE_PATH; ?>';
    </script>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <h1>💰 Quản Lý Chi Tiêu</h1>
            <h2>Đăng nhập</h2>
            
            <form id="loginForm" class="auth-form">
                <div class="form-group">
                    <label for="username">Tên đăng nhập hoặc Email</label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                
                <div id="errorMessage" class="error-message" style="display: none;"></div>
                
                <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
            </form>
            
            <p class="auth-link">
                Chưa có tài khoản? <a href="<?php echo base_url('register.php'); ?>">Đăng ký ngay</a>
            </p>
        </div>
    </div>
    
    <script src="<?php echo base_url('assets/js/auth.js'); ?>"></script>
</body>
</html>

