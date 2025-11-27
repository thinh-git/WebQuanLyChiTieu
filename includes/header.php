<?php
require_once __DIR__ . '/../config/path.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/session.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ứng dụng web quản lý chi tiêu cá nhân hiện đại, dễ sử dụng. Theo dõi thu chi, phân tích chi tiêu theo danh mục, thống kê báo cáo trực quan với biểu đồ.">
    <meta name="keywords" content="quản lý chi tiêu, quản lý tài chính, theo dõi thu chi, quản lý ngân sách, expense tracker, personal finance">
    <meta name="author" content="Web Quản Lý Chi Tiêu">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Quản Lý Chi Tiêu">
    <meta property="og:description" content="Ứng dụng web quản lý chi tiêu cá nhân hiện đại, dễ sử dụng">
    <meta property="og:locale" content="vi_VN">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Quản Lý Chi Tiêu">
    <meta name="twitter:description" content="Ứng dụng web quản lý chi tiêu cá nhân hiện đại, dễ sử dụng">
    
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Quản Lý Chi Tiêu</title>
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <script>
        // Make base path available to JavaScript
        window.BASE_PATH = '<?php echo BASE_PATH; ?>';
    </script>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>💰 Quản Lý Chi Tiêu</h1>
            </div>
            <ul class="nav-menu">
                <li><a href="<?php echo base_url('dashboard.php'); ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
                <li><a href="<?php echo base_url('add_expense.php'); ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_expense.php' ? 'active' : ''; ?>"><i class="fas fa-plus"></i> Thêm giao dịch</a></li>
                <li><a href="<?php echo base_url('categories.php'); ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>"><i class="fas fa-tags"></i> Danh mục</a></li>
                <li><a href="<?php echo base_url('budgets.php'); ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == 'budgets.php' ? 'active' : ''; ?>"><i class="fas fa-piggy-bank"></i> Ngân sách</a></li>
                <li><a href="<?php echo base_url('reports.php'); ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>"><i class="fas fa-chart-bar"></i> Thống kê</a></li>
                <li><a href="<?php echo base_url('api/auth.php?action=logout'); ?>" class="logout-btn">Đăng xuất (<?php echo htmlspecialchars(getCurrentUsername()); ?>)</a></li>
            </ul>
        </div>
    </nav>
    <main class="main-content">

