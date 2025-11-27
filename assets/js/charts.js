let categoryChart, dailyChart, trendChart, incomeExpenseChart;

// Initialize charts
async function initCharts() {
    await loadChartsData();
    
    // Set up month/year change handlers
    if (document.getElementById('reportMonth')) {
        document.getElementById('reportMonth').addEventListener('change', loadChartsData);
    }
    
    if (document.getElementById('reportYear')) {
        document.getElementById('reportYear').addEventListener('change', loadChartsData);
    }
}

// Load all charts data
async function loadChartsData() {
    const month = document.getElementById('reportMonth')?.value || new Date().getMonth() + 1;
    const year = document.getElementById('reportYear')?.value || new Date().getFullYear();
    
    await Promise.all([
        loadIncomeExpenseChart(month, year),
        loadCategoryChart(month, year),
        loadDailyChart(month, year),
        loadTrendChart(),
        loadTopCategories(month, year),
        loadSummary(month, year)
    ]);
}

// Income vs Expense Chart (Bar Chart)
async function loadIncomeExpenseChart(month, year) {
    try {
        const basePath = window.BASE_PATH || '';
        const firstDay = new Date(year, month - 1, 1).toISOString().split('T')[0];
        const lastDay = new Date(year, month, 0).toISOString().split('T')[0];
        
        const response = await fetch(basePath + `/api/expenses.php?start_date=${firstDay}&end_date=${lastDay}&limit=10000`);
        const result = await response.json();
        
        if (result.success) {
            const income = result.data
                .filter(e => e.type === 'income')
                .reduce((sum, e) => sum + parseFloat(e.amount), 0);
            
            const expense = result.data
                .filter(e => e.type === 'expense')
                .reduce((sum, e) => sum + parseFloat(e.amount), 0);
            
            const ctx = document.getElementById('incomeExpenseChart');
            if (!ctx) return;
            
            if (incomeExpenseChart) {
                incomeExpenseChart.destroy();
            }
            
            incomeExpenseChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Thu nhập', 'Chi tiêu'],
                    datasets: [{
                        label: 'Số tiền (đ)',
                        data: [income, expense],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(239, 68, 68, 0.8)'
                        ],
                        borderColor: [
                            '#10B981',
                            '#EF4444'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return formatCurrency(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatCurrency(value);
                                }
                            }
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error loading income/expense chart:', error);
    }
}

// Category Chart (Pie)
async function loadCategoryChart(month, year) {
    try {
        const basePath = window.BASE_PATH || '';
        const response = await fetch(basePath + `/api/reports.php?type=by_category&month=${month}&year=${year}`);
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            const ctx = document.getElementById('categoryChart');
            if (!ctx) return;
            
            if (categoryChart) {
                categoryChart.destroy();
            }
            
            const labels = result.data.map(item => `${item.icon} ${item.name}`);
            const data = result.data.map(item => parseFloat(item.total));
            const colors = result.data.map(item => item.color);
            
            categoryChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${formatCurrency(value)} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            const ctx = document.getElementById('categoryChart');
            if (ctx && ctx.parentElement) {
                ctx.parentElement.innerHTML = '<p class="empty-state">Không có dữ liệu</p>';
            }
        }
    } catch (error) {
        console.error('Error loading category chart:', error);
    }
}

// Daily Chart (Line)
async function loadDailyChart(month, year) {
    try {
        const basePath = window.BASE_PATH || '';
        const response = await fetch(basePath + `/api/reports.php?type=monthly&month=${month}&year=${year}`);
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            const ctx = document.getElementById('dailyChart');
            if (!ctx) return;
            
            if (dailyChart) {
                dailyChart.destroy();
            }
            
            const labels = result.data.map(item => {
                const date = new Date(item.date);
                return date.getDate() + '/' + (date.getMonth() + 1);
            });
            const data = result.data.map(item => parseFloat(item.total));
            
            dailyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Chi tiêu (đ)',
                        data: data,
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Chi tiêu: ${formatCurrency(context.parsed.y)}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatCurrency(value);
                                }
                            }
                        }
                    }
                }
            });
        } else {
            const ctx = document.getElementById('dailyChart');
            if (ctx && ctx.parentElement) {
                ctx.parentElement.innerHTML = '<p class="empty-state">Không có dữ liệu</p>';
            }
        }
    } catch (error) {
        console.error('Error loading daily chart:', error);
    }
}

