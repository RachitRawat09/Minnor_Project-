<?php
session_start();
if (!isset($_GET['restaurant_id']) || !is_numeric($_GET['restaurant_id'])) {
    die('<div style="color:red;text-align:center;margin-top:2rem;">Invalid or missing restaurant ID.</div>');
}
$restaurant_id = intval($_GET['restaurant_id']);
if (isset($_GET["index"]) && isset($_SESSION["cart"][$_GET["index"]])) {
    unset($_SESSION["cart"][$_GET["index"]]);
    $_SESSION["cart"] = array_values($_SESSION["cart"]); // Re-index array
}
header("Location: cart.php?restaurant_id=$restaurant_id");
exit();
?>
