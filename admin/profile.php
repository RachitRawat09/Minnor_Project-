<?php
session_start();
include '../includes/db_connect.php';

// Only allow logged-in admins
if (!isset($_SESSION['user_id']) || !isset($_SESSION['restaurant_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$restaurant_id = $_SESSION['restaurant_id'];
$user_id = $_SESSION['user_id'];

// Fetch restaurant details
$restaurant = $conn->query("SELECT * FROM restaurants WHERE id = $restaurant_id")->fetch_assoc();
// Fetch admin details
$admin = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Handle messages
$profile_msg = '';
$password_msg = '';
$logo_msg = '';

// Handle admin profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_admin'])) {
    $full_name = trim($_POST['full_name']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    if (empty($full_name) || empty($mobile) || empty($email)) {
        $profile_msg = '<div class="alert alert-danger">All fields are required.</div>';
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name=?, mobile=?, email=? WHERE id=?");
        $stmt->bind_param('sssi', $full_name, $mobile, $email, $user_id);
        if ($stmt->execute()) {
            $profile_msg = '<div class="alert alert-success">Profile updated successfully!</div>';
            // Refresh admin data
            $admin = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
        } else {
            $profile_msg = '<div class="alert alert-danger">Failed to update profile.</div>';
        }
        $stmt->close();
    }
}

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    if (empty($current) || empty($new) || empty($confirm)) {
        $password_msg = '<div class="alert alert-danger">All password fields are required.</div>';
    } elseif ($new !== $confirm) {
        $password_msg = '<div class="alert alert-danger">New passwords do not match.</div>';
    } else {
        $user = $conn->query("SELECT password FROM users WHERE id = $user_id")->fetch_assoc();
        if (!password_verify($current, $user['password'])) {
            $password_msg = '<div class="alert alert-danger">Current password is incorrect.</div>';
        } else {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $conn->query("UPDATE users SET password = '$hashed' WHERE id = $user_id");
            $password_msg = '<div class="alert alert-success">Password updated successfully!</div>';
        }
    }
}

// Handle logo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_logo'])) {
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['logo']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['logo']['name']);
        $targetPath = '../uploads/' . $fileName;
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($fileType, $allowed)) {
            $logo_msg = '<div class="alert alert-danger">Only JPG, PNG, and WEBP files are allowed.</div>';
        } elseif (move_uploaded_file($fileTmp, $targetPath)) {
            $conn->query("UPDATE restaurants SET logo='$fileName' WHERE id=$restaurant_id");
            $logo_msg = '<div class="alert alert-success">Logo updated successfully!</div>';
            // Refresh restaurant data
            $restaurant = $conn->query("SELECT * FROM restaurants WHERE id = $restaurant_id")->fetch_assoc();
        } else {
            $logo_msg = '<div class="alert alert-danger">Failed to upload logo.</div>';
        }
    } else {
        $logo_msg = '<div class="alert alert-danger">Please select a logo file to upload.</div>';
    }
}

