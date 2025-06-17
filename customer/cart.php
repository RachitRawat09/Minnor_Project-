<?php
session_start();
include '../includes/db_connect.php';

// ✅ Handle Quantity Update via AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_quantity"])) {
    $index = $_POST["index"];
    $newQuantity = $_POST["quantity"];

    if (isset($_SESSION["cart"][$index]) && $newQuantity > 0) {
        $_SESSION["cart"][$index]["quantity"] = $newQuantity;
        $_SESSION["cart"][$index]["total"] = $newQuantity * $_SESSION["cart"][$index]["price"];
    }

    echo json_encode([
        "success" => true, 
        "quantity" => $_SESSION["cart"][$index]["quantity"],
        "total" => $_SESSION["cart"][$index]["total"],
        "grandTotal" => array_sum(array_column($_SESSION["cart"], "total")),
        "cartEmpty" => empty($_SESSION["cart"])
    ]);
    exit();
}

// ✅ Handle Item Removal via AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["remove_item"])) {
    $index = $_POST["index"];

    if (isset($_SESSION["cart"][$index])) {
        unset($_SESSION["cart"][$index]);
        $_SESSION["cart"] = array_values($_SESSION["cart"]); // ✅ Re-index array
    }

    echo json_encode([
        "success" => true,
        "grandTotal" => array_sum(array_column($_SESSION["cart"], "total")),
        "cartEmpty" => empty($_SESSION["cart"])
    ]);
    exit();
}

// ✅ Handle Order Confirmation & Save to DB
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["confirm_order"])) {
    if (!isset($_SESSION["cart"]) || empty($_SESSION["cart"])) {
        echo json_encode(["status" => "error", "message" => "Cart is empty."]);
        exit();
    }

    // ✅ Get User Inputs from AJAX
    $mobile_number = $_POST["mobile_number"];
    $table_number = (int)$_POST["table_number"]; // ✅ Typecasting to integer
    $order_type = $_POST["order_type"];
    $customization = $_POST["customization"] ?? null; // ✅ Handling optional input
    $payment_type = $_POST["payment_type"] ?? "Not Selected"; // ✅ Capture selected payment method

    // ✅ Set Payment Status Based on Payment Type
    $payment_status = ($payment_type === "Online") ? "Paid" : "Pending";

    // ✅ Prepare Order Data
    $orderDetails = json_encode($_SESSION["cart"], JSON_UNESCAPED_UNICODE);
    $totalPrice = array_sum(array_column($_SESSION["cart"], "total"));

    // ✅ Insert Order into DB
    $stmt = $conn->prepare("INSERT INTO orders (mobile_number, table_number, order_details, total_price, order_type, customization, payment_status, payment_type, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    if ($stmt === false) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        exit();
    }

    // ✅ Bind parameters correctly
    $stmt->bind_param("sissssss", $mobile_number, $table_number, $orderDetails, $totalPrice, $order_type, $customization, $payment_status, $payment_type);

    if ($stmt->execute()) {
        $_SESSION["cart"] = []; // ✅ Clear Cart after Order Placed
        $_SESSION['last_order_id'] = $conn->insert_id; // ✅ Store the last inserted order ID in session

        echo json_encode(["status" => "success", "message" => "Order placed successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to place order. Error: " . $stmt->error]);
    }

    $stmt->close();
    exit();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - CodeToCuisine</title>
    
    <!-- ✅ Bootstrap & SweetAlert -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .cart-container {
            max-width: 900px;
            margin: 40px auto;
        }
        .cart-card {
            border-radius: 12px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            background: white;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            padding: 15px;
            position: relative;
            transition: transform 0.2s ease-in-out;
        }
        .cart-card:hover {
            transform: scale(1.02);
        }
        .cart-card img {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 10px;
        }
        .cart-item {
            flex-grow: 1;
            margin-left: 15px;
        }
        .cart-item h5 {
            margin-bottom: 5px;
        }
        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.2s;
        }
        .remove-btn:hover {
            background: #b02a37;
        }

        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            margin: 0; 
        }

    </style>
</head>
<body>

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

