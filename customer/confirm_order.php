<?php
session_start();

// ✅ Ensure the cart exists and is not empty
if (!isset($_SESSION["cart"]) || empty($_SESSION["cart"])) {
    echo json_encode(["status" => "error", "message" => "Cart is empty."]);
    exit();
}

// ✅ Move cart data to `pending_order` session (instead of DB)
$_SESSION["pending_order"] = [
    "order_details" => $_SESSION["cart"],
    "total_price" => array_sum(array_column($_SESSION["cart"], "total")),
    "created_at" => date("Y-m-d H:i:s") // ✅ Store timestamp for order reference
];

// ✅ Clear cart session after moving data to `pending_order`
unset($_SESSION["cart"]);

// ✅ Redirect user to `bill.php`
echo json_encode(["status" => "success", "redirect" => "bill.php"]);
exit();
