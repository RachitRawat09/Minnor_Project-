<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["payment_method"])) {
    if (!isset($_SESSION["pending_order"])) {
        echo json_encode(["status" => "error", "message" => "No pending order found."]);
        exit();
    }

    $orderDetails = json_encode($_SESSION["pending_order"]["order_details"]);
    $totalPrice = $_SESSION["pending_order"]["total_price"];
    $paymentMethod = $_POST["payment_method"];

    // ✅ Insert order into DB
    $stmt = $conn->prepare("INSERT INTO orders (order_details, total_price, order_status, payment_method, created_at) VALUES (?, ?, 'Confirmed', ?, NOW())");
    $stmt->bind_param("sds", $orderDetails, $totalPrice, $paymentMethod);

    if ($stmt->execute()) {
        unset($_SESSION["pending_order"]);  // ✅ Clear pending order after payment
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to process payment. Try again."]);
    }

    $stmt->close();
    exit();
}
?>
