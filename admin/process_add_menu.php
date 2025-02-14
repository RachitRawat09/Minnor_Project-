<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../includes/db_connect.php';

// ✅ Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $size_type = $_POST['size_type'];
    $availability = $_POST['availability'];

    // Initialize price variables with NULL
    $price_half = $price_full = $price_small = $price_medium = $price_large = $price_extra_large = NULL;

    // ✅ Assign prices based on size type
    if ($size_type == "half_full") {
        $price_half = $_POST['price_half'] ?? NULL;
        $price_full = $_POST['price_full'] ?? NULL;
    } elseif ($size_type == "sml_lrg") {
        $price_small = $_POST['price_small'] ?? NULL;
        $price_medium = $_POST['price_medium'] ?? NULL;
        $price_large = $_POST['price_large'] ?? NULL;
        $price_extra_large = $_POST['price_extra_large'] ?? NULL;
    } else {
        $price_full = $_POST['price_full'] ?? NULL;
    }

    // ✅ Handle Image Upload
    $image = "";
    if (!empty($_FILES['image']['name'])) {
        $target_dir = __DIR__ . "/../uploads/";
        $image = time() . "_" . basename($_FILES["image"]["name"]); // Unique filename
        $target_file = $target_dir . $image;

        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            die("❌ Image upload failed. Check file permissions.");
        }
    }

    // ✅ Check if item already exists in the same category
    $check_stmt = $conn->prepare("SELECT id FROM menu_items WHERE name = ? AND category = ?");
    $check_stmt->bind_param("ss", $name, $category);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Duplicate Item!',
            text: 'This menu item already exists in this category.',
        }).then(() => {
            window.history.back();
        });
        </script>";
        exit();
    }
    $check_stmt->close();

    // ✅ Insert into database
    $stmt = $conn->prepare("INSERT INTO menu_items 
        (name, category, image, size_type, price_half, price_full, price_small, price_medium, price_large, price_extra_large, availability) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssdddddds", $name, $category, $image, $size_type, $price_half, $price_full, $price_small, $price_medium, $price_large, $price_extra_large, $availability);

    if ($stmt->execute()) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Menu Item Added!',
                text: 'Redirecting to Manage Menu...',
                showConfirmButton: false,
                timer: 1000
            }).then(() => {
                window.location.href='manage_menu.php';
            });
        });
        </script>";
        exit();
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Failed to add menu item. Please try again.',
        }).then(() => {
            window.history.back();
        });
        </script>";
    }

    $stmt->close();
    $conn->close();
}
?>
