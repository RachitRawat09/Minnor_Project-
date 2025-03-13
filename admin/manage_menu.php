<?php
// session_start();
include '../includes/db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu - CodeToCuisine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            margin: 0; 
        }</style>
</head>
<body>

<!-- Navbar -->
<?php include '../includes/header.php'; ?>

<div class="container mt-4">
    <h2 class="text-center mb-4">Manage Menu</h2>
    
    <!-- Button to Add New Item -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addMenuItemModal">Add New Item</button>

    <!-- Table to Display Menu Items -->
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Food Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Size Type</th>
                <th>Availability</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT * FROM menu_items";
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['id']}</td>
                    <td><img src='../uploads/{$row['image']}' width='50' height='50' alt='{$row['name']}'></td>
                    <td>{$row['name']}</td>
                    <td>{$row['category']}</td>
                    <td>";
                if ($row['size_type'] == 'half_full') {
                    echo "Half: ₹{$row['price_half']} / Full: ₹{$row['price_full']}";
                } elseif ($row['size_type'] == 'sml_lrg') {
                    echo "S: ₹{$row['price_small']}, M: ₹{$row['price_medium']}, L: ₹{$row['price_large']}, XL: ₹{$row['price_extra_large']}";
                } else {
                    echo "₹{$row['price_full']}";
                }
                echo "</td>
                    <td>{$row['size_type']}</td>
                    <td>{$row['availability']}</td>
                    <td>
    <a href='edit_menu.php?id={$row['id']}' class='btn btn-warning btn-sm'>Edit</a>
    <button class='btn btn-danger btn-sm delete-btn' data-id='{$row['id']}'>Delete</button>
</td>

                </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Add Menu Item Modal -->
<div class="modal fade" id="addMenuItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="process_add_menu.php" method="POST" enctype="multipart/form-data">
                    <label>Food Name:</label>
                    <input type="text" name="name" class="form-control" required>

                    <label>Category:</label>
<select class="form-control" name="category" required>
    <option disabled selected>-- Select Category --</option>
    <?php
    $categoryQuery = "SELECT * FROM categories";
    $categoryResult = $conn->query($categoryQuery);
    
    while ($categoryRow = $categoryResult->fetch_assoc()) {
        echo "<option value='{$categoryRow['category_name']}'>{$categoryRow['category_name']}</option>";
    }
    ?>
</select>



                    <label>Size Type:</label>
                    <select class="form-control" name="size_type" id="size_type">
                        <option value="none">No Size</option>
                        <option value="half_full">Half/Full</option>
                        <option value="sml_lrg">Small/Medium/Large/XL</option>
                    </select>

                    <div id="price_fields">
                        <label>Price:</label>
                        <input type="number" name="price_full" class="form-control">
                    </div>

                    <label>Availability:</label>
                    <select class="form-control" name="availability">
                        <option value="Available">Available</option>
                        <option value="Not Available">Not Available</option>
                    </select>

                    <!-- Image Upload -->
                    <label>Upload Image:</label>
                    <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event)" required>
                    <img id="imagePreview" src="" class="mt-2" style="max-width: 100px; display: none;">

                    <button type="submit" class="btn btn-success mt-3">Add Item</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Delete Confirmation (SweetAlert) -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".delete-btn").forEach(button => {
        button.addEventListener("click", function() {
            let itemId = this.getAttribute("data-id");
            Swal.fire({
                title: "Are you sure?",
                text: "This item will be permanently deleted!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "delete_menu.php?id=" + itemId;
                }
            });
        });
    });

    // Show correct price fields based on size type
    document.getElementById("size_type").addEventListener("change", function() {
        let priceFields = document.getElementById("price_fields");
        priceFields.innerHTML = "";

        if (this.value === "half_full") {
            priceFields.innerHTML = `
                <label>Half Price:</label>
                <input type="number" name="price_half" class="form-control">
                <label>Full Price:</label>
                <input type="number" name="price_full" class="form-control">
            `;
        } else if (this.value === "sml_lrg") {
            priceFields.innerHTML = `
                <label>Small Price:</label>
                <input type="number" name="price_small" class="form-control">
                <label>Medium Price:</label>
                <input type="number" name="price_medium" class="form-control">
                <label>Large Price:</label>
                <input type="number" name="price_large" class="form-control">
                <label>Extra Large Price:</label>
                <input type="number" name="price_extra_large" class="form-control">
            `;
        }
    });
});

// Image Preview Function
function previewImage(event) {
    let reader = new FileReader();
    reader.onload = function(){
        let output = document.getElementById('imagePreview');
        output.src = reader.result;
        output.style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>

</body>
</html>
