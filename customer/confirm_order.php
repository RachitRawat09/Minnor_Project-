<?php
session_start();

// Require restaurant_id in the request (from session or URL)
$restaurant_id = null;
if (isset($_GET['restaurant_id'])) {
    $restaurant_id = intval($_GET['restaurant_id']);
} elseif (isset($_POST['restaurant_id'])) {
    $restaurant_id = intval($_POST['restaurant_id']);
}
if (!$restaurant_id) {
    echo json_encode(["status" => "error", "message" => "Missing restaurant ID."]);
    exit();
}

// Make sure the cart exists and isn't empty before proceeding
if (!isset($_SESSION["cart"]) || empty($_SESSION["cart"])) {
    echo json_encode(["status" => "error", "message" => "Cart is empty."]);
    exit();
}

// Move the cart data into a session variable for pending orders
$_SESSION["pending_order"] = [
    "order_details" => $_SESSION["cart"],
    "total_price" => array_sum(array_column($_SESSION["cart"], "total")),
    "created_at" => date("Y-m-d H:i:s"), // Save the time for order reference
    "restaurant_id" => $restaurant_id
];

// Clear the cart session now that we've moved the data
unset($_SESSION["cart"]);

// Send the user to the bill page
echo json_encode(["status" => "success", "redirect" => "bill.php"]);
exit();
