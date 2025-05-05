<?php
session_start();
include '../includes/db_connect.php';

// Fetch Only Today's Orders
$query = "SELECT * FROM orders WHERE DATE(created_at) = CURDATE() ORDER BY created_at DESC";
$result = $conn->query($query);

// Define order statuses
$orderStatuses = [
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
    <title>Admin Orders - Today's Orders</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .order-card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .order-card:hover {
            transform: translateY(-5px);
        }
        .status-badge {
            font-size: 14px;
            padding: 8px 15px;
            border-radius: 20px;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            padding: 0.25rem 0.5rem;
            border-radius: 50%;
            font-size: 0.75rem;
        }
        .order-items-scroll {
            max-height: 300px;
            overflow-y: auto;
        }
        @keyframes highlight {
            0% { background-color: #fff; }
            50% { background-color: #fff3cd; }
            100% { background-color: #fff; }
        }
        .new-order {
            animation: highlight 2s ease-in-out;
        }
        .status-timeline {
            position: relative;
            padding: 20px 0;
        }
        .status-step {
            position: relative;
            padding-bottom: 20px;
        }
        .status-step::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 30px;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        .status-step:last-child::before {
            display: none;
        }
        .status-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary">
            <i class="fas fa-utensils"></i> CodeToCuisine Admin
        </a>
        <div class="d-flex align-items-center">
            <div class="position-relative me-3">
                <button class="btn btn-outline-warning rounded-circle" id="notificationBtn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge bg-danger text-white d-none" id="notificationCount">0</span>
                </button>
            </div>
            <a href="index.php" class="btn btn-outline-primary rounded-pill">
                <i class="fas fa-arrow-left me-2"></i>Dashboard
            </a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Today's Orders</h2>
        <div class="btn-group">
            <button class="btn btn-outline-primary" onclick="refreshOrders()">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
        </div>
    </div>

    <div id="ordersContainer">
        <?php if ($result->num_rows > 0) { 
            while ($order = $result->fetch_assoc()) { 
                $orderStatus = $order['payment_status'];
                $statusInfo = $orderStatuses[$orderStatus] ?? $orderStatuses['Pending'];
        ?>
            <div class="order-card card mb-3" id="order-<?= $order['id']; ?>" data-order-id="<?= $order['id']; ?>">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <h5 class="fw-bold mb-2">#<?= $order['id']; ?></h5>
                            <span class="badge bg-<?= $statusInfo['color']; ?> status-badge">
                                <i class="fas fa-<?= $statusInfo['icon']; ?> me-1"></i>
                                <?= $orderStatus; ?>
                            </span>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-user-circle fs-5 me-2 text-primary"></i>
                                <span><?= htmlspecialchars($order['mobile_number']); ?></span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-chair fs-5 me-2 text-success"></i>
                                <span>Table <?= htmlspecialchars($order['table_number']); ?></span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock fs-5 me-2 text-warning"></i>
                                <span><?= date('h:i A', strtotime($order['created_at'])); ?></span>
                            </div>
                        </div>

                        <div class="col-md-4 text-end">
                            <h5 class="text-primary mb-3">₹<?= number_format($order['total_price'], 2); ?></h5>
                            <button class="btn btn-outline-primary w-100" 
                                    onclick="viewOrderDetails(<?= $order['id']; ?>)">
                                <i class="fas fa-eye me-2"></i>View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Details Modal -->
            <div class="modal fade" id="orderModal<?= $order['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title">
                                <i class="fas fa-receipt me-2"></i>Order #<?= $order['id']; ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Order Information</h6>
                                    <p><strong>Type:</strong> <?= htmlspecialchars($order['order_type']); ?></p>
                                    <p><strong>Table:</strong> <?= htmlspecialchars($order['table_number']); ?></p>
                                    <p><strong>Mobile:</strong> <?= htmlspecialchars($order['mobile_number']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-3">Payment Information</h6>
                                    <p><strong>Status:</strong> <?= $order['payment_status']; ?></p>
                                    <p><strong>Type:</strong> <?= $order['payment_type'] ?? 'Not Specified'; ?></p>
                                    <p><strong>Total:</strong> ₹<?= number_format($order['total_price'], 2); ?></p>
                                </div>
                            </div>

                            <!-- Order Status Timeline -->
                            <div class="status-timeline mb-4">
                                <h6 class="text-muted mb-3">Order Status</h6>
                                <select class="form-select mb-3" 
                                        onchange="updateOrderStatus(<?= $order['id']; ?>, this.value)">
                                    <?php foreach ($orderStatuses as $status => $info): ?>
                                        <option value="<?= $status ?>" 
                                                <?= ($orderStatus === $status) ? 'selected' : ''; ?>>
                                            <?= $status ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Order Items -->
                            <h6 class="text-muted mb-3">Order Items</h6>
                            <div class="order-items-scroll">
                                <div class="list-group">
                                    <?php 
                                    $items = json_decode($order['order_details'], true);
                                    foreach ($items as $item): 
                                    ?>
                                        <div class="list-group-item">
                                            <div class="d-flex align-items-center">
                                                <img src="<?= htmlspecialchars($item['image']); ?>" 
                                                     class="rounded me-3" 
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0"><?= htmlspecialchars($item['name']); ?></h6>
                                                    <small class="text-muted">
                                                        Size: <?= htmlspecialchars($item['size']); ?> | 
                                                        Quantity: <?= htmlspecialchars($item['quantity']); ?>
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <h6 class="mb-0">₹<?= number_format($item['total'], 2); ?></h6>
                                                    <small class="text-muted">
                                                        ₹<?= number_format($item['price'], 2); ?> each
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <?php if ($order['customization']): ?>
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Special Instructions:</strong> 
                                    <?= htmlspecialchars($order['customization']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Close
                            </button>
                            <button type="button" class="btn btn-danger" 
                                    onclick="updateOrderStatus(<?= $order['id']; ?>, 'Cancelled')">
                                <i class="fas fa-times-circle me-2"></i>Cancel Order
                            </button>
                            <button type="button" class="btn btn-success" 
                                    onclick="updateOrderStatus(<?= $order['id']; ?>, 'Completed')">
                                <i class="fas fa-check-circle me-2"></i>Complete Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php } } else { ?>
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No orders placed today</h5>
            </div>
        <?php } ?>
    </div>
</div>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
let lastOrderId = <?= ($result->num_rows > 0) ? $result->data_seek(0) && $result->fetch_assoc()['id'] : 0 ?>;
let newOrdersCount = 0;
let activeOrderId = null;
let statusCheckInterval = null;

// Check for new orders every 30 seconds
setInterval(checkNewOrders, 30000);

function checkNewOrders() {
    $.ajax({
        url: 'check_new_orders.php',
        type: 'POST',
        data: { last_order_id: lastOrderId },
        success: function(response) {
            const data = JSON.parse(response);
            if (data.new_orders > 0) {
                newOrdersCount += data.new_orders;
                updateNotificationBadge();
                playNotificationSound();
                refreshOrders();
            }
        }
    });
}

function updateNotificationBadge() {
    const badge = document.getElementById('notificationCount');
    if (newOrdersCount > 0) {
        badge.textContent = newOrdersCount;
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }
}

function refreshOrders() {
    $.ajax({
        url: 'get_todays_orders.php',
        success: function(response) {
            $('#ordersContainer').html(response);
            newOrdersCount = 0;
            updateNotificationBadge();
            
            // If there was an active order being tracked, restart tracking
            if (activeOrderId) {
                startStatusTracking(activeOrderId);
            }
        }
    });
}

function updateOrderStatus(orderId, status) {
    console.log('Updating order status:', { orderId, status }); // Debug log
    
    Swal.fire({
        title: 'Update Order Status',
        text: `Are you sure you want to mark this order as ${status}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Update',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'update_order_status.php',
                type: 'POST',
                data: {
                    order_id: orderId,
                    status: status
                },
                success: function(response) {
                    console.log('Server response:', response); // Debug log
                    try {
                        const data = JSON.parse(response);
                        if (data.success) {
                            Swal.fire('Updated!', 'Order status has been updated.', 'success');
                            refreshOrders();
                            
                            // Update the status in the modal if it's open
                            if (activeOrderId === orderId) {
                                updateStatusInModal(data.order.status);
                            }
                        } else {
                            Swal.fire('Error!', data.message || 'Failed to update order status.', 'error');
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        Swal.fire('Error!', 'Invalid server response.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', { xhr, status, error });
                    Swal.fire('Error!', 'Failed to connect to server. Please try again.', 'error');
                }
            });
        }
    });
}

function viewOrderDetails(orderId) {
    $(`#orderModal${orderId}`).modal('show');
    startStatusTracking(orderId);
}

function startStatusTracking(orderId) {
    // Clear any existing interval
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval);
    }
    
    activeOrderId = orderId;
    
    // Check status every 10 seconds
    statusCheckInterval = setInterval(() => {
        checkOrderStatus(orderId);
    }, 10000);
    
    // Initial check
    checkOrderStatus(orderId);
}

function checkOrderStatus(orderId) {
    $.ajax({
        url: 'check_order_status.php',
        type: 'POST',
        data: { order_id: orderId },
        success: function(response) {
            const data = JSON.parse(response);
            if (data.success) {
                updateStatusInModal(data.order.status);
            }
        }
    });
}

function updateStatusInModal(status) {
    const statusSelect = document.querySelector(`#orderModal${activeOrderId} select.form-select`);
    if (statusSelect) {
        statusSelect.value = status;
        
        // Update status badge
        const statusBadge = document.querySelector(`#order-${activeOrderId} .status-badge`);
        if (statusBadge) {
            const statusInfo = {
                'Pending': { color: 'warning', icon: 'clock' },
                'Processing': { color: 'info', icon: 'sync' },
                'Preparing': { color: 'primary', icon: 'utensils' },
                'Ready': { color: 'success', icon: 'check-circle' },
                'Completed': { color: 'success', icon: 'flag-checkered' },
                'Cancelled': { color: 'danger', icon: 'times-circle' }
            }[status] || { color: 'warning', icon: 'clock' };
            
            statusBadge.className = `badge bg-${statusInfo.color} status-badge`;
            statusBadge.innerHTML = `<i class="fas fa-${statusInfo.icon} me-1"></i>${status}`;
        }
    }
}

// Stop tracking when modal is closed
$('.modal').on('hidden.bs.modal', function() {
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval);
        statusCheckInterval = null;
    }
    activeOrderId = null;
});

// Notification sound
function playNotificationSound() {
    const audio = new Audio('notification.mp3');
    audio.play();
}

// Clear notifications when button is clicked
document.getElementById('notificationBtn').addEventListener('click', function() {
    newOrdersCount = 0;
    updateNotificationBadge();
});
</script>

</body>
</html>
