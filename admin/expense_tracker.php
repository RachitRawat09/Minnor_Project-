<?php
session_start();
include '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch expense categories
$categories_query = "SELECT * FROM expense_categories ORDER BY name ASC";
$categories = $conn->query($categories_query);

// Fetch recent expenses
$expenses_query = "SELECT e.*, c.name as category_name 
                  FROM expenses e 
                  LEFT JOIN expense_categories c ON e.category_id = c.id 
                  ORDER BY e.date DESC 
                  LIMIT 10";
$expenses = $conn->query($expenses_query);

// Calculate total expenses
$total_query = "SELECT SUM(amount) as total FROM expenses";
$total_result = $conn->query($total_query);
$total_expenses = $total_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #36b9cc;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
        }

        body {
            background-color: #f8f9fc;
            font-family: 'Poppins', sans-serif;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .expense-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .expense-list {
            max-height: 500px;
            overflow-y: auto;
        }

        .expense-item {
            border-left: 4px solid var(--info-color);
            margin-bottom: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .expense-item:hover {
            transform: translateX(5px);
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .btn-add-expense {
            background: var(--info-color);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-add-expense:hover {
            background: #2a8a99;
            transform: translateY(-2px);
        }

        .total-expenses {
            font-size: 2rem;
            font-weight: bold;
            color: var(--info-color);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="mb-0">Expense Tracker</h2>
                <p class="text-muted">Track and manage your expenses</p>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-add-expense" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                    <i class="fas fa-plus me-2"></i>Add Expense
                </button>
            </div>
        </div>

        <div class="row">
            <!-- Expense Summary -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Expenses</h5>
                        <div class="total-expenses">₹<?= number_format($total_expenses, 2) ?></div>
                        <div class="chart-container mt-4">
                            <canvas id="expenseChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Expense Form -->
            <div class="col-md-8 mb-4">
                <div class="expense-form">
                    <h5 class="mb-4">Add New Expense</h5>
                    <form id="expenseForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category" required>
                                    <option value="">Select Category</option>
                                    <?php while($category = $categories->fetch_assoc()) { ?>
                                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Amount</label>
                                <input type="number" class="form-control" name="amount" required min="0" step="0.01">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="date" required>
                        </div>
                        <button type="submit" class="btn btn-add-expense">Add Expense</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Expenses -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Recent Expenses</h5>
                        <div class="expense-list">
                            <?php while($expense = $expenses->fetch_assoc()) { ?>
                                <div class="expense-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($expense['description']) ?></h6>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($expense['category_name']) ?> • 
                                                <?= date('M d, Y', strtotime($expense['date'])) ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <h6 class="mb-1">₹<?= number_format($expense['amount'], 2) ?></h6>
                                            <small class="text-muted">Expense ID: <?= $expense['id'] ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Chart
        const ctx = document.getElementById('expenseChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Food', 'Supplies', 'Utilities', 'Other'],
                datasets: [{
                    data: [30, 25, 25, 20],
                    backgroundColor: [
                        '#36b9cc',
                        '#1cc88a',
                        '#f6c23e',
                        '#e74a3b'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Handle form submission
        document.getElementById('expenseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add your form submission logic here
            alert('Expense added successfully!');
            this.reset();
        });
    </script>
</body>
</html> 