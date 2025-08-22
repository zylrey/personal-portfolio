<?php
// Initialize or load expenses data
$expensesFile = 'data/expenses.json';
$categories = ['Food', 'Transport', 'Entertainment', 'Utilities', 'Shopping', 'Healthcare', 'Other'];
$expenses = [];

if (file_exists($expensesFile)) {
    $expenses = json_decode(file_get_contents($expensesFile), true);
}

// Calculate totals for summary
$totalExpenses = 0;
$categoryTotals = array_fill_keys($categories, 0);

foreach ($expenses as $expense) {
    $totalExpenses += $expense['amount'];
    $categoryTotals[$expense['category']] += $expense['amount'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-wallet"></i> Expense Tracker</h1>
            <p class="subtitle">Track your spending with ease</p>
        </header>

        <div class="dashboard">
            <div class="summary-card total">
                <h3>Total Expenses</h3>
                <p class="amount">$<?= number_format($totalExpenses, 2) ?></p>
                <p class="info">Last 30 days</p>
            </div>

            <div class="summary-card average">
                <h3>Daily Average</h3>
                <p class="amount">$<?= number_format($totalExpenses/30, 2) ?></p>
                <p class="info">Based on 30 days</p>
            </div>

            <div class="summary-card categories">
                <h3>Categories</h3>
                <p class="amount"><?= count($categories) ?></p>
                <p class="info">Different types</p>
            </div>
        </div>

        <div class="main-content">
            <div class="form-section">
                <h2><i class="fas fa-plus-circle"></i> Add New Expense</h2>
                <form id="expenseForm" method="post" action="process.php">
                    <div class="form-group">
                        <label for="description">Description</label>
                        <input type="text" id="description" name="description" required placeholder="What was this expense for?">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="amount">Amount ($)</label>
                            <input type="number" id="amount" name="amount" min="0.01" step="0.01" required placeholder="0.00">
                        </div>

                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="date" id="date" name="date" required value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category ?>"><?= $category ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Add Expense
                    </button>
                </form>
            </div>

            <div class="chart-section">
                <h2><i class="fas fa-chart-pie"></i> Expense Overview</h2>
                <div class="chart-container">
                    <canvas id="expenseChart"></canvas>
                </div>
                <div class="chart-toggles">
                    <button class="chart-btn active" data-chart-type="pie"><i class="fas fa-chart-pie"></i> Pie</button>
                    <button class="chart-btn" data-chart-type="bar"><i class="fas fa-chart-bar"></i> Bar</button>
                    <button class="chart-btn" data-chart-type="line"><i class="fas fa-chart-line"></i> Trend</button>
                </div>
            </div>
        </div>

        <div class="recent-expenses">
            <h2><i class="fas fa-history"></i> Recent Expenses</h2>
            <div class="expenses-list">
                <?php if (empty($expenses)): ?>
                    <p class="no-expenses">No expenses recorded yet. Add your first expense above!</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice(array_reverse($expenses), 0, 10) as $index => $expense): ?>
                                <tr>
                                    <td><?= date('M j, Y', strtotime($expense['date'])) ?></td>
                                    <td><?= htmlspecialchars($expense['description']) ?></td>
                                    <td><span class="category-tag" style="background-color: <?= getCategoryColor($expense['category']) ?>"><?= $expense['category'] ?></span></td>
                                    <td class="amount">$<?= number_format($expense['amount'], 2) ?></td>
                                    <td>
                                        <button class="delete-btn" data-index="<?= count($expenses) - $index - 1 ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>

<?php
// Helper function to get consistent colors for categories
function getCategoryColor($category) {
    $colors = [
        'Food' => '#FF6384',
        'Transport' => '#36A2EB',
        'Entertainment' => '#FFCE56',
        'Utilities' => '#4BC0C0',
        'Shopping' => '#9966FF',
        'Healthcare' => '#FF9F40',
        'Other' => '#8AC24A'
    ];
    return $colors[$category] ?? '#CCCCCC';
}
?>