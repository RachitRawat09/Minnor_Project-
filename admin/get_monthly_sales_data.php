<?php
include '../includes/db_connect.php';

// Get sales for the last 30 days
// Only include orders with order_status = 'Completed'
date_default_timezone_set('Asia/Kolkata');
$labels = [];
$sales = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('M d', strtotime($date));
    $query = "SELECT SUM(total_price) as total FROM orders WHERE DATE(created_at) = '$date' AND order_status = 'Completed'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $sales[] = (float)($row['total'] ?? 0);
}

echo json_encode([
    'labels' => $labels,
    'sales' => $sales
]); 