<?php
// session_start();
include '../includes/db_connect.php';

// ✅ Fetch Only Today's Orders
$query = "SELECT * FROM orders WHERE DATE(created_at) = CURDATE() ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders - Today's Orders</title>
    
    <!-- ✅ Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        .order-card {
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            padding: 15px;
            background: white;
            margin-bottom: 15px;
        }
        .status-badge {
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .pending { background-color: #ffcc00; color: #000; }
        .completed { background-color: #28a745; color: #fff; }
        .cancelled { background-color: #dc3545; color: #fff; }
    </style>
</head>
<body>

<!-- ✅ Navbar -->
<nav class="navbar navbar-light bg-white shadow-sm p-3">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand fw-bold text-primary">
            <i class="fas fa-utensils"></i> CodeToCuisine Admin
        </a>
        <a href="index.php" class="btn btn-outline-primary rounded-pill">
            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
        </a>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="text-center mb-4">📦 Today's Orders</h2>

    <?php if ($result->num_rows > 0) { 
        while ($order = $result->fetch_assoc()) { 
            $orderStatus = $order['order_status'] ?? 'Pending';
            $statusClass = ($orderStatus === 'Completed') ? 'completed' : (($orderStatus === 'Cancelled') ? 'cancelled' : 'pending');
    ?>

    <div class="order-card p-3">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                <h5 class="fw-bold mb-1">#<?= $order['id']; ?></h5>
                <span class="badge status-badge <?= $statusClass; ?>"><?= $orderStatus; ?></span>
            </div>

            <div class="col-md-6">
                <h6><i class="fas fa-user"></i> Mobile: <b><?= htmlspecialchars($order['mobile_number']); ?></b></h6>
                <h6><i class="fas fa-chair"></i> Table No: <b><?= htmlspecialchars($order['table_number']); ?></b></h6>
                <h6><i class="fas fa-clock"></i> Placed On: <b><?= date('d M Y, h:i A', strtotime($order['created_at'])); ?></b></h6>
            </div>

            <div class="col-md-4">
                <h6 class="text-end"><i class="fas fa-money-bill-wave"></i> Total: <b>₹<?= number_format($order['total_price'], 2); ?></b></h6>
                <button class="btn btn-outline-info btn-sm mt-2 w-100" data-bs-toggle="modal" data-bs-target="#orderModal<?= $order['id']; ?>">
                    <i class="fas fa-eye"></i> View Details
                </button>
            </div>
        </div>
    </div>

    <!-- ✅ Order Details Modal -->
    <div class="modal fade" id="orderModal<?= $order['id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order #<?= $order['id']; ?> Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6><b>Order Type:</b> <?= htmlspecialchars($order['order_type'] ?? 'N/A'); ?></h6>
                    <h6><b>Customization Request:</b> <?= htmlspecialchars($order['customization'] ?? 'None'); ?></h6>
                    <h6><b>Payment Mode:</b> <?= htmlspecialchars($order['payment_mode'] ?? 'Not Specified'); ?></h6>

                    <?php 
                    $paymentStatus = $order['payment_status'] ?? 'Unpaid'; 
                    ?>

                    <h6><b>Payment Status:</b> 
                        <select class="form-select" onchange="updatePaymentStatus(<?= $order['id']; ?>, this.value)">
                            <option value="Paid" <?= ($paymentStatus == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                            <option value="Unpaid" <?= ($paymentStatus == 'Unpaid') ? 'selected' : ''; ?>>Unpaid</option>
                        </select>
                    </h6>

                    <ul class="list-group mt-3">
                        <?php 
                        $items = json_decode($order['order_details'], true) ?? [];
                        foreach ($items as $item) { ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="<?= htmlspecialchars($item['image'] ?? 'default.jpg'); ?>" 
                                         alt="<?= htmlspecialchars($item['name'] ?? 'Unknown Item'); ?>" 
                                         class="me-3 rounded" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                    <div>
                                        <strong><?= htmlspecialchars($item['name'] ?? 'Unknown Item'); ?></strong> 
                                        (<?= htmlspecialchars($item['size'] ?? 'N/A'); ?>)
                                        <small class="text-muted">₹<?= htmlspecialchars($item['price'] ?? '0'); ?> x <?= htmlspecialchars($item['quantity'] ?? '0'); ?></small>
                                    </div>
                                </div>
                                <b>₹<?= htmlspecialchars($item['total'] ?? '0'); ?></b>
                            </li>
                        <?php } ?>
                    </ul>

                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" onclick="cancelOrder(<?= $order['id']; ?>)">❌ Cancel Order</button>
                    <button class="btn btn-success" onclick="completeOrder(<?= $order['id']; ?>)">✅ Mark as Completed</button>
                </div>
            </div>
        </div>
    </div>

    <?php } } else { ?>
        <p class="text-center text-muted">No orders placed today.</p>
    <?php } ?>
</div>

<!-- ✅ Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
