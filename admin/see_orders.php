<?php
session_start();
include '../includes/db_connect.php';

// Fetch all orders from the database
$query = "SELECT * FROM orders ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>📦 Customer Orders</h2>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Order ID</th>
                <th>Order Details</th>
                <th>Total Price</th>
                <th>Status</th>
                <th>Placed On</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $order['id']; ?></td>
                    <td>
                        <ul>
                            <?php 
                            $items = json_decode($order['order_details'], true);
                            foreach ($items as $item) { ?>
                                <li>
                                    <strong><?= htmlspecialchars($item['name']); ?></strong> 
                                    (<?= htmlspecialchars($item['size']); ?>) - 
                                    <b>₹<?= htmlspecialchars($item['price']); ?></b> x 
                                    <?= htmlspecialchars($item['quantity']); ?> = 
                                    <b>₹<?= htmlspecialchars($item['total']); ?></b>
                                </li>
                            <?php } ?>
                        </ul>
                    </td>
                    <td>₹<?= number_format($order['total_price'], 2); ?></td>
                    <td><?= $order['order_status']; ?></td>
                    <td><?= $order['created_at']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>
