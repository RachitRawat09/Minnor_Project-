<?php
session_start();
include '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch recent completed sales (last 10 completed orders in the last month)
$sales_query = "SELECT * FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND order_status = 'Completed' ORDER BY created_at DESC LIMIT 10";
$sales = $conn->query($sales_query);

// Calculate total completed sales for the last month
$total_sales_query = "SELECT SUM(total_price) as total FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND order_status = 'Completed'";
$total_sales_result = $conn->query($total_sales_query);
$total_sales = $total_sales_result->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Tracker - Admin Dashboard</title>
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

        .chart-container {
            position: relative;
            height: 300px;
        }

        .total-sales {
            font-size: 2rem;
            font-weight: bold;
            color: var(--info-color);
        }
        .sales-list {
            max-height: 500px;
            overflow-y: auto;
        }
        .sales-item {
            border-left: 4px solid var(--info-color);
            margin-bottom: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .sales-item:hover {
            transform: translateX(5px);
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary">
            <i class="fas fa-utensils"></i> CodeToCuisine Admin
        </a>
        <div class="d-flex align-items-center">
            
            <a href="index.php" class="btn btn-outline-primary rounded-pill">
                <i class="fas fa-arrow-left me-2"></i>Dashboard
            </a>
        </div>
    </div>
</nav>
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="mb-0">Sales Tracker</h2>
                <p class="text-muted">Track and manage your monthly sales</p>
            </div>
        </div>

        <div class="row">
            <!-- Sales Summary -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Sales (Last 1 Month)</h5>
                        <div class="total-sales">₹<?= number_format($total_sales, 2) ?></div>
                        <div class="chart-container mt-4">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Sales List -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Recent Sales</h5>
                        <div class="sales-list">
                            <?php while($sale = $sales->fetch_assoc()) { ?>
                                <div class="sales-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">Order #<?= $sale['id'] ?></h6>
                                            <small class="text-muted">
                                                Table <?= htmlspecialchars($sale['table_number']) ?> •
                                                <?= date('M d, Y h:i A', strtotime($sale['created_at'])) ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <h6 class="mb-1">₹<?= number_format($sale['total_price'], 2) ?></h6>
                                            <small class="text-muted">Order Type: <?= htmlspecialchars($sale['order_type']) ?></small><br>
                                            <span class="badge bg-success">Status: <?= htmlspecialchars($sale['order_status']) ?></span>
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
        // Fetch sales data for the last 30 days for the chart
        fetch('get_monthly_sales_data.php')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('salesChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Daily Sales',
                            data: data.sales,
                            backgroundColor: 'rgba(54, 185, 204, 0.2)',
                            borderColor: '#36b9cc',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
    </script>
</body>
</html> 