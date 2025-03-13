<?php
// session_start();
include '../includes/db_connect.php';

// ✅ Fetch All Orders (No Date Filter)
$query = "SELECT * FROM orders ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders - All Orders</title>
    
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
    <h2 class="text-center mb-4">📦 All Orders</h2>

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

    <?php } } else { ?>
        <p class="text-center text-muted">No orders found.</p>
    <?php } ?>
</div>

<!-- ✅ Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>