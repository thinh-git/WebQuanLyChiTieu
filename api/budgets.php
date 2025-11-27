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
        handleGetBudgets($userId);
        break;
    case 'POST':
        handleCreateBudget($userId);
        break;
    case 'PUT':
        handleUpdateBudget($userId);
        break;
    case 'DELETE':
        handleDeleteBudget($userId);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

function handleGetBudgets($userId) {
    try {
        $conn = getDBConnection();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
        exit();
    }

    $categoryId = $_GET['category_id'] ?? null;
    $period = $_GET['period'] ?? null;
    
    $query = "SELECT b.*, c.name as category_name, c.icon as category_icon, c.color as category_color,
              (SELECT COALESCE(SUM(e.amount), 0) 
               FROM expenses e 
               WHERE e.category_id = b.category_id 
               AND e.user_id = b.user_id 
               AND e.type = 'expense'
               AND e.expense_date >= b.start_date 
               AND (b.end_date IS NULL OR e.expense_date <= b.end_date)
               AND (
                   (b.period = 'daily' AND DATE(e.expense_date) = CURDATE()) OR
                   (b.period = 'weekly' AND YEARWEEK(e.expense_date) = YEARWEEK(CURDATE())) OR
                   (b.period = 'monthly' AND YEAR(e.expense_date) = YEAR(CURDATE()) AND MONTH(e.expense_date) = MONTH(CURDATE())) OR
                   (b.period = 'yearly' AND YEAR(e.expense_date) = YEAR(CURDATE()))
               )
              ) as spent_amount
              FROM budgets b
              INNER JOIN categories c ON b.category_id = c.id
              WHERE b.user_id = ?";
    
    $params = ["i", $userId];
    
    if ($categoryId) {
        $query .= " AND b.category_id = ?";
        $params[0] .= "i";
        $params[] = $categoryId;
    }
    
    if ($period) {
        $query .= " AND b.period = ?";
        $params[0] .= "s";
        $params[] = $period;
    }
    
    $query .= " ORDER BY b.created_at DESC";
    
    $stmt = $conn->prepare($query);
    
    if (count($params) > 1) {
        $stmt->bind_param($params[0], ...array_slice($params, 1));
    } else {
        $stmt->bind_param($params[0], $params[1]);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $budgets = [];
    while ($row = $result->fetch_assoc()) {
        $spent = floatval($row['spent_amount']);
        $budget = floatval($row['amount']);
        $percentage = $budget > 0 ? ($spent / $budget) * 100 : 0;
        
        $row['spent_amount'] = $spent;
        $row['percentage'] = round($percentage, 2);
        $row['remaining'] = max(0, $budget - $spent);
        $row['status'] = $percentage >= 100 ? 'exceeded' : ($percentage >= floatval($row['alert_threshold']) ? 'warning' : 'normal');
        
        $budgets[] = $row;
    }
    
    $stmt->close();
    closeDBConnection($conn);
    
    echo json_encode(['success' => true, 'data' => $budgets]);
}

function handleCreateBudget($userId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $categoryId = intval($input['category_id'] ?? 0);
    $amount = floatval($input['amount'] ?? 0);
    $period = $input['period'] ?? 'monthly';
    $startDate = $input['start_date'] ?? date('Y-m-d');
    $endDate = $input['end_date'] ?? null;
    $alertThreshold = floatval($input['alert_threshold'] ?? 80);
    
    if ($categoryId <= 0 || $amount <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
        return;
    }
    
    if (!in_array($period, ['daily', 'weekly', 'monthly', 'yearly'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Chu kỳ không hợp lệ']);
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
    
    // Insert budget
    $stmt = $conn->prepare("INSERT INTO budgets (user_id, category_id, amount, period, start_date, end_date, alert_threshold) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iidsssd", $userId, $categoryId, $amount, $period, $startDate, $endDate, $alertThreshold);
    
    if ($stmt->execute()) {
        $budgetId = $conn->insert_id;
        $stmt->close();
        closeDBConnection($conn);
        
        echo json_encode(['success' => true, 'message' => 'Tạo ngân sách thành công', 'id' => $budgetId]);
    } else {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi tạo ngân sách']);
    }
}

function handleUpdateBudget($userId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $budgetId = intval($input['id'] ?? 0);
    $amount = floatval($input['amount'] ?? 0);
    $period = $input['period'] ?? 'monthly';
    $startDate = $input['start_date'] ?? null;
    $endDate = $input['end_date'] ?? null;
    $alertThreshold = floatval($input['alert_threshold'] ?? 80);
    
    if ($budgetId <= 0 || $amount <= 0) {
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
    
    // Verify budget belongs to user
    $stmt = $conn->prepare("SELECT id FROM budgets WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $budgetId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Ngân sách không tồn tại']);
        return;
    }
    $stmt->close();
    
    // Update budget
    $query = "UPDATE budgets SET amount = ?, period = ?, alert_threshold = ?";
    $params = ["ids", $amount, $period, $alertThreshold];
    
    if ($startDate) {
        $query .= ", start_date = ?";
        $params[0] .= "s";
        $params[] = $startDate;
    }
    
    if ($endDate !== null) {
        $query .= ", end_date = ?";
        $params[0] .= "s";
        $params[] = $endDate;
    }
    
    $query .= " WHERE id = ? AND user_id = ?";
    $params[0] .= "ii";
    $params[] = $budgetId;
    $params[] = $userId;
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($params[0], ...array_slice($params, 1));
    
    if ($stmt->execute()) {
        $stmt->close();
        closeDBConnection($conn);
        echo json_encode(['success' => true, 'message' => 'Cập nhật ngân sách thành công']);
    } else {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật ngân sách']);
    }
}

function handleDeleteBudget($userId) {
    $budgetId = intval($_GET['id'] ?? 0);
    
    if ($budgetId <= 0) {
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
    
    $stmt = $conn->prepare("DELETE FROM budgets WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $budgetId, $userId);
    
    if ($stmt->execute()) {
        $stmt->close();
        closeDBConnection($conn);
        echo json_encode(['success' => true, 'message' => 'Xóa ngân sách thành công']);
    } else {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa ngân sách']);
    }
}
?>

