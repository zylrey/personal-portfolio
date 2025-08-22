document.addEventListener('DOMContentLoaded', function() {
    // Initialize chart
    let expenseChart;
    let currentChartType = 'pie';
    
    // DOM elements
    const expenseForm = document.getElementById('expenseForm');
    const chartToggles = document.querySelectorAll('.chart-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    // Initialize the chart
    function initChart() {
        fetchChartData().then(data => {
            renderChart(data, currentChartType);
        });
    }
    
    // Fetch chart data from server
    function fetchChartData() {
        return fetch('process.php')
            .then(response => response.json())
            .catch(error => {
                console.error('Error fetching chart data:', error);
            });
    }
    
    // Render chart based on type
    function renderChart(data, type = 'pie') {
        const ctx = document.getElementById('expenseChart').getContext('2d');
        
        // Destroy previous chart if exists
        if (expenseChart) {
            expenseChart.destroy();
        }
        
        let chartConfig;
        
        switch (type) {
            case 'bar':
                chartConfig = getBarChartConfig(data);
                break;
            case 'line':
                chartConfig = getLineChartConfig(data.dailyTrend);
                break;
            case 'pie':
            default:
                chartConfig = getPieChartConfig(data);
                break;
        }
        
        expenseChart = new Chart(ctx, chartConfig);
    }
    
    // Pie chart configuration
    function getPieChartConfig(data) {
        return {
            type: 'pie',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.datasets[0].data,
                    backgroundColor: data.datasets[0].backgroundColor,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: $${value.toFixed(2)} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        };
    }
    
    // Bar chart configuration
    function getBarChartConfig(data) {
        return {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Expenses by Category',
                    data: data.datasets[0].data,
                    backgroundColor: data.datasets[0].backgroundColor,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '$' + context.raw.toFixed(2);
                            }
                        }
                    }
                }
            }
        };
    }
    
    // Line chart configuration (daily trend)
    function getLineChartConfig(trendData) {
        return {
            type: 'line',
            data: {
                labels: trendData.labels.map(date => {
                    const d = new Date(date);
                    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }),
                datasets: [{
                    label: 'Daily Spending',
                    data: trendData.data,
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '$' + context.raw.toFixed(2);
                            }
                        }
                    }
                }
            }
        };
    }
    
    // Handle form submission
    expenseForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(expenseForm);
        
        fetch('process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reset form
                expenseForm.reset();
                expenseForm.querySelector('#date').value = new Date().toISOString().split('T')[0];
                
                // Refresh chart and recent expenses
                initChart();
                location.reload(); // Simple refresh for demo
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
    
    // Handle chart type toggles
    chartToggles.forEach(button => {
        button.addEventListener('click', function() {
            const chartType = this.dataset.chartType;
            
            // Update active state
            chartToggles.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Update chart
            currentChartType = chartType;
            fetchChartData().then(data => {
                renderChart(data, currentChartType);
            });
        });
    });
    
    // Handle expense deletion
    document.querySelector('.expenses-list').addEventListener('click', function(e) {
        if (e.target.closest('.delete-btn')) {
            const button = e.target.closest('.delete-btn');
            const index = button.dataset.index;
            
            if (confirm('Are you sure you want to delete this expense?')) {
                fetch('process.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `index=${index}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Simple refresh for demo
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        }
    });
    
    // Initialize the app
    initChart();
});