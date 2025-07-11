<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['restaurant_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$restaurant_id = $_SESSION['restaurant_id'];
include '../includes/db_connect.php';

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$date_filter = $_GET['date'] ?? date('Y-m-d');
$search_query = $_GET['search'] ?? '';

// Build the query
$query = "SELECT * FROM orders WHERE restaurant_id = ?";
$params = [$restaurant_id];
$types = "i";

if ($status_filter !== 'all') {
    $query .= " AND order_status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($date_filter) {
    $query .= " AND DATE(created_at) = ?";
    $params[] = $date_filter;
    $types .= "s";
}

if ($search_query) {
    $query .= " AND (mobile_number LIKE ? OR table_number LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$query .= " ORDER BY created_at DESC";

// Prepare and execute the query
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders - All Orders</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

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
            transition: transform 0.2s;
        }
        .order-card:hover {
            transform: translateY(-2px);
        }
        .status-badge {
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .pending { background-color: #ffcc00; color: #000; }
        .processing { background-color: #17a2b8; color: #fff; }
        .preparing { background-color: #007bff; color: #fff; }
        .ready { background-color: #28a745; color: #fff; }
        .completed { background-color: #28a745; color: #fff; }
        .cancelled { background-color: #dc3545; color: #fff; }
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .search-box {
            position: relative;
        }
        .search-box i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        .search-box input {
            padding-left: 35px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-light bg-white shadow-sm p-3">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand fw-bold text-primary">
            <i class="fas fa-utensils"></i> CodeToCuisine Admin
        </a>
        <a href="restaurant_dashboard.php" class="btn btn-outline-primary rounded-pill">
            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
        </a>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="text-center mb-4">📦 All Orders</h2>

    <!-- Filter Section -->
    <div class="filter-section">
        <form id="filterForm" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Processing" <?= $status_filter === 'Processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="Preparing" <?= $status_filter === 'Preparing' ? 'selected' : '' ?>>Preparing</option>
                    <option value="Ready" <?= $status_filter === 'Ready' ? 'selected' : '' ?>>Ready</option>
                    <option value="Completed" <?= $status_filter === 'Completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date</label>
                <input type="text" name="date" class="form-control datepicker" value="<?= $date_filter ?>" onchange="this.form.submit()">
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="Search by mobile or table number" value="<?= htmlspecialchars($search_query) ?>">
                </div>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Orders List -->
    <div id="ordersList">
        <?php if ($result->num_rows > 0) { 
            while ($order = $result->fetch_assoc()) { 
                $orderStatus = $order['order_status'] ?? 'Pending';
                $statusClass = match($orderStatus) {
                    'Processing' => 'processing',
                    'Preparing' => 'preparing',
                    'Ready' => 'ready',
                    'Completed' => 'completed',
                    'Cancelled' => 'cancelled',
                    default => 'pending'
                };
        ?>

        <div class="order-card p-3" id="order-<?= $order['id']; ?>">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <h5 class="fw-bold mb-1">#<?= $order['id']; ?></h5>
                    <span class="badge status-badge <?= $statusClass; ?>">
                        <i class="fas fa-<?= match($orderStatus) {
                            'Processing' => 'sync',
                            'Preparing' => 'utensils',
                            'Ready' => 'check-circle',
                            'Completed' => 'flag-checkered',
                            'Cancelled' => 'times-circle',
                            default => 'clock'
                        } ?> me-1"></i>
                        <?= $orderStatus; ?>
                    </span>
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
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No orders found</h5>
                <p class="text-muted">Try adjusting your filters or search criteria</p>
            </div>
        <?php } ?>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date picker
    flatpickr(".datepicker", {
        dateFormat: "Y-m-d",
        maxDate: "today"
    });

    // Function to update order status
    function updateOrderStatus(orderId, status) {
        const orderCard = document.getElementById(`order-${orderId}`);
        if (!orderCard) return;

        const statusBadge = orderCard.querySelector('.status-badge');
        const statusInfo = {
            'Pending': { class: 'pending', icon: 'clock' },
            'Processing': { class: 'processing', icon: 'sync' },
            'Preparing': { class: 'preparing', icon: 'utensils' },
            'Ready': { class: 'ready', icon: 'check-circle' },
            'Completed': { class: 'completed', icon: 'flag-checkered' },
            'Cancelled': { class: 'cancelled', icon: 'times-circle' }
        }[status];

        statusBadge.className = `badge status-badge ${statusInfo.class}`;
        statusBadge.innerHTML = `<i class="fas fa-${statusInfo.icon} me-1"></i>${status}`;
    }

    // Check for status updates every 10 seconds
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
    }, 10000);

    // Debounce search input
    let searchTimeout;
    const searchInput = document.querySelector('input[name="search"]');
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 500);
    });
});
</script>
</body>
</html>