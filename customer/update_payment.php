<?php
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = $_POST['order_id'];
    $payment_method = $_POST['payment_method'];

    if (!empty($order_id) && !empty($payment_method)) {
        $updateQuery = "UPDATE orders SET payment_type=? WHERE id=?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("si", $payment_method, $order_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "payment_method" => $payment_method]);
        } else {
            echo json_encode(["success" => false, "message" => "Database update failed"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid request"]);
    }
}
?>
