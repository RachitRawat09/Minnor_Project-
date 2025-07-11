<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['restaurant_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$restaurant_id = $_SESSION['restaurant_id'];
include '../includes/db_connect.php';

// Get recent orders
$recent_orders_query = "SELECT * FROM orders WHERE DATE(created_at) = CURDATE() AND restaurant_id = $restaurant_id ORDER BY created_at DESC LIMIT 5";
$recent_orders_result = $conn->query($recent_orders_query);

$orders = [];
if ($recent_orders_result->num_rows > 0) {
    while ($order = $recent_orders_result->fetch_assoc()) {
        $orders[] = $order;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($orders); 