<?php
header('Content-Type: application/json');

$expensesFile = 'data/expenses.json';

// Create data directory if it doesn't exist
if (!file_exists('data')) {
    mkdir('data', 0777, true);
}

// Initialize expenses array
$expenses = [];
if (file_exists($expensesFile)) {
    $expenses = json_decode(file_get_contents($expensesFile), true);
}

// Handle POST request (add new expense)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newExpense = [
        'description' => $_POST['description'],
        'amount' => (float)$_POST['amount'],
        'date' => $_POST['date'],
        'category' => $_POST['category']
    ];
    
    array_push($expenses, $newExpense);
    file_put_contents($expensesFile, json_encode($expenses));
    
    echo json_encode(['success' => true, 'expense' => $newExpense]);
    exit;
}

// Handle DELETE request (remove expense)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents('php://input'), $_DELETE);
    
    if (isset($_DELETE['index']) && isset($expenses[$_DELETE['index']])) {
        array_splice($expenses, $_DELETE['index'], 1);
        file_put_contents($expensesFile, json_encode($expenses));
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    echo json_encode(['success' => false, 'error' => 'Invalid index']);
    exit;
}

// Handle GET request (get expenses data for charts)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $categories = ['Food', 'Transport', 'Entertainment', 'Utilities', 'Shopping', 'Healthcare', 'Other'];
    $categoryTotals = array_fill_keys($categories, 0);
    
    foreach ($expenses as $expense) {
        $categoryTotals[$expense['category']] += $expense['amount'];
    }
    
    // Prepare data for charts
    $chartData = [
        'labels' => $categories,
        'datasets' => [[
            'data' => array_values($categoryTotals),
            'backgroundColor' => [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                '#9966FF', '#FF9F40', '#8AC24A'
            ]
        ]],
        'dailyTrend' => getDailyTrendData($expenses)
    ];
    
    echo json_encode($chartData);
    exit;
}

function getDailyTrendData($expenses) {
    $last30Days = [];
    $today = new DateTime();
    
    for ($i = 29; $i >= 0; $i--) {
        $date = clone $today;
        $date->modify("-$i days");
        $formattedDate = $date->format('Y-m-d');
        $last30Days[$formattedDate] = 0;
    }
    
    foreach ($expenses as $expense) {
        $expenseDate = $expense['date'];
        if (array_key_exists($expenseDate, $last30Days)) {
            $last30Days[$expenseDate] += $expense['amount'];
        }
    }
    
    return [
        'labels' => array_keys($last30Days),
        'data' => array_values($last30Days)
    ];
}
?>