<?php
session_start();
include '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$restaurant_id = isset($_SESSION['restaurant_id']) ? $_SESSION['restaurant_id'] : null;

// Get today's orders count
$today_orders_query = ($role === 'admin') ? "SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE() AND restaurant_id = $restaurant_id" : "SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()";
$today_orders_result = $conn->query($today_orders_query);
$today_orders = $today_orders_result->fetch_assoc()['count'];

// Get pending orders count
$pending_orders_query = ($role === 'admin') ? "SELECT COUNT(*) as count FROM orders WHERE payment_status = 'Pending' AND restaurant_id = $restaurant_id" : "SELECT COUNT(*) as count FROM orders WHERE payment_status = 'Pending'";
$pending_orders_result = $conn->query($pending_orders_query);
$pending_orders = $pending_orders_result->fetch_assoc()['count'];

// Get total menu items
$menu_items_query = ($role === 'admin') ? "SELECT COUNT(*) as count FROM menu_items WHERE restaurant_id = $restaurant_id" : "SELECT COUNT(*) as count FROM menu_items";
$menu_items_result = $conn->query($menu_items_query);
$menu_items = $menu_items_result->fetch_assoc()['count'];

// Get orders from the last 1 month
$month_orders_query = ($role === 'admin') ? "SELECT COUNT(*) as count FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND restaurant_id = $restaurant_id" : "SELECT COUNT(*) as count FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
$month_orders_result = $conn->query($month_orders_query);
$month_orders = $month_orders_result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CodeToCuisine</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
        
        .dashboard-card {
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: transform 0.2s;
            border: none;
            overflow: hidden;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        
        .card-icon {
            font-size: 2rem;
            opacity: 0.4;
        }
        
        .orders-card {
            border-left: 4px solid var(--primary-color);
        }
        
        .menu-card {
            border-left: 4px solid var(--success-color);
        }
        
        .pending-card {
            border-left: 4px solid var(--warning-color);
        }
        
        .today-card {
            border-left: 4px solid var(--info-color);
        }
        
        .quick-actions {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 1.5rem;
        }
        
        .action-btn {
            padding: 1rem;
            border-radius: 10px;
            transition: all 0.3s;
            text-decoration: none;
            color: #5a5c69;
        }
        
        .action-btn:hover {
            background-color: #f8f9fc;
            transform: translateY(-2px);
        }
        
        .action-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .recent-orders {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .order-item {
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem;
            transition: background-color 0.2s;
        }
        
        .order-item:hover {
            background-color: #f8f9fc;
        }
        
        .status-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-pending {
            background-color: #f6c23e;
            color: white;
        }
        
        .status-completed {
            background-color: #1cc88a;
            color: white;
        }

        .status-info {
            background-color: #36b9cc;
            color: white;
        }

        .status-primary {
            background-color: #4e73df;
            color: white;
        }

        .status-success {
            background-color: #1cc88a;
            color: white;
        }

        .status-danger {
            background-color: #e74a3b;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0 text-gray-800">Dashboard</h2>
            <div class="d-flex gap-2">
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <a href="logout.php" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                <?php } else { ?>
                    <a href="login.php" class="btn btn-outline-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                <?php } ?>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card orders-card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="text-uppercase mb-2">Total Orders</h6>
                                <h2 class="mb-0"><?= $month_orders ?></h2>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list card-icon text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card pending-card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="text-uppercase mb-2">Pending Orders</h6>
                                <h2 class="mb-0"><?= $pending_orders ?></h2>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock card-icon text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card menu-card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="text-uppercase mb-2">Menu Items</h6>
                                <h2 class="mb-0"><?= $menu_items ?></h2>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-utensils card-icon text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card dashboard-card today-card h-100">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="text-uppercase mb-2">Today's Orders</h6>
                                <h2 class="mb-0"><?= $today_orders ?></h2>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-day card-icon text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="quick-actions">
                    <h5 class="mb-4">Quick Actions</h5>
                    <div class="row">
                        <div class="col-md-3 col-6 mb-3">
                            <a href="manage_menu.php" class="d-block text-center action-btn">
                                <i class="fas fa-utensils action-icon text-success"></i>
                                <h6 class="mb-0">Manage Menu</h6>
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="orders.php" class="d-block text-center action-btn">
                                <i class="fas fa-clipboard-list action-icon text-primary"></i>
                                <h6 class="mb-0">View Orders</h6>
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="see_orders.php" class="d-block text-center action-btn">
                                <i class="fas fa-eye action-icon text-info"></i>
                                <h6 class="mb-0">Today's Orders</h6>
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="expense_tracker.php" class="d-block text-center action-btn">
                                <i class="fas fa-chart-line action-icon text-info"></i>
                                <h6 class="mb-0">Expense Tracker</h6>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="row">
            <div class="col-12">
                <div class="recent-orders">
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                        <h5 class="mb-0">Recent Orders</h5>
                        <a href="see_orders.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <?php
                    $recent_orders_query = ($role === 'admin') ? "SELECT * FROM orders WHERE DATE(created_at) = CURDATE() AND restaurant_id = $restaurant_id ORDER BY created_at DESC LIMIT 5" : "SELECT * FROM orders WHERE DATE(created_at) = CURDATE() ORDER BY created_at DESC LIMIT 5";
                    $recent_orders_result = $conn->query($recent_orders_query);
                    
                    if ($recent_orders_result->num_rows > 0) {
                        while ($order = $recent_orders_result->fetch_assoc()) {
                            $orderStatus = $order['order_status'] ?? 'Pending';
                            $statusInfo = [
                                'Pending' => ['class' => 'status-pending', 'icon' => 'clock'],
                                'Processing' => ['class' => 'status-info', 'icon' => 'sync'],
                                'Preparing' => ['class' => 'status-primary', 'icon' => 'utensils'],
                                'Ready' => ['class' => 'status-success', 'icon' => 'check-circle'],
                                'Completed' => ['class' => 'status-success', 'icon' => 'flag-checkered'],
                                'Cancelled' => ['class' => 'status-danger', 'icon' => 'times-circle']
                            ][$orderStatus] ?? ['class' => 'status-pending', 'icon' => 'clock'];
                    ?>
                            <div class="order-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Order #<?= $order['id'] ?></h6>
                                        <small class="text-muted">
                                            Table <?= $order['table_number'] ?> • 
                                            <?= date('h:i A', strtotime($order['created_at'])) ?>
                                        </small>
                                    </div>
                                    <div>
                                        <span class="status-badge <?= $statusInfo['class'] ?>">
                                            <i class="fas fa-<?= $statusInfo['icon'] ?> me-1"></i>
                                            <?= $orderStatus ?>
                                        </span>
                                        <span class="ms-2 fw-bold">₹<?= number_format($order['total_price'], 2) ?></span>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        echo '<div class="p-3 text-center text-muted">No recent orders</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Real-time Updates Script -->
    <script>
        // Function to update dashboard stats
        function updateDashboardStats() {
            fetch('get_dashboard_stats.php')
                .then(response => response.json())
                .then(data => {
                    // Update stats cards
                    document.querySelector('.orders-card h2').textContent = data.month_orders;
                    document.querySelector('.pending-card h2').textContent = data.pending_orders;
                    document.querySelector('.menu-card h2').textContent = data.menu_items;
                    document.querySelector('.today-card h2').textContent = data.today_orders;
                });
        }

        // Function to update recent orders
        function updateRecentOrders() {
            fetch('get_recent_orders.php')
                .then(response => response.json())
                .then(data => {
                    const recentOrdersContainer = document.querySelector('.recent-orders');
                    const ordersList = recentOrdersContainer.querySelector('.order-item').parentElement;
                    
                    // Clear existing orders
                    ordersList.innerHTML = '';
                    
                    if (data.length > 0) {
                        data.forEach(order => {
                            const statusInfo = {
                                'Pending': { class: 'status-pending', icon: 'clock' },
                                'Processing': { class: 'status-info', icon: 'sync' },
                                'Preparing': { class: 'status-primary', icon: 'utensils' },
                                'Ready': { class: 'status-success', icon: 'check-circle' },
                                'Completed': { class: 'status-success', icon: 'flag-checkered' },
                                'Cancelled': { class: 'status-danger', icon: 'times-circle' }
                            }[order.order_status] || { class: 'status-pending', icon: 'clock' };

                            const orderHTML = `
                                <div class="order-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">Order #${order.id}</h6>
                                            <small class="text-muted">
                                                Table ${order.table_number} • 
                                                ${new Date(order.created_at).toLocaleTimeString()}
                                            </small>
                                        </div>
                                        <div>
                                            <span class="status-badge ${statusInfo.class}">
                                                <i class="fas fa-${statusInfo.icon} me-1"></i>
                                                ${order.order_status}
                                            </span>
                                            <span class="ms-2 fw-bold">₹${parseFloat(order.total_price).toFixed(2)}</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                            ordersList.innerHTML += orderHTML;
                        });
                    } else {
                        ordersList.innerHTML = '<div class="p-3 text-center text-muted">No recent orders</div>';
                    }
                });
        }

        // Function to check for new orders
        function checkNewOrders() {
            updateDashboardStats();
            updateRecentOrders();
        }

        // Check for new orders every 10 seconds
        setInterval(checkNewOrders, 10000);

        // Initial check
        checkNewOrders();
    </script>
</body>
</html>
