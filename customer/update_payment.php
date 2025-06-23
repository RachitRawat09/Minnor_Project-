<?php
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = $_POST['order_id'] ?? null;
    $payment_method = $_POST['payment_method'] ?? null;

    if (!empty($order_id) && !empty($payment_method)) {
        // Figure out payment type and status based on what the user picked
        if ($payment_method === 'Online') {
            $payment_type = 'Online';
            $payment_status = 'Paid';  // Online payment is always paid
        } elseif ($payment_method === 'Cash') {
            $payment_type = 'Cash on Counter';
            $payment_status = 'Pending';  // If it's cash, payment comes later
        } else {
            // If we get here, something went wrong with the payment method
            $payment_type = $payment_method;
            $payment_status = 'Pending';
        }

        // Update the payment type and status in the database
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
