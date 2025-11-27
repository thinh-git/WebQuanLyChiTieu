<?php
// Suppress errors and warnings to prevent HTML output
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start output buffering to catch any unexpected output
ob_start();

try {
    require_once __DIR__ . '/../config/path.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/session.php';
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    exit();
}

// Clear any output that might have been generated
ob_end_clean();

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'register':
        handleRegister();
        break;
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function handleRegister() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
        return;
    }

    if (strlen($username) < 3 || strlen($username) > 50) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tên đăng nhập phải từ 3-50 ký tự']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email không hợp lệ']);
        return;
    }

    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự']);
        return;
    }

    try {
        $conn = getDBConnection();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
        return;
    }

    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc email đã tồn tại']);
        return;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashedPassword);

    if ($stmt->execute()) {
        $userId = $conn->insert_id;
        
        // Create default categories
        $defaultCategories = [
            ['name' => 'Lương', 'icon' => '💰', 'color' => '#10B981', 'type' => 'income'],
            ['name' => 'Thưởng', 'icon' => '🎁', 'color' => '#10B981', 'type' => 'income'],
            ['name' => 'Ăn uống', 'icon' => '🍔', 'color' => '#EF4444', 'type' => 'expense'],
            ['name' => 'Giao thông', 'icon' => '🚗', 'color' => '#3B82F6', 'type' => 'expense'],
            ['name' => 'Mua sắm', 'icon' => '🛍️', 'color' => '#9b59b6', 'type' => 'expense'],
            ['name' => 'Giải trí', 'icon' => '🎬', 'color' => '#F59E0B', 'type' => 'expense'],
            ['name' => 'Y tế', 'icon' => '🏥', 'color' => '#1abc9c', 'type' => 'expense'],
            ['name' => 'Hóa đơn', 'icon' => '💡', 'color' => '#e67e22', 'type' => 'expense'],
            ['name' => 'Khác', 'icon' => '📦', 'color' => '#95a5a6', 'type' => 'both']
        ];

        // Check if category_type column exists
        $checkColumn = $conn->query("SHOW COLUMNS FROM categories LIKE 'category_type'");
        $hasCategoryType = $checkColumn->num_rows > 0;
        
        if ($hasCategoryType) {
            $catStmt = $conn->prepare("INSERT INTO categories (user_id, name, category_type, icon, color) VALUES (?, ?, ?, ?, ?)");
            foreach ($defaultCategories as $cat) {
                $catStmt->bind_param("issss", $userId, $cat['name'], $cat['type'], $cat['icon'], $cat['color']);
                $catStmt->execute();
            }
        } else {
            $catStmt = $conn->prepare("INSERT INTO categories (user_id, name, icon, color) VALUES (?, ?, ?, ?)");
            foreach ($defaultCategories as $cat) {
                $catStmt->bind_param("isss", $userId, $cat['name'], $cat['icon'], $cat['color']);
                $catStmt->execute();
            }
        }
        $catStmt->close();

        $stmt->close();
        closeDBConnection($conn);
        
        echo json_encode(['success' => true, 'message' => 'Đăng ký thành công']);
    } else {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại']);
    }
}

function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
        return;
    }

    try {
        $conn = getDBConnection();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
        return;
    }

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng']);
        return;
    }

    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        $stmt->close();
        closeDBConnection($conn);
        
        echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công', 'redirect' => base_url('dashboard.php')]);
    } else {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng']);
    }
}

function handleLogout() {
    session_start();
    session_unset();
    session_destroy();
    header('Location: ' . base_url('index.php'));
    exit();
}
?>

