<?php
include '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $restaurant_name = trim($_POST['restaurant_name']);
    $address = trim($_POST['address']);
    $restaurant_email = trim($_POST['restaurant_email']);
    $phone = trim($_POST['phone']);
    $admin_email = trim($_POST['admin_email']);
    $admin_password = trim($_POST['admin_password']);

    // Check for duplicate restaurant email
    $stmt = $conn->prepare("SELECT id FROM restaurants WHERE email = ?");
    $stmt->bind_param('s', $restaurant_email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = 'A restaurant with this email already exists.';
        header('Location: restaurant_register.php');
        exit();
    }
    $stmt->close();

    // Check for duplicate admin email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param('s', $admin_email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = 'An admin with this email already exists.';
        header('Location: restaurant_register.php');
        exit();
    }
    $stmt->close();

    // Insert into restaurants (approved = 0)
    $stmt = $conn->prepare("INSERT INTO restaurants (name, address, email, phone, approved) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param('ssss', $restaurant_name, $address, $restaurant_email, $phone);
    if (!$stmt->execute()) {
        $_SESSION['error'] = 'Failed to register restaurant. Please try again.';
        header('Location: restaurant_register.php');
        exit();
    }
    $restaurant_id = $stmt->insert_id;
    $stmt->close();

    // Insert admin user
    $hashed_password = password_hash($admin_password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (email, password, restaurant_id, role) VALUES (?, ?, ?, 'admin')");
    $stmt->bind_param('ssi', $admin_email, $hashed_password, $restaurant_id);
    if (!$stmt->execute()) {
        // Rollback restaurant if user creation fails
        $conn->query("DELETE FROM restaurants WHERE id = $restaurant_id");
        $_SESSION['error'] = 'Failed to create admin user. Please try again.';
        header('Location: restaurant_register.php');
        exit();
    }
    $stmt->close();

    $_SESSION['success'] = 'Registration successful! Awaiting super-admin approval.';
    header('Location: restaurant_register.php');
    exit();
} else {
    header('Location: restaurant_register.php');
    exit();
} 