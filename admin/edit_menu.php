<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../includes/db_connect.php';

// ✅ Ensure `id` is Passed in URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<script>
        alert('Invalid request! No item selected.');
        window.location.href='manage_menu.php';
    </script>");
}
$id = $_GET['id'];

// ✅ Fetch Item Details from Database
$stmt = $conn->prepare("SELECT * FROM menu_items WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

if (!$item) {
    die("<script>
        alert('Item not found! It may have been deleted.');
        window.location.href='manage_menu.php';
    </script>");
}

// ✅ Handle Form Submission for Updating Item
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $size_type = $_POST['size_type'];
    $availability = $_POST['availability'];

    // ✅ Set prices based on size type
    $price_half = $_POST['price_half'] ?? NULL;
    $price_full = $_POST['price_full'] ?? NULL;
    $price_small = $_POST['price_small'] ?? NULL;
    $price_medium = $_POST['price_medium'] ?? NULL;
    $price_large = $_POST['price_large'] ?? NULL;
    $price_extra_large = $_POST['price_extra_large'] ?? NULL;

    // ✅ Handle Image Upload (Keep Old Image If No New Upload)
    $image = $item['image']; // Default to old image
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        $image = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    // ✅ Update the Database with New Data
    $stmt = $conn->prepare("UPDATE menu_items SET name=?, category=?, image=?, size_type=?, price_half=?, price_full=?, price_small=?, price_medium=?, price_large=?, price_extra_large=?, availability=? WHERE id=?");
    $stmt->bind_param("ssssddddddsi", $name, $category, $image, $size_type, $price_half, $price_full, $price_small, $price_medium, $price_large, $price_extra_large, $availability, $id);

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
        echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Failed to update menu item. Try again.',
        });
        </script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Edit Menu Item</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <label>Food Name:</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($item['name'] ?? ''); ?>" required>

        <label>Category:</label>
        <select class="form-control" name="category" required>
            <option disabled>-- Select Category --</option>
            <?php
            $categories = ["Burgers", "Chinese Cuisine", "Combo Meals", "Indian Main Course", "Pasta Delight", "Pizzas",
                "Raita & Yogurt", "Rice & Biryani", "Roti & Breads", "Salads", "Sandwiches", "Single Topping Pizza",
                "Double Topping Pizza", "Tandoori Snacks", "Thali", "Wraps & Rolls",
                "Burgers (Non-Veg)", "Chinese Cuisine (Non-Veg)", "Combo Meals (Non-Veg)", "Indian Main Course (Non-Veg)",
                "Pasta Delight (Non-Veg)", "Pizzas (Non-Veg)", "Raita & Yogurt (Non-Veg)", "Rice & Biryani (Non-Veg)",
                "Roti & Breads (Non-Veg)", "Salads (Non-Veg)", "Sandwiches (Non-Veg)", "Single Topping Pizza (Non-Veg)",
                "Double Topping Pizza (Non-Veg)", "Tandoori Snacks (Non-Veg)", "Thali (Non-Veg)", "Wraps & Rolls (Non-Veg)"];
            foreach ($categories as $cat) {
                $selected = ($cat == $item['category']) ? "selected" : "";
                echo "<option value='$cat' $selected>$cat</option>";
            }
            ?>
        </select>

        <label>Size Type:</label>
        <select class="form-control" name="size_type" id="size_type" onchange="updatePriceFields(this.value)">
            <option value="none" <?= $item['size_type'] == 'none' ? 'selected' : ''; ?>>No Size</option>
            <option value="half_full" <?= $item['size_type'] == 'half_full' ? 'selected' : ''; ?>>Half/Full</option>
            <option value="sml_lrg" <?= $item['size_type'] == 'sml_lrg' ? 'selected' : ''; ?>>Small/Medium/Large/XL</option>
        </select>

        <!-- Dynamic Price Fields -->
        <div id="price_fields" class="mt-3"></div>

        <label>Upload New Image (Optional):</label>
        <input type="file" name="image" class="form-control">
        <img src="../uploads/<?= htmlspecialchars($item['image'] ?? ''); ?>" width="100" class="mt-2">

        <label>Availability:</label>
        <select class="form-control" name="availability">
            <option value="Available" <?= $item['availability'] == 'Available' ? 'selected' : ''; ?>>Available</option>
            <option value="Not Available" <?= $item['availability'] == 'Not Available' ? 'selected' : ''; ?>>Not Available</option>
        </select>

        <button type="submit" class="btn btn-success mt-3">Update Item</button>
    </form>
</div>

<!-- JavaScript to Dynamically Show Correct Price Fields -->
<script>
function updatePriceFields(sizeType) {
    let priceFields = document.getElementById("price_fields");
    priceFields.innerHTML = "";

    if (sizeType === "half_full") {
        priceFields.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Half Price:</label>
                <input type="number" name="price_half" class="form-control" value="<?= htmlspecialchars($item['price_half'] ?? '') ?>" step="0.01" min="0">
            </div>
            <div class="mb-3">
                <label class="form-label">Full Price:</label>
                <input type="number" name="price_full" class="form-control" value="<?= htmlspecialchars($item['price_full'] ?? '') ?>" step="0.01" min="0">
            </div>`;
    } else if (sizeType === "sml_lrg") {
        priceFields.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Small Price:</label>
                <input type="number" name="price_small" class="form-control" value="<?= htmlspecialchars($item['price_small'] ?? '') ?>" step="0.01" min="0">
            </div>
            <div class="mb-3">
                <label class="form-label">Medium Price:</label>
                <input type="number" name="price_medium" class="form-control" value="<?= htmlspecialchars($item['price_medium'] ?? '') ?>" step="0.01" min="0">
            </div>
            <div class="mb-3">
                <label class="form-label">Large Price:</label>
                <input type="number" name="price_large" class="form-control" value="<?= htmlspecialchars($item['price_large'] ?? '') ?>" step="0.01" min="0">
            </div>
            <div class="mb-3">
                <label class="form-label">Extra Large Price:</label>
                <input type="number" name="price_extra_large" class="form-control" value="<?= htmlspecialchars($item['price_extra_large'] ?? '') ?>" step="0.01" min="0">
            </div>`;
    } else {
        priceFields.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Price:</label>
                <input type="number" name="price_full" class="form-control" value="<?= htmlspecialchars($item['price_full'] ?? '') ?>" step="0.01" min="0">
            </div>`;
    }
}

// Call updatePriceFields when the page loads
document.addEventListener("DOMContentLoaded", function() {
    const sizeTypeSelect = document.getElementById("size_type");
    updatePriceFields(sizeTypeSelect.value);
});
</script>
</body>
</html>