<div class="container cart-container">
    <h2 class="text-center mb-4">🛒 Your Cart</h2>
         <!-- ✅ Order Details Section -->
    <div class="cart-details p-3 border rounded mb-3">
        <h5 class="mb-3">Order Details:</h5>
        <div class="mb-2">
            <label><strong>Table No:</strong></label>
            <input type="number" id="tableNumber" class="form-control" placeholder="Enter Your Table Number" required>
        </div>
        <div class="mb-2">
            <label><strong>Mobile No:</strong></label>
            <input type="number" id="mobileNumber" class="form-control" placeholder="Enter Your Mobile Number" required>
        </div>
        <div class="mb-2">
            <label><strong>Order Type:</strong></label>
            <select id="orderType" class="form-control">
                <option value="Dine In" >Dine In</option>
            </select>
        </div>
    </div>

    
    <div id="cartItems">
        <?php if (!empty($_SESSION["cart"])): ?>
            <?php foreach ($_SESSION["cart"] as $index => $item): ?>
                <div class="cart-card" data-index="<?= $index ?>">
                    <img src="../uploads/<?= htmlspecialchars($item["image"]) ?>" alt="<?= htmlspecialchars($item["name"]) ?>">
                    <div class="cart-item">
                        <h5><?= htmlspecialchars($item["name"]) ?> (<?= htmlspecialchars($item["size"]) ?>)</h5>
                        <p class="text-muted">₹<?= htmlspecialchars($item["price"]) ?> x 
                            <span class="item-quantity"><?= htmlspecialchars($item["quantity"]) ?></span>
                        </p>


                        
                        <div class="quantity-controls">
                            <button onclick="updateQuantity(<?= $index ?>, -1)">-</button>
                            
                            <button onclick="updateQuantity(<?= $index ?>, 1)">+</button>
                        </div>

                       
                        
                    </div>
                    <div>
                        <p class="fw-bold">₹<span class="item-total"><?= htmlspecialchars($item["total"]) ?></span></p>
                        <button class="remove-btn" onclick="removeItem(<?= $index ?>)">🗑</button>
                    </div>
                </div>
            <?php endforeach; ?>
            <h4 class="text-end mt-3">Total: ₹<span id="totalPrice"><?= array_sum(array_column($_SESSION["cart"], "total")) ?></span></h4>
            <button id="confirmOrderBtn" onclick="confirmOrder()" class="btn btn-success w-100 mt-3">Confirm Order & Generate Bill</button>
        <?php else: ?>
            <p class="text-center" id="emptyCartMessage">Add Food items First !! 🛒</p>
        <?php endif; ?>
    </div>
</div>

<!-- ✅ Bootstrap & jQuery for AJAX -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    function updateQuantity(index, change) {
    let quantityElement = document.querySelector(`.cart-card[data-index='${index}'] .item-quantity`);
    let totalElement = document.querySelector(`.cart-card[data-index='${index}'] .item-total`);
    
    let newQuantity = parseInt(quantityElement.innerText) + change;
    if (newQuantity < 1) return;

    $.post("cart.php", { update_quantity: 1, index: index, quantity: newQuantity }, function(response) {
        let data = JSON.parse(response);
        if (data.success) {
            quantityElement.innerText = data.quantity;
            totalElement.innerText = data.total.toFixed(2);
            $("#totalPrice").text(data.grandTotal.toFixed(2));
        }
    });
}
function removeItem(index) {
    $.post("cart.php", { remove_item: 1, index: index }, function(response) {
        $(".cart-card[data-index='" + index + "']").remove();
        let data = JSON.parse(response);
        $("#totalPrice").text(data.grandTotal.toFixed(2));

        if (data.cartEmpty) {
            $("#confirmOrderBtn").remove();
            $("#cartItems").html('<p class="text-center">Your cart is empty! 🛒</p>');
        }
    });
}

function confirmOrder() {
    let mobileNumber = $("#mobileNumber").val();
    let tableNumber = $("#tableNumber").val();
    let orderType = $("#orderType").val();
    let customization = $("#customization").val(); // ✅ Get customization input

    if (!mobileNumber || !tableNumber || !orderType) {
        Swal.fire("Error", "Please fill all details!", "error");
        return;
    }

    Swal.fire({
    title: "Confirm Order?",
    text: "Are you sure you want to place this order?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Yes, Confirm!"
}).then((result) => {
    if (result.isConfirmed) {
        $.ajax({
            url: "cart.php",
            type: "POST",
            data: {
                confirm_order: 1,
                mobile_number: mobileNumber,
                table_number: tableNumber,
                order_type: orderType,
                customization: customization
            },
            dataType: "json",
            success: function(response) {
                console.log(response); // ✅ Debugging ke liye

                if (response.status === "success") {
                    Swal.fire({
                        title: "Order Placed!",
                        text: "Your order has been placed successfully!",
                        icon: "success",
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false 
                    }).then(() => {
                        window.location.href = "bill.php"; // ✅ Redirect
                    });
                } else {
                    Swal.fire("Error", response.message, "error");
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText); // ✅ Debugging ke liye error console me dikhayega
                Swal.fire("Error", "Something went wrong. Please try again.", "error");
            }
        });
    }
});

}


</script>

</body>
</html>
