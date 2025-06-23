<?php
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = $_POST['order_id'] ?? null;
    $payment_method = $_POST['payment_method'] ?? null;

    if (!empty($order_id) && !empty($payment_method)) {
        // ✅ Determine payment_type & status based on selection
        if ($payment_method === 'Online') {
            $payment_type = 'Online';
            $payment_status = 'Paid';  // Online payment is always paid
        } elseif ($payment_method === 'Cash') {
            $payment_type = 'Cash on Counter';
            $payment_status = 'Pending';  // Cash is collected later, so pending
        } else {
            // Fallback for unknown method
            $payment_type = $payment_method;
            $payment_status = 'Pending';
        }

        // ✅ Update payment_type & payment_status in database
        $updateQuery = "UPDATE orders SET payment_type = ?, payment_status = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);

        if ($stmt === false) {
            echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
            exit();
        }

        $stmt->bind_param("ssi", $payment_type, $payment_status, $order_id);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true, 
                "payment_type" => $payment_type, 
                "payment_status" => $payment_status
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Database update failed"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Invalid request"]);
    }
}
?>
