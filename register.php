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
    <title>Đăng ký - Quản Lý Chi Tiêu</title>
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
            <h2>Đăng ký</h2>
            
            <form id="registerForm" class="auth-form">
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" id="username" name="username" required autocomplete="username" minlength="3" maxlength="50">
                    <small>Từ 3-50 ký tự</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required autocomplete="email">
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password" minlength="6">
                    <small>Ít nhất 6 ký tự</small>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Xác nhận mật khẩu</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required autocomplete="new-password">
                </div>
                
                <div id="errorMessage" class="error-message" style="display: none;"></div>
                <div id="successMessage" class="success-message" style="display: none;"></div>
                
                <button type="submit" class="btn btn-primary btn-block">Đăng ký</button>
            </form>
            
            <p class="auth-link">
                Đã có tài khoản? <a href="<?php echo base_url('index.php'); ?>">Đăng nhập</a>
            </p>
        </div>
    </div>
    
    <script src="<?php echo base_url('assets/js/auth.js'); ?>"></script>
</body>
</html>

