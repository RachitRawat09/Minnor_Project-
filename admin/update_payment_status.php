<?php
session_start();
include '../includes/db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
        $orderId = intval($_POST['order_id']);
        
        // First check if the order exists and is a cash payment
        $checkQuery = "SELECT payment_type FROM orders WHERE id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $orderId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Order not found");
        }
        
        $order = $result->fetch_assoc();
        if (strtolower($order['payment_type']) !== 'cash on counter') {
            throw new Exception("Invalid payment type");
        }
        
        // Update the payment status to Paid
        $updateQuery = "UPDATE orders SET payment_status = 'Paid' WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("i", $orderId);
        
        if ($updateStmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Payment status updated successfully'
            ]);
        } else {
            throw new Exception("Failed to update payment status: " . $conn->error);
        }
        
        $updateStmt->close();
    } else {
        throw new Exception("Invalid request method or missing order ID");
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 