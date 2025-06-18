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
        
        // First check if the order exists and get its current status
        $checkQuery = "SELECT payment_type, payment_status FROM orders WHERE id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $orderId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Order not found");
        }
        
        $order = $result->fetch_assoc();
        
        // Check if payment is already marked as paid
        if (strtolower($order['payment_status']) === 'paid') {
            echo json_encode([
                'success' => true,
                'message' => 'Payment is already marked as paid'
            ]);
            exit();
        }
        
        // Update the payment status to Paid
        $updateQuery = "UPDATE orders SET payment_status = 'Paid', updated_at = NOW() WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("i", $orderId);
        
        if ($updateStmt->execute()) {
            // Get the updated order details
            $getUpdatedQuery = "SELECT payment_status, payment_type FROM orders WHERE id = ?";
            $getUpdatedStmt = $conn->prepare($getUpdatedQuery);
            $getUpdatedStmt->bind_param("i", $orderId);
            $getUpdatedStmt->execute();
            $updatedResult = $getUpdatedStmt->get_result();
            $updatedOrder = $updatedResult->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'message' => 'Payment status updated successfully',
                'order' => [
                    'status' => $updatedOrder['payment_status'],
                    'type' => $updatedOrder['payment_type']
                ]
            ]);
        } else {
            throw new Exception("Failed to update payment status: " . $conn->error);
        }
        
        $updateStmt->close();
        $getUpdatedStmt->close();
    } else {
        throw new Exception("Invalid request method or missing order ID");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 