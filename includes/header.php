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
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="nav-brand">
                <a href="<?php echo base_url('dashboard.php'); ?>" class="brand-link">
                    <div class="brand-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="brand-text">
                        <span class="brand-title">Quản Lý Chi Tiêu</span>
                        <span class="brand-subtitle">Personal Finance</span>
                    </div>
                </a>
            </div>
            
            <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <div class="nav-menu-wrapper" id="navMenuWrapper">
                <ul class="nav-menu">
                    <li>
                        <a href="<?php echo base_url('dashboard.php'); ?>" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Trang chủ</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo base_url('add_expense.php'); ?>" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'add_expense.php' ? 'active' : ''; ?>">
                            <i class="fas fa-plus-circle"></i>
                            <span>Thêm giao dịch</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo base_url('categories.php'); ?>" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                            <i class="fas fa-tags"></i>
                            <span>Danh mục</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo base_url('budgets.php'); ?>" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'budgets.php' ? 'active' : ''; ?>">
                            <i class="fas fa-piggy-bank"></i>
                            <span>Ngân sách</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo base_url('reports.php'); ?>" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-line"></i>
                            <span>Thống kê</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-user">
                    <div class="user-dropdown">
                        <button class="user-toggle" id="userToggle" aria-label="User menu">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="user-name"><?php echo htmlspecialchars(getCurrentUsername()); ?></span>
                            <i class="fas fa-chevron-down dropdown-icon"></i>
                        </button>
                        <div class="user-dropdown-menu" id="userDropdownMenu">
                            <div class="user-info">
                                <div class="user-avatar-large">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="user-details">
                                    <strong><?php echo htmlspecialchars(getCurrentUsername()); ?></strong>
                                    <span>Người dùng</span>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="<?php echo base_url('dashboard.php'); ?>" class="dropdown-item">
                                <i class="fas fa-home"></i>
                                <span>Trang chủ</span>
                            </a>
                            <a href="<?php echo base_url('api/auth.php?action=logout'); ?>" class="dropdown-item logout-item">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Đăng xuất</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <main class="main-content">

