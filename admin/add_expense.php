<?php
session_start();
include '../includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['restaurant_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

$restaurant_id = $_SESSION['restaurant_id'];

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

// Validate required fields
$required_fields = ['category', 'amount', 'description', 'date'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

// Sanitize and prepare data
$category_id = (int)$data['category'];
$amount = (float)$data['amount'];
$description = $conn->real_escape_string($data['description']);
$date = $conn->real_escape_string($data['date']);

// Insert expense
$query = "INSERT INTO expenses (category_id, amount, description, date, restaurant_id) VALUES ($category_id, $amount, '$description', '$date', $restaurant_id)";

if ($conn->query($query)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Expense added successfully',
        'expense_id' => $conn->insert_id
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error adding expense: ' . $conn->error
    ]);
}

$conn->close();
?> 