<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['restaurant_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$restaurant_id = $_SESSION['restaurant_id'];
include '../includes/db_connect.php';

// ✅ Step 1: Ensure `id` is Passed in URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Invalid request!',
            text: 'No item selected.',
        }).then(() => {
            window.location.href='manage_menu.php';
        });
    </script>";
    exit();
}
$id = $_GET['id'];

// ✅ Step 2: Get Image Filename from Database
$stmt = $conn->prepare("SELECT image FROM menu_items WHERE id = ? AND restaurant_id = ?");
$stmt->bind_param("ii", $id, $restaurant_id);
$stmt->execute();
$stmt->bind_result($image);
$stmt->fetch();
$stmt->close();

// ✅ Step 3: Delete Image File if Exists
if (!empty($image)) {
    $image_path = "../uploads/" . $image;
    if (file_exists($image_path)) {
        unlink($image_path); // Deletes the image file
    }
}

// ✅ Step 4: Delete the Menu Item from Database
$stmt = $conn->prepare("DELETE FROM menu_items WHERE id = ? AND restaurant_id = ?");
$stmt->bind_param("ii", $id, $restaurant_id);

if ($stmt->execute()) {
    // ✅ Step 5: Reset Auto-Increment
    $conn->query("SET @num := 0;");
    $conn->query("UPDATE menu_items SET id = @num := @num + 1 WHERE restaurant_id = $restaurant_id;");
    $conn->query("ALTER TABLE menu_items AUTO_INCREMENT = 1;");
    
    // ✅ Step 6: Show SweetAlert Properly
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Deleting Item...</title>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Menu Item Deleted!',
                text: 'Image removed & IDs reset successfully!',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href='manage_menu.php';
            });
        </script>
    </body>
    </html>";
} else {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to delete the menu item. Try again.',
            }).then(() => {
                window.location.href='manage_menu.php';
            });
        </script>
    </body>
    </html>";
}

$stmt->close();
$conn->close();
?>