// Placeholder logo if none
$logo = !empty($restaurant['logo']) ? '../uploads/' . htmlspecialchars($restaurant['logo']) : 'https://via.placeholder.com/120?text=Logo';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - CodeToCuisine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fc; font-family: 'Poppins', sans-serif; }
        .profile-container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 20px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.15); overflow: hidden; }
        .profile-left { background: #f1f3f6; padding: 2rem 1.5rem; text-align: center; }
        .profile-logo-wrapper { position: relative; display: inline-block; }
        .profile-logo { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 4px solid #36b9cc; margin-bottom: 1rem; transition: filter 0.2s; cursor: pointer; }
        .profile-logo-wrapper:hover .profile-logo { filter: brightness(0.7); }
        .camera-overlay {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.5);
            color: #fff;
            border-radius: 50%;
            width: 48px; height: 48px;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity 0.2s;
            font-size: 1.5rem;
        }
        .profile-logo-wrapper:hover .camera-overlay { opacity: 1; pointer-events: auto; }
        .profile-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; }
        .profile-info { color: #555; margin-bottom: 0.5rem; }
        .profile-label { font-weight: 600; color: #888; }
        .profile-right { padding: 2rem 2.5rem; }
        .admin-title { font-size: 1.3rem; font-weight: 600; margin-bottom: 1.2rem; }
        .form-label { font-weight: 500; }
        .divider { border-left: 2px solid #e3e6f0; height: 100%; }
        #logoInput { display: none; }
        @media (max-width: 767px) {
            .profile-container { flex-direction: column; }
            .divider { border-left: none; border-top: 2px solid #e3e6f0; width: 100%; height: 2px; margin: 2rem 0; }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary">
            <i class="fas fa-utensils"></i> CodeToCuisine Admin
        </a>
        <div class="d-flex align-items-center">
            
            <a href="restaurant_dashboard.php" class="btn btn-outline-primary rounded-pill">
                <i class="fas fa-arrow-left me-2"></i>Dashboard
            </a>
        </div>
    </div>
</nav>
    <div class="container profile-container d-flex flex-md-row flex-column">
        <!-- Left: Restaurant Info -->
        <div class="profile-left flex-fill d-flex flex-column align-items-center justify-content-center">
            <?= $logo_msg ?>
            <form method="post" enctype="multipart/form-data" class="mb-3">
                <div class="profile-logo-wrapper mb-3" id="logoWrapper">
                    <img src="<?= $logo ?>" alt="Restaurant Logo" class="profile-logo mb-0" id="logoPreview">
                    <span class="camera-overlay"><i class="fas fa-camera"></i></span>
                    <input type="file" name="logo" accept="image/*" id="logoInput">
                </div>
                <button type="submit" name="update_logo" class="btn btn-outline-primary btn-sm">Change Logo</button>
            </form>
            <div class="profile-title mb-2">
                <i class="fas fa-store me-2 text-primary"></i><?= htmlspecialchars($restaurant['name'] ?? 'N/A') ?>
            </div>
            <div class="profile-info mb-2">
                <span class="profile-label"><i class="fas fa-map-marker-alt me-1"></i>Address:</span><br>
                <?= htmlspecialchars($restaurant['address'] ?? 'N/A') ?>
            </div>
            <div class="profile-info mb-2">
                <span class="profile-label"><i class="fas fa-envelope me-1"></i>Email:</span><br>
                <?= htmlspecialchars($restaurant['email'] ?? 'N/A') ?>
            </div>
            <div class="profile-info mb-2">
                <span class="profile-label"><i class="fas fa-phone me-1"></i>Phone:</span><br>
                <?= htmlspecialchars($restaurant['phone'] ?? 'N/A') ?>
            </div>
        </div>
        <div class="divider d-none d-md-block"></div>
        <!-- Right: Admin Info -->
        <div class="profile-right flex-fill">
            <div class="admin-title mb-4"><i class="fas fa-user-circle me-2 text-primary"></i>Admin Details</div>
            <?= $profile_msg ?>
            <form method="post" class="mb-4">
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-user me-1"></i>Full Name:</label>
                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($admin['full_name'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-phone me-1"></i>Mobile:</label>
                    <input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars($admin['mobile'] ?? '') ?>" >
                </div>
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-envelope me-1"></i>Email:</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required>
                </div>
                <button type="submit" name="update_admin" class="btn btn-primary">Save Changes</button>
            </form>
            <hr>
            <div class="mb-3">
                <div class="admin-title mb-2"><i class="fas fa-key me-2 text-warning"></i>Update Password</div>
                <?= $password_msg ?>
                <form method="post" class="row g-3">
                    <div class="col-12 col-md-4">
                        <input type="password" name="current_password" class="form-control" placeholder="Current Password" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm New Password" required>
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" name="update_password" class="btn btn-outline-primary w-100 w-md-auto">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    // Modern logo upload: click image or camera icon to open file picker, show preview
    const logoWrapper = document.getElementById('logoWrapper');
    const logoInput = document.getElementById('logoInput');
    const logoPreview = document.getElementById('logoPreview');
    logoWrapper.addEventListener('click', function() {
        logoInput.click();
    });
    logoInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                logoPreview.src = ev.target.result;
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });
    </script>
</body>
</html> 