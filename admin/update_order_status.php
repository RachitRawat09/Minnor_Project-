<?php
session_start();
include '../includes/db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is admin (using user_id instead of admin_id)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please login first.']);
    exit;
}

// Get POST data
$order_id = $_POST['order_id'] ?? null;
$status = $_POST['status'] ?? null;

// Log received data
error_log("Received order_id: " . $order_id);
error_log("Received status: " . $status);

// Validate input
if (!$order_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Validate status
$valid_statuses = ['Pending', 'Processing', 'Preparing', 'Ready', 'Completed', 'Cancelled'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    // First check if order exists
    $check_stmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
    $check_stmt->bind_param("i", $order_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET order_status = ?, updated_at = NOW() WHERE id = ?");
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database prepare failed']);
        exit;
    }
    
    $stmt->bind_param("si", $status, $order_id);
    $result = $stmt->execute();
    
    if ($result) {
        // Get updated order details
        $select_stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
        $select_stmt->bind_param("i", $order_id);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        $order = $result->fetch_assoc();
        
        // Prepare response with updated order details
        $response = [
            'success' => true,
            'message' => 'Order status updated successfully',
            'order' => [
                'id' => $order['id'],
                'status' => $order['order_status'],
                'updated_at' => $order['updated_at']
            ]
        ];
        
        echo json_encode($response);
    } else {
        error_log("Update failed: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Failed to update order status: ' . $stmt->error]);
    }
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 