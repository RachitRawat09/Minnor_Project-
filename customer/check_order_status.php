<?php
include '../includes/db_connect.php';
session_start();

// Check if order_id is provided
if (!isset($_POST['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

$order_id = $_POST['order_id'];

// Get the current order status
$query = "SELECT order_status FROM orders WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'order' => [
            'status' => $row['order_status']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
}

$stmt->close(); 