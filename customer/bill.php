<?php
session_start();

// ✅ Ensure there's a pending order
if (!isset($_SESSION["pending_order"])) {
    die("<script>alert('No pending order found!'); window.location.href='index.php';</script>");
}

// ✅ Retrieve order details from session
$orderDetails = $_SESSION["pending_order"]["order_details"];
$totalPrice = $_SESSION["pending_order"]["total_price"];
$orderDate = date("d M Y, h:i A");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Bill - CodeToCuisine</title>

    <!-- ✅ Bootstrap & SweetAlert -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .bill-container {
            max-width: 450px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .bill-header {
            text-align: center;
            font-weight: bold;
            font-size: 22px;
            color: #007bff;
            margin-bottom: 15px;
        }
        .bill-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dashed #ddd;
        }
        .bill-footer {
            font-weight: bold;
            font-size: 18px;
            color: #28a745;
            text-align: right;
        }
        .btn-download, .btn-pay {
            display: block;
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-download {
            background: #007bff;
            color: white;
            border: none;
        }
        .btn-pay {
            background: #28a745;
            color: white;
            border: none;
        }
    </style>
</head>
<body>

<!-- ✅ Navbar -->
<nav class="navbar navbar-light bg-white shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand fw-bold text-primary">
            <i class="fas fa-utensils"></i> CodeToCuisine
        </a>
        <a href="index.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Menu
        </a>
    </div>
</nav>

<!-- ✅ Bill Section -->
<div id="billSection" class="bill-container">
    <div class="bill-header">CodeToCuisine 🍽</div>
    <div class="text-center text-muted">Order Date: <?= $orderDate ?></div>
    <hr>

    <?php foreach ($orderDetails as $item): ?>
        <div class="bill-item">
            <span><?= htmlspecialchars($item["name"]) ?> (<?= htmlspecialchars($item["size"]) ?>)</span>
            <span>₹<?= number_format($item["total"], 2) ?></span>
        </div>
    <?php endforeach; ?>

    <hr>
    <div class="bill-footer">Total: ₹<?= number_format($totalPrice, 2) ?></div>
    
    <!-- ✅ Payment Options -->
    <button class="btn-pay mt-3" onclick="confirmPayment('Online')">💳 Pay Online</button>
    <button class="btn-pay mt-2 bg-danger" onclick="confirmPayment('Cash')">💵 Cash on Counter</button>
    
    <button class="btn-download mt-3" onclick="downloadBill()">📝 Download Bill (PDF)</button>
</div>

<!-- ✅ JavaScript for Storing Order in DB & Downloading Bill -->
<script>
function downloadBill() {
    const bill = document.getElementById("billSection");
    html2pdf().from(bill).save("CodeToCuisine_Bill.pdf");
}

function confirmPayment(method) {
    Swal.fire({
        title: "Confirm Payment?",
        text: "Proceed with " + method + " payment?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, Confirm",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            // ✅ Send payment method to `process_payment.php`
            fetch("process_payment.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "payment_method=" + method
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    Swal.fire("Order Confirmed!", "Thank you for your payment.", "success")
                    .then(() => {
                        window.location.href = "index.php"; // ✅ Redirect to homepage after payment
                    });
                } else {
                    Swal.fire("Error!", data.message, "error");
                }
            });
        }
    });
}
</script>

</body>
</html>