// Trend Chart (Line - 6 months)
async function loadTrendChart() {
    try {
        const basePath = window.BASE_PATH || '';
        const response = await fetch(basePath + '/api/reports.php?type=trend&months=6');
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            const ctx = document.getElementById('trendChart');
            if (!ctx) return;
            
            if (trendChart) {
                trendChart.destroy();
            }
            
            const labels = result.data.map(item => {
                const [year, month] = item.month.split('-');
                return `T${month}/${year}`;
            });
            const data = result.data.map(item => parseFloat(item.total));
            
            trendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Tổng chi tiêu (đ)',
                        data: data,
                        borderColor: '#2ecc71',
                        backgroundColor: 'rgba(46, 204, 113, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Tổng: ${formatCurrency(context.parsed.y)}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatCurrency(value);
                                }
                            }
                        }
                    }
                }
            });
        } else {
            const ctx = document.getElementById('trendChart');
            if (ctx && ctx.parentElement) {
                ctx.parentElement.innerHTML = '<p class="empty-state">Không có dữ liệu</p>';
            }
        }
    } catch (error) {
        console.error('Error loading trend chart:', error);
    }
}

// Top Categories List
async function loadTopCategories(month, year) {
    try {
        const basePath = window.BASE_PATH || '';
        const response = await fetch(basePath + `/api/reports.php?type=top_categories&month=${month}&year=${year}&limit=5`);
        const result = await response.json();
        
        const listEl = document.getElementById('topCategoriesList');
        if (!listEl) return;
        
        if (result.success && result.data.length > 0) {
            listEl.innerHTML = result.data.map((item, index) => `
                <div class="top-category-item">
                    <div class="top-category-info">
                        <span style="font-size: 1.5rem; margin-right: 10px;">${item.icon}</span>
                        <div>
                            <strong>${index + 1}. ${item.name}</strong>
                            <div style="color: #666; font-size: 0.9rem;">${item.total} đ</div>
                        </div>
                    </div>
                    <div class="top-category-amount">${formatCurrency(item.total)}</div>
                </div>
            `).join('');
        } else {
            listEl.innerHTML = '<p class="empty-state">Không có dữ liệu</p>';
        }
    } catch (error) {
        console.error('Error loading top categories:', error);
    }
}

// Summary
async function loadSummary(month, year) {
    try {
        const basePath = window.BASE_PATH || '';
        const firstDay = new Date(year, month - 1, 1).toISOString().split('T')[0];
        const lastDay = new Date(year, month, 0).toISOString().split('T')[0];
        
        const response = await fetch(basePath + `/api/expenses.php?start_date=${firstDay}&end_date=${lastDay}&limit=10000`);
        const result = await response.json();
        
        if (result.success) {
            const income = result.data
                .filter(e => e.type === 'income')
                .reduce((sum, e) => sum + parseFloat(e.amount), 0);
            
            const expense = result.data
                .filter(e => e.type === 'expense')
                .reduce((sum, e) => sum + parseFloat(e.amount), 0);
            
            const balance = income - expense;
            const count = result.data.length;
            const daysInMonth = new Date(year, month, 0).getDate();
            const avgPerDay = count > 0 ? expense / daysInMonth : 0;
            
            if (document.getElementById('totalIncome')) {
                document.getElementById('totalIncome').textContent = formatCurrency(income);
            }
            
            if (document.getElementById('totalExpense')) {
                document.getElementById('totalExpense').textContent = formatCurrency(expense);
            }
            
            if (document.getElementById('balance')) {
                const balanceEl = document.getElementById('balance');
                balanceEl.textContent = formatCurrency(Math.abs(balance));
                balanceEl.style.color = balance >= 0 ? 'var(--income-color)' : 'var(--expense-color)';
            }
            
            if (document.getElementById('totalCount')) {
                document.getElementById('totalCount').textContent = count;
            }
        }
    } catch (error) {
        console.error('Error loading summary:', error);
    }
}

// Initialize on page load
if (document.getElementById('categoryChart')) {
    initCharts();
}

