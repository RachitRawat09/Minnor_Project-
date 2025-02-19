<?php
session_start();
header("Content-Type: application/json");

// Get JSON data from AJAX request
$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $cartItem = [
        "id" => $data["itemId"],
        "name" => $data["itemName"],
        "image" => $data["itemImage"],
        "quantity" => (int)$data["quantity"],
        "size" => $data["selectedSize"],
        "price" => (float)$data["selectedPrice"],
        "total" => (int)$data["quantity"] * (float)$data["selectedPrice"]
    ];

    // Initialize cart session if not set
    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = [];
    }

    // Check if item is already in cart
    $exists = false;
    foreach ($_SESSION["cart"] as &$item) {
        if ($item["id"] == $cartItem["id"] && $item["size"] == $cartItem["size"]) {
            $item["quantity"] += $cartItem["quantity"];
            $item["total"] = $item["quantity"] * $item["price"];
            $exists = true;
            break;
        }
    }

    if (!$exists) {
        $_SESSION["cart"][] = $cartItem;
    }

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}
?>
