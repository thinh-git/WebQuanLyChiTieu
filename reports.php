<?php
require_once __DIR__ . '/config/path.php';
$pageTitle = 'Thống kê và báo cáo';
$additionalScripts = ['assets/js/charts.js'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h2>Thống kê và báo cáo</h2>
        <div class="date-selector">
            <select id="reportMonth">
                <?php
                $currentMonth = date('m');
                $currentYear = date('Y');
                for ($i = 1; $i <= 12; $i++) {
                    $selected = ($i == $currentMonth) ? 'selected' : '';
                    $monthName = date('F', mktime(0, 0, 0, $i, 1));
                    echo "<option value=\"$i\" $selected>Tháng $i</option>";
                }
                ?>
            </select>
            <select id="reportYear">
                <?php
                for ($year = $currentYear; $year >= $currentYear - 5; $year--) {
                    $selected = ($year == $currentYear) ? 'selected' : '';
                    echo "<option value=\"$year\" $selected>$year</option>";
                }
                ?>
            </select>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card income-card">
            <div class="summary-icon income-icon">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="summary-content">
                <h3>Tổng thu nhập</h3>
                <p class="summary-amount" id="totalIncome">0 đ</p>
            </div>
        </div>
        <div class="summary-card expense-card">
            <div class="summary-icon expense-icon">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="summary-content">
                <h3>Tổng chi tiêu</h3>
                <p class="summary-amount" id="totalExpense">0 đ</p>
            </div>
        </div>
        <div class="summary-card balance-card">
            <div class="summary-icon balance-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="summary-content">
                <h3>Số dư</h3>
                <p class="summary-amount" id="balance">0 đ</p>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-list"></i>
            </div>
            <div class="summary-content">
                <h3>Số giao dịch</h3>
                <p class="summary-amount" id="totalCount">0</p>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-grid">
        <div class="chart-card">
            <h3><i class="fas fa-chart-pie"></i> So sánh Thu - Chi</h3>
            <canvas id="incomeExpenseChart"></canvas>
        </div>
        
        <div class="chart-card">
            <h3><i class="fas fa-chart-pie"></i> Chi tiêu theo danh mục</h3>
            <canvas id="categoryChart"></canvas>
        </div>
        
        <div class="chart-card">
            <h3><i class="fas fa-chart-line"></i> Chi tiêu theo ngày (tháng này)</h3>
            <canvas id="dailyChart"></canvas>
        </div>
        
        <div class="chart-card">
            <h3><i class="fas fa-chart-line"></i> Xu hướng chi tiêu (6 tháng gần nhất)</h3>
            <canvas id="trendChart"></canvas>
        </div>
        
        <div class="chart-card">
            <h3><i class="fas fa-trophy"></i> Top 5 danh mục</h3>
            <div id="topCategoriesList"></div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

