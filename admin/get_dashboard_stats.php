<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['restaurant_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$restaurant_id = $_SESSION['restaurant_id'];
include '../includes/db_connect.php';

// Get today's orders count
$today_orders_query = "SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE() AND restaurant_id = $restaurant_id";
$today_orders_result = $conn->query($today_orders_query);
$today_orders = $today_orders_result->fetch_assoc()['count'];

// Get pending orders count
$pending_orders_query = "SELECT COUNT(*) as count FROM orders WHERE payment_status = 'Pending' AND restaurant_id = $restaurant_id";
$pending_orders_result = $conn->query($pending_orders_query);
$pending_orders = $pending_orders_result->fetch_assoc()['count'];

// Get total menu items
$menu_items_query = "SELECT COUNT(*) as count FROM menu_items WHERE restaurant_id = $restaurant_id";
$menu_items_result = $conn->query($menu_items_query);
$menu_items = $menu_items_result->fetch_assoc()['count'];

// Get orders from the last 1 month
$month_orders_query = "SELECT COUNT(*) as count FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND restaurant_id = $restaurant_id";
$month_orders_result = $conn->query($month_orders_query);
$month_orders = $month_orders_result->fetch_assoc()['count'];

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'today_orders' => $today_orders,
    'pending_orders' => $pending_orders,
    'menu_items' => $menu_items,
    'month_orders' => $month_orders
]); 