<?php
include '../includes/db_connect.php';
session_start();
// Require restaurant_id in URL
if (!isset($_GET['restaurant_id']) || !is_numeric($_GET['restaurant_id'])) {
    die('<div style="color:red;text-align:center;margin-top:2rem;">Invalid or missing restaurant ID.</div>');
}
$restaurant_id = intval($_GET['restaurant_id']);

// Grab the user's mobile number from session or POST
$mobile_number = $_SESSION['mobile_number'] ?? $_POST['mobile_number'] ?? '';
$orders = [];

if (!empty($mobile_number)) {
    // Pull all orders for this number, newest first
    $query = "SELECT * FROM orders 
              WHERE mobile_number = ? 
              ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $mobile_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Turn the order details JSON into an array
        $row['order_items'] = json_decode($row['order_details'], true);
        $orders[] = $row;
    }
    $stmt->close();
}

// This array helps us show progress for each order
$orderStatuses = [
    'Pending' => 1,
    'Processing' => 2,
    'Preparing' => 3,
    'Ready' => 4,
    'Completed' => 5,
    'Cancelled' => 0
];

// Color and icon info for each order status
$statusInfo = [
    'Pending' => ['color' => 'warning', 'icon' => 'clock'],
    'Processing' => ['color' => 'info', 'icon' => 'sync'],
    'Preparing' => ['color' => 'primary', 'icon' => 'utensils'],
    'Ready' => ['color' => 'success', 'icon' => 'check-circle'],
    'Completed' => ['color' => 'success', 'icon' => 'flag-checkered'],
    'Cancelled' => ['color' => 'danger', 'icon' => 'times-circle']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking - CodeToCuisine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .glow {
            box-shadow: 0 0 15px #00f;
            animation: glowEffect 1.5s infinite alternate;
        }
        @keyframes glowEffect {
            from { box-shadow: 0 0 10px #00f; }
            to { box-shadow: 0 0 20px #00f; }
        }
        .progress-step.active {
            background-color: #0d6efd;
            color: white;
        }
        .progress-step.completed {
            background-color: #198754;
            color: white;
        }
        .timeline-connector {
            transition: width 0.5s ease-in-out;
        }
        .status-badge {
            font-size: 14px;
            padding: 8px 15px;
            border-radius: 20px;
        }
    </style>
</head>
<body class="bg-gray-100">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary">
            <i class="fas fa-utensils"></i> CodeToCuisine
        </a>
        <div>
            <a href="index.php?restaurant_id=<?= $restaurant_id ?>" class="btn btn-outline-primary rounded-pill me-2">
                <i class="fas fa-arrow-left"></i> Menu
            </a>
            <a href="cart.php?restaurant_id=<?= $restaurant_id ?>" class="btn btn-outline-success rounded-pill">
                <i class="fas fa-shopping-cart"></i> Cart
            </a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <!-- Search Order Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3"><i class="fas fa-search me-2"></i>Track Your Order</h5>
            <form id="searchForm" method="POST" class="row g-3">
                <div class="col-md-8">
                    <input type="tel" name="mobile_number" class="form-control" 
                           placeholder="Enter your mobile number" 
                           value="<?= htmlspecialchars($mobile_number) ?>"
                           pattern="[0-9]{10}" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Find Orders
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $order): ?>
            <div class="card shadow-sm mb-4" id="order-<?= $order['id'] ?>">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-receipt me-2"></i>
                            Order #<?= $order['id'] ?>
                        </h5>
                        <span class="badge bg-<?= $statusInfo[$order['order_status']]['color'] ?> status-badge">
                            <i class="fas fa-<?= $statusInfo[$order['order_status']]['icon'] ?> me-1"></i>
                            <?= $order['order_status'] ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Order Details -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><i class="fas fa-clock me-2"></i>Ordered: 
                                <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?>
                            </p>
                            <p><i class="fas fa-table me-2"></i>Table: <?= $order['table_number'] ?></p>
                            <p><i class="fas fa-money-bill me-2"></i>Payment: <?= $order['payment_type'] ?? 'Not specified' ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><i class="fas fa-shopping-bag me-2"></i>Type: <?= $order['order_type'] ?></p>
                            <p><i class="fas fa-rupee-sign me-2"></i>Total: ₹<?= number_format($order['total_price'], 2) ?></p>
                            <?php if ($order['customization']): ?>
                                <p><i class="fas fa-edit me-2"></i>Note: <?= htmlspecialchars($order['customization']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="table-responsive mb-4">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Size</th>
                                    <th>Quantity</th>
                                    <th class="text-end">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['order_items'] as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['name']) ?></td>
                                        <td><?= htmlspecialchars($item['size']) ?></td>
                                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                                        <td class="text-end">₹<?= number_format($item['total'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Progress Timeline -->
                    <div class="position-relative mb-4">
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: <?= ($orderStatuses[$order['order_status']] / 5) * 100 ?>%"
                                 aria-valuenow="<?= $orderStatuses[$order['order_status']] ?>" 
                                 aria-valuemin="0" aria-valuemax="5">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between position-relative" style="margin-top: -15px;">
                            <?php
                            $steps = [
                                ["Order Placed", "fa-shopping-cart"],
                                ["Processing", "fa-sync"],
                                ["Preparing", "fa-utensils"],
                                ["Ready", "fa-check-circle"],
                                ["Completed", "fa-flag-checkered"]
                            ];

                            foreach ($steps as $index => $info):
                                $isCompleted = $index < $orderStatuses[$order['order_status']];
                                $isActive = $index == $orderStatuses[$order['order_status']] - 1;
                                $statusClass = $isCompleted ? 'completed' : ($isActive ? 'active' : '');
                            ?>
                                <div class="text-center">
                                    <div class="progress-step rounded-circle <?= $statusClass ?>"
                                         style="width: 30px; height: 30px; line-height: 30px; background: <?= $isCompleted || $isActive ? '' : '#e9ecef' ?>">
                                        <i class="fas <?= $info[1] ?> fa-sm"></i>
                                    </div>
                                    <div class="mt-2 small"><?= $info[0] ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Status Message -->
                    <div class="mt-3">
                        <?php if ($order['order_status'] === 'Pending'): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-clock me-2"></i>Your order is being processed
                            </div>
                        <?php elseif ($order['order_status'] === 'Completed'): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>Order has been completed
                            </div>
                        <?php elseif ($order['order_status'] === 'Cancelled'): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-times-circle me-2"></i>Order has been cancelled
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>Your order is currently <?= strtolower($order['order_status']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php elseif (!empty($mobile_number)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No orders found for this mobile number.
        </div>
    <?php endif; ?>
</div>

<!-- Real-time Updates Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Store the last known status for each order
    const lastKnownStatus = {};

    // Function to update order status
    function updateOrderStatus(orderId, status) {
        const orderCard = document.getElementById(`order-${orderId}`);
        if (!orderCard) return;

        // Check if status has changed
        if (lastKnownStatus[orderId] === status) {
            return; // No change in status
        }

        // Update last known status
        lastKnownStatus[orderId] = status;

        // Add a highlight effect to show the update
        orderCard.classList.add('glow');
        setTimeout(() => orderCard.classList.remove('glow'), 2000);

        // Update status badge
        const statusBadge = orderCard.querySelector('.status-badge');
        const statusInfo = {
            'Pending': { color: 'warning', icon: 'clock' },
            'Processing': { color: 'info', icon: 'sync' },
            'Preparing': { color: 'primary', icon: 'utensils' },
            'Ready': { color: 'success', icon: 'check-circle' },
            'Completed': { color: 'success', icon: 'flag-checkered' },
            'Cancelled': { color: 'danger', icon: 'times-circle' }
        }[status];

        statusBadge.className = `badge bg-${statusInfo.color} status-badge`;
        statusBadge.innerHTML = `<i class="fas fa-${statusInfo.icon} me-1"></i>${status}`;

        // Update progress bar with animation
        const progressBar = orderCard.querySelector('.progress-bar');
        const statusValue = {
            'Pending': 1,
            'Processing': 2,
            'Preparing': 3,
            'Ready': 4,
            'Completed': 5,
            'Cancelled': 0
        }[status];

        // Animate progress bar
        progressBar.style.transition = 'width 0.5s ease-in-out';
        progressBar.style.width = `${(statusValue / 5) * 100}%`;
        progressBar.setAttribute('aria-valuenow', statusValue);

        // Update progress steps with animation
        const steps = orderCard.querySelectorAll('.progress-step');
        steps.forEach((step, index) => {
            const isCompleted = index < statusValue;
            const isActive = index == statusValue - 1;
            
            // Add transition for smooth color change
            step.style.transition = 'background-color 0.5s ease-in-out';
            step.className = `progress-step rounded-circle ${isCompleted ? 'completed' : (isActive ? 'active' : '')}`;
            step.style.background = isCompleted || isActive ? '' : '#e9ecef';
        });

        // Update status message with animation
        const statusMessage = orderCard.querySelector('.alert');
        if (statusMessage) {
            const message = {
                'Pending': 'Your order is being processed',
                'Processing': 'Your order is being processed',
                'Preparing': 'Your order is being prepared',
                'Ready': 'Your order is ready',
                'Completed': 'Order has been completed',
                'Cancelled': 'Order has been cancelled'
            }[status];

            const icon = {
                'Pending': 'clock',
                'Processing': 'sync',
                'Preparing': 'utensils',
                'Ready': 'check-circle',
                'Completed': 'check-circle',
                'Cancelled': 'times-circle'
            }[status];

            const alertClass = {
                'Pending': 'warning',
                'Processing': 'info',
                'Preparing': 'info',
                'Ready': 'success',
                'Completed': 'success',
                'Cancelled': 'danger'
            }[status];

            // Add fade effect
            statusMessage.style.transition = 'opacity 0.5s ease-in-out';
            statusMessage.style.opacity = '0';
            
            setTimeout(() => {
                statusMessage.className = `alert alert-${alertClass}`;
                statusMessage.innerHTML = `<i class="fas fa-${icon} me-2"></i>${message}`;
                statusMessage.style.opacity = '1';
            }, 500);
        }

        // Show notification for status change
        if (status !== 'Pending') {
            Swal.fire({
                title: 'Order Status Updated!',
                text: `Your order #${orderId} is now ${status}`,
                icon: 'info',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    }

    // Initialize last known status for all orders
    document.querySelectorAll('[id^="order-"]').forEach(orderCard => {
        const orderId = orderCard.id.split('-')[1];
        const statusBadge = orderCard.querySelector('.status-badge');
        lastKnownStatus[orderId] = statusBadge.textContent.trim();
    });

    // Check for status updates every 5 seconds
    setInterval(function() {
        const orderIds = Array.from(document.querySelectorAll('[id^="order-"]')).map(el => el.id.split('-')[1]);
        
        orderIds.forEach(orderId => {
            fetch('check_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_id=${orderId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateOrderStatus(orderId, data.order.status);
                }
            })
            .catch(error => console.error('Error checking order status:', error));
        });
    }, 5000); // Check every 5 seconds instead of 10
});
</script>

</body>
</html>
