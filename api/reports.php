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

$type = $_GET['type'] ?? 'monthly';
$userId = getCurrentUserId();

try {
    $conn = getDBConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
    exit();
}

switch ($type) {
    case 'monthly':
        getMonthlyReport($conn, $userId);
        break;
    case 'by_category':
        getCategoryReport($conn, $userId);
        break;
    case 'trend':
        getTrendReport($conn, $userId);
        break;
    case 'top_categories':
        getTopCategories($conn, $userId);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid report type']);
        break;
}

function getMonthlyReport($conn, $userId) {
    $year = intval($_GET['year'] ?? date('Y'));
    $month = intval($_GET['month'] ?? date('m'));

    $startDate = "$year-$month-01";
    $endDate = date('Y-m-t', strtotime($startDate));

    $stmt = $conn->prepare("SELECT 
                            DATE(expense_date) as date,
                            SUM(amount) as total
                            FROM expenses 
                            WHERE user_id = ? AND expense_date >= ? AND expense_date <= ?
                            GROUP BY DATE(expense_date)
                            ORDER BY date ASC");
    $stmt->bind_param("iss", $userId, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // Get total for month
    $stmt = $conn->prepare("SELECT SUM(amount) as total FROM expenses WHERE user_id = ? AND expense_date >= ? AND expense_date <= ?");
    $stmt->bind_param("iss", $userId, $startDate, $endDate);
    $stmt->execute();
    $totalResult = $stmt->get_result();
    $total = $totalResult->fetch_assoc()['total'] ?? 0;

    $stmt->close();
    closeDBConnection($conn);

    echo json_encode([
        'success' => true,
        'data' => $data,
        'total' => floatval($total),
        'month' => $month,
        'year' => $year
    ]);
}

function getCategoryReport($conn, $userId) {
    $year = intval($_GET['year'] ?? date('Y'));
    $month = intval($_GET['month'] ?? date('m'));

    $startDate = "$year-$month-01";
    $endDate = date('Y-m-t', strtotime($startDate));

    $stmt = $conn->prepare("SELECT 
                            c.id,
                            c.name,
                            c.icon,
                            c.color,
                            SUM(e.amount) as total,
                            COUNT(e.id) as count
                            FROM categories c
                            LEFT JOIN expenses e ON c.id = e.category_id 
                            AND e.user_id = ? 
                            AND e.expense_date >= ? 
                            AND e.expense_date <= ?
                            WHERE c.user_id = ?
                            GROUP BY c.id
                            HAVING total > 0
                            ORDER BY total DESC");
    $stmt->bind_param("issi", $userId, $startDate, $endDate, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();
    closeDBConnection($conn);

    echo json_encode(['success' => true, 'data' => $data]);
}

function getTrendReport($conn, $userId) {
    $months = intval($_GET['months'] ?? 6);
    
    $startDate = date('Y-m-01', strtotime("-$months months"));
    $endDate = date('Y-m-t');

    $stmt = $conn->prepare("SELECT 
                            DATE_FORMAT(expense_date, '%Y-%m') as month,
                            SUM(amount) as total
                            FROM expenses 
                            WHERE user_id = ? AND expense_date >= ? AND expense_date <= ?
                            GROUP BY DATE_FORMAT(expense_date, '%Y-%m')
                            ORDER BY month ASC");
    $stmt->bind_param("iss", $userId, $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();
    closeDBConnection($conn);

    echo json_encode(['success' => true, 'data' => $data]);
}

function getTopCategories($conn, $userId) {
    $limit = intval($_GET['limit'] ?? 5);
    $year = intval($_GET['year'] ?? date('Y'));
    $month = intval($_GET['month'] ?? date('m'));

    $startDate = "$year-$month-01";
    $endDate = date('Y-m-t', strtotime($startDate));

    $stmt = $conn->prepare("SELECT 
                            c.id,
                            c.name,
                            c.icon,
                            c.color,
                            SUM(e.amount) as total
                            FROM categories c
                            JOIN expenses e ON c.id = e.category_id
                            WHERE e.user_id = ? AND e.expense_date >= ? AND e.expense_date <= ?
                            GROUP BY c.id
                            ORDER BY total DESC
                            LIMIT ?");
    $stmt->bind_param("issi", $userId, $startDate, $endDate, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();
    closeDBConnection($conn);

    echo json_encode(['success' => true, 'data' => $data]);
}
?>

