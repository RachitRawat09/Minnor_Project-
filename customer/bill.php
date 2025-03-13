<?php
session_start();
include '../includes/db_connect.php';

$billAvailable = false;
$order = null;
$orderDetails = [];
// ✅ Check if User has a Last Order in Session
if (!empty($_SESSION['last_order_id'])) {
    $order_id = $_SESSION['last_order_id'];
    $query = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) { // ✅ Ensure Statement Prepared Successfully
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // ✅ If Order Exists, Fetch Data Safely
        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();

            // ✅ Ensure 'order_details' Key Exists Before Decoding
            if (isset($order['order_details']) && !empty($order['order_details'])) {
                $decodedDetails = json_decode($order['order_details'], true);

                // ✅ Ensure JSON Decoded Properly as an Array
                if (is_array($decodedDetails)) {
                    $orderDetails = $decodedDetails;
                    $billAvailable = true;
                }
            }
        }
        $stmt->close();
    }
}
// ✅ Tax Calculations (Only if $billAvailable is true)
$subtotal = $billAvailable ? $order['total_price'] : 0;
$gst = round($subtotal * 0.05, 2);
$serviceCharge = round($subtotal * 0.10, 2);
$grandTotal = $subtotal + $gst + $serviceCharge;

// ✅ Set Payment Status & Payment Mode Initially
$paymentStatus = $billAvailable ? $order['payment_status'] : "None";
$paymentType = $billAvailable ? $order['payment_type'] : "Not Selected";




// ✅ Show Payment Status with Proper Formatting
if ($paymentStatus === 'Paid') {
    $paymentStatusDisplay = "<span class='badge bg-success'>Paid</span>";
} elseif ($paymentStatus === 'Pending') {
    $paymentStatusDisplay = "<span class='badge bg-warning'>Pending</span>";
} else {
    $paymentStatusDisplay = "<span class='badge bg-danger'>None</span>";
}

// ✅ Show Payment Type
$paymentTypeDisplay = $paymentType === "Not Selected" ? "<span class='badge bg-secondary'>Not Selected</span>" : "<span class='badge bg-info'>$paymentType</span>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill - CodeToCuisine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-size: 1.5rem; }
        .card { border-radius: 15px; }
        .table { border-radius: 10px; overflow: hidden; }
        .btn-lg { border-radius: 30px; }
        .animate-pulse { animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
    </style>
</head>
<body>

<?php if (!$billAvailable): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'No Bill Available!',
                text: 'Place Order First !! ',
                icon: 'info',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'index.php'; // ✅ Redirect to Home
            });
        });
    </script>
<?php exit(); ?>
<?php endif; ?>

<!-- ✅ Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary">
            <i class="fas fa-utensils"></i> CodeToCuisine
        </a>
        <a href="index.php" class="btn btn-outline-primary rounded-pill">
            <i class="fas fa-arrow-left me-2"></i> Back to Menu
        </a>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="text-center mb-4"><i class="fas fa-receipt me-2"></i>Restaurant Bill</h2>

    <div class="card shadow mb-4">
        <div class="card-body">
            <h5 class="card-title mb-4"><i class="fas fa-info-circle me-2"></i>Order Details</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <p class="mb-2"><i class="fas fa-hashtag me-2 text-primary"></i><span class="text-muted">Order ID:</span> <strong><?= $order['id'] ?></strong></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><i class="fas fa-chair me-2 text-primary"></i><span class="text-muted">Table No:</span> <strong><?= $order['table_number'] ?></strong></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><i class="fas fa-mobile-alt me-2 text-primary"></i><span class="text-muted">Mobile No:</span> <strong><?= $order['mobile_number'] ?></strong></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><i class="fas fa-shopping-bag me-2 text-primary"></i><span class="text-muted">Order Type:</span> <strong><?= $order['order_type'] ?></strong></p>
                </div>
                <div class="col-12">
                    <p class="mb-0">
                        <i class="fas fa-check-circle me-2 text-primary"></i><span class="text-muted">Payment Status:</span> 
                        <span id="payment-status" class="ms-2 <?= $order['payment_status'] === 'Paid' ? '' : 'animate-pulse' ?>">
                            <?= $paymentStatus ?>
                        </span>
                    </p>
                    <p><i class="fas fa-credit-card me-2 text-primary"></i>Payment Mode: 
                        <b id="payment-type"><?= $order['payment_type'] ? $order['payment_type'] : 'Not Selected'; ?></b>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <h5 class="card-title mb-4"><i class="fas fa-utensils me-2"></i>Order Items</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Item</th>
                            <th>Size</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderDetails as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= htmlspecialchars($item['size']) ?></td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td>₹<?= htmlspecialchars($item['price']) ?></td>
                            <td>₹<?= htmlspecialchars($item['total']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <h5 class="card-title mb-4"><i class="fas fa-calculator me-2"></i>Bill Summary</h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Subtotal:</strong> ₹<?= number_format($subtotal, 2) ?></p>
                    <p><strong>GST (5%):</strong> ₹<?= number_format($gst, 2) ?></p>
                    <p><strong>Service Charge (10%):</strong> ₹<?= number_format($serviceCharge, 2) ?></p>
                </div>
                <div class="col-md-6">
                    <h4 class="text-primary"><strong>Grand Total:</strong> ₹<?= number_format($grandTotal, 2) ?></h4>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Note: If Pay with Cash is selected, pay first so that order creation can begin.</p>
                </div>
            </div>
        </div>
    </div>

<!-- ✅ Payment Options -->
<div id="payment-buttons" class="text-center mt-4" <?= ($order['payment_type'] === "Not Selected" || empty($order['payment_type'])) ? '' : 'style="display:none;"' ?>>
    <button class="btn btn-success btn-lg me-3 mb-2" onclick="updatePaymentStatus('Cash')">
        <i class="fas fa-money-bill-wave me-2"></i> Pay with Cash
    </button>
    <button class="btn btn-primary btn-lg mb-2" onclick="updatePaymentStatus('Online')">
        <i class="fas fa-credit-card me-2"></i> Pay Online
    </button>
</div>

<!-- ✅ Print Button (Only Enabled if Payment is Done) -->
<button id="printBillBtn" onclick="window.print()" class="btn btn-secondary btn-lg w-100 mt-3" <?= ($order['payment_type'] !== "Not Selected" && !empty($order['payment_type'])) ? '' : 'disabled' ?>>
    <i class="fas fa-print me-2"></i> Print Bill
</button>


</div>

<script>
function updatePaymentStatus(method) {
    let orderId = <?= $order['id'] ?>;
    
    Swal.fire({
        title: "Confirm Payment?",
        text: `Are you sure you want to pay via ${method}?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, Confirm!",
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('update_payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `order_id=${orderId}&payment_method=${method}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // ✅ Update Payment Status
                    document.getElementById("payment-status").innerHTML = data.payment_status === "Paid" 
                        ? "<span class='badge bg-success'>Paid</span>" 
                        : "<span class='badge bg-warning'>Pending</span>";

                    // ✅ Update Payment Type
                    document.getElementById("payment-type").innerHTML = `<span class='badge bg-info'>${data.payment_type}</span>`;

                    // ✅ Hide Payment Buttons & Enable Print Bill
                    document.getElementById("payment-buttons").style.display = "none";
                    document.getElementById("printBillBtn").disabled = false;

                    Swal.fire("Payment Updated!", "Your payment status has been updated.", "success");
                } else {
                    Swal.fire("Error", "Failed to update payment status.", "error");
                }
            })
            .catch(error => console.error("Error:", error));
        }
    });
}
</script>

</body>
</html>

