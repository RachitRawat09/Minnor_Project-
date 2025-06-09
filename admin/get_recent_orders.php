<?php
session_start();
include '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

// Get recent orders
$recent_orders_query = "SELECT * FROM orders WHERE DATE(created_at) = CURDATE() ORDER BY created_at DESC LIMIT 5";
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