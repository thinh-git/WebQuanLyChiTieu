<?php
// Suppress errors and warnings to prevent HTML output
error_reporting(E_ALL);
ini_set('display_errors', 0);
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

ob_end_clean();

header('Content-Type: application/json');
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
$userId = getCurrentUserId();

switch ($method) {
    case 'GET':
        handleGetCategories($userId);
        break;
    case 'POST':
        handleCreateCategory($userId);
        break;
    case 'PUT':
        handleUpdateCategory($userId);
        break;
    case 'DELETE':
        handleDeleteCategory($userId);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

function handleGetCategories($userId) {
    try {
        $conn = getDBConnection();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
        exit();
    }

    $stmt = $conn->prepare("SELECT c.*, COUNT(e.id) as expense_count 
                           FROM categories c 
                           LEFT JOIN expenses e ON c.id = e.category_id 
                           WHERE c.user_id = ? 
                           GROUP BY c.id 
                           ORDER BY c.name ASC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }

    $stmt->close();
    closeDBConnection($conn);

    echo json_encode(['success' => true, 'data' => $categories]);
}

function handleCreateCategory($userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        $data = $_POST;
    }

    $name = trim($data['name'] ?? '');
    $icon = trim($data['icon'] ?? '💰');
    $color = trim($data['color'] ?? '#3498db');

    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tên danh mục không được để trống']);
        return;
    }

    try {
        $conn = getDBConnection();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
        exit();
    }

    // Check if category name already exists for this user
    $stmt = $conn->prepare("SELECT id FROM categories WHERE user_id = ? AND name = ?");
    $stmt->bind_param("is", $userId, $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tên danh mục đã tồn tại']);
        return;
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO categories (user_id, name, icon, color) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $name, $icon, $color);

    if ($stmt->execute()) {
        $categoryId = $conn->insert_id;
        $stmt->close();
        closeDBConnection($conn);
        echo json_encode(['success' => true, 'message' => 'Thêm danh mục thành công', 'id' => $categoryId]);
    } else {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại']);
    }
}

function handleUpdateCategory($userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        parse_str(file_get_contents('php://input'), $data);
    }

    $categoryId = intval($data['id'] ?? 0);
    $name = trim($data['name'] ?? '');
    $icon = trim($data['icon'] ?? '💰');
    $color = trim($data['color'] ?? '#3498db');

    if ($categoryId <= 0 || empty($name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
        return;
    }

    try {
        $conn = getDBConnection();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
        exit();
    }

    // Verify category belongs to user
    $stmt = $conn->prepare("SELECT id FROM categories WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $categoryId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Danh mục không tồn tại']);
        return;
    }
    $stmt->close();

    // Check if new name conflicts with existing category
    $stmt = $conn->prepare("SELECT id FROM categories WHERE user_id = ? AND name = ? AND id != ?");
    $stmt->bind_param("isi", $userId, $name, $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tên danh mục đã tồn tại']);
        return;
    }
    $stmt->close();

    $stmt = $conn->prepare("UPDATE categories SET name = ?, icon = ?, color = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sssii", $name, $icon, $color, $categoryId, $userId);

    if ($stmt->execute()) {
        $stmt->close();
        closeDBConnection($conn);
        echo json_encode(['success' => true, 'message' => 'Cập nhật danh mục thành công']);
    } else {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại']);
    }
}

function handleDeleteCategory($userId) {
    $categoryId = intval($_GET['id'] ?? 0);

    if ($categoryId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
        return;
    }

    try {
        $conn = getDBConnection();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
        exit();
    }

    // Check if category has expenses
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM expenses WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();

    if ($count > 0) {
        closeDBConnection($conn);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Không thể xóa danh mục đang có chi tiêu']);
        return;
    }

    // Verify category belongs to user
    $stmt = $conn->prepare("SELECT id FROM categories WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $categoryId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Danh mục không tồn tại']);
        return;
    }
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $categoryId, $userId);

    if ($stmt->execute()) {
        $stmt->close();
        closeDBConnection($conn);
        echo json_encode(['success' => true, 'message' => 'Xóa danh mục thành công']);
    } else {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại']);
    }
}
?>

