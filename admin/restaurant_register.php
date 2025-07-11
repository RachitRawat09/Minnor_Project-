<?php
include '../includes/header.php';

?>
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-lg p-4" style="max-width: 600px; width: 100%;">
        <div class="text-center mb-4">
            <img src="../assets/images/CODE TO CUISINE.png" alt="Logo" style="max-width: 180px;">
            <h2 class="mt-3">Register Your Restaurant</h2>
        </div>
        <form action="process_restaurant_register.php" method="POST">
            <div class="mb-3">
                <label for="restaurant_name" class="form-label">Restaurant Name</label>
                <input type="text" class="form-control" id="restaurant_name" name="restaurant_name" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address">
            </div>
            <div class="mb-3">
                <label for="restaurant_email" class="form-label">Restaurant Email</label>
                <input type="email" class="form-control" id="restaurant_email" name="restaurant_email" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone">
            </div>
            <hr>
            <h5 class="mb-3">Admin Account Details</h5>
            <div class="mb-3">
                <label for="admin_email" class="form-label">Admin Email</label>
                <input type="email" class="form-control" id="admin_email" name="admin_email" required>
            </div>
            <div class="mb-3">
                <label for="admin_password" class="form-label">Admin Password</label>
                <input type="password" class="form-control" id="admin_password" name="admin_password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register Restaurant</button>
        </form>
    </div>
</div>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
if (isset($_SESSION['success'])) {
    echo "<script>Swal.fire({icon: 'success', title: 'Success!', text: '" . $_SESSION['success'] . "', showConfirmButton: false, timer: 3000});</script>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo "<script>Swal.fire({icon: 'error', title: 'Error!', text: '" . $_SESSION['error'] . "'});</script>";
    unset($_SESSION['error']);
}
?>
<?php include '../includes/footer.php'; ?> 