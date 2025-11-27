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
        handleGetExpenses($userId);
        break;
    case 'POST':
        handleCreateExpense($userId);
        break;
    case 'PUT':
        handleUpdateExpense($userId);
        break;
    case 'DELETE':
        handleDeleteExpense($userId);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

function handleGetExpenses($userId) {
    try {
        $conn = getDBConnection();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
        exit();
    }
    
    $categoryId = $_GET['category_id'] ?? null;
    $type = $_GET['type'] ?? null; // Filter by income/expense
    $startDate = $_GET['start_date'] ?? null;
    $endDate = $_GET['end_date'] ?? null;
    $search = $_GET['search'] ?? null;
    $page = intval($_GET['page'] ?? 1);
    $limit = intval($_GET['limit'] ?? 20);
    $offset = ($page - 1) * $limit;

    $query = "SELECT e.*, c.name as category_name, c.icon as category_icon, c.color as category_color 
              FROM expenses e 
              JOIN categories c ON e.category_id = c.id 
              WHERE e.user_id = ?";
    $params = [$userId];
    $types = "i";

    if ($categoryId) {
        $query .= " AND e.category_id = ?";
        $params[] = $categoryId;
        $types .= "i";
    }
    
    if ($type && in_array($type, ['income', 'expense'])) {
        $query .= " AND e.type = ?";
        $params[] = $type;
        $types .= "s";
    }

    if ($startDate) {
        $query .= " AND e.expense_date >= ?";
        $params[] = $startDate;
        $types .= "s";
    }

    if ($endDate) {
        $query .= " AND e.expense_date <= ?";
        $params[] = $endDate;
        $types .= "s";
    }

    if ($search) {
        $query .= " AND (e.description LIKE ? OR c.name LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "ss";
    }

    $query .= " ORDER BY e.expense_date DESC, e.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $expenses = [];
    while ($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }

    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM expenses e 
                   JOIN categories c ON e.category_id = c.id 
                   WHERE e.user_id = ?";
    $countParams = [$userId];
    $countTypes = "i";

    if ($categoryId) {
        $countQuery .= " AND e.category_id = ?";
        $countParams[] = $categoryId;
        $countTypes .= "i";
    }
    
    if ($type && in_array($type, ['income', 'expense'])) {
        $countQuery .= " AND e.type = ?";
        $countParams[] = $type;
        $countTypes .= "s";
    }

    if ($startDate) {
        $countQuery .= " AND e.expense_date >= ?";
        $countParams[] = $startDate;
        $countTypes .= "s";
    }

    if ($endDate) {
        $countQuery .= " AND e.expense_date <= ?";
        $countParams[] = $endDate;
        $countTypes .= "s";
    }

    if ($search) {
        $countQuery .= " AND (e.description LIKE ? OR c.name LIKE ?)";
        $searchParam = "%$search%";
        $countParams[] = $searchParam;
        $countParams[] = $searchParam;
        $countTypes .= "ss";
    }

    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param($countTypes, ...$countParams);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $total = $countResult->fetch_assoc()['total'];

    $stmt->close();
    $countStmt->close();
    closeDBConnection($conn);

    echo json_encode([
        'success' => true,
        'data' => $expenses,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

function handleCreateExpense($userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        $data = $_POST;
    }

    $categoryId = intval($data['category_id'] ?? 0);
    $amount = floatval($data['amount'] ?? 0);
    $type = $data['type'] ?? 'expense';
    $description = trim($data['description'] ?? '');
    $expenseDate = $data['expense_date'] ?? date('Y-m-d');

    if ($categoryId <= 0 || $amount <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
        return;
    }
    
    if (!in_array($type, ['income', 'expense'])) {
        $type = 'expense';
    }

    // Verify category belongs to user
    try {
        $conn = getDBConnection();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
        exit();
    }
    $stmt = $conn->prepare("SELECT id FROM categories WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $categoryId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Danh mục không hợp lệ']);
        return;
    }

    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO expenses (user_id, category_id, amount, type, description, expense_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iidsss", $userId, $categoryId, $amount, $type, $description, $expenseDate);

    if ($stmt->execute()) {
        $expenseId = $conn->insert_id;
        $stmt->close();
        closeDBConnection($conn);
        echo json_encode(['success' => true, 'message' => 'Thêm chi tiêu thành công', 'id' => $expenseId]);
    } else {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại']);
    }
}

function handleUpdateExpense($userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        parse_str(file_get_contents('php://input'), $data);
    }

    $expenseId = intval($data['id'] ?? 0);
    $categoryId = intval($data['category_id'] ?? 0);
    $amount = floatval($data['amount'] ?? 0);
    $description = trim($data['description'] ?? '');
    $expenseDate = $data['expense_date'] ?? date('Y-m-d');

    if ($expenseId <= 0 || $categoryId <= 0 || $amount <= 0) {
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

    // Verify expense belongs to user
    $stmt = $conn->prepare("SELECT id FROM expenses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $expenseId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Chi tiêu không tồn tại']);
        return;
    }
    $stmt->close();

    // Verify category belongs to user
    $stmt = $conn->prepare("SELECT id FROM categories WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $categoryId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Danh mục không hợp lệ']);
        return;
    }
    $stmt->close();

    $stmt = $conn->prepare("UPDATE expenses SET category_id = ?, amount = ?, type = ?, description = ?, expense_date = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("idsssii", $categoryId, $amount, $type, $description, $expenseDate, $expenseId, $userId);

    if ($stmt->execute()) {
        $stmt->close();
        closeDBConnection($conn);
        echo json_encode(['success' => true, 'message' => 'Cập nhật chi tiêu thành công']);
    } else {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại']);
    }
}

function handleDeleteExpense($userId) {
    $expenseId = intval($_GET['id'] ?? 0);

    if ($expenseId <= 0) {
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

    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $expenseId, $userId);

    if ($stmt->execute()) {
        $stmt->close();
        closeDBConnection($conn);
        echo json_encode(['success' => true, 'message' => 'Xóa chi tiêu thành công']);
    } else {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại']);
    }
}
?>

