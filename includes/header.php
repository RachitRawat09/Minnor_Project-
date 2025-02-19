<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | CodeToCuisine</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Custom Styles -->
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
             <!-- Logo Image -->
             <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="../assets/images/CODE TO CUISINE.png" alt="Logo" class="logo me-2"> 
                <span>CodeToCuisine</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_menu.php">Manage Menu</a></li>
                    <li class="nav-item"><a class="nav-link" href="see_orders.php">Orders</a></li>
                    <li class="nav-item"><a class="nav-link" href="orders.php">Orders List</a></li>
                    <li class="nav-item"><a class="nav-link" href="orders.php">Sales tracker</a></li>
                    <?php if (isset($_SESSION['user_id'])) { ?>
<li class="nav-item">
    <a class="nav-link text-white">Welcome, <?php echo $_SESSION['user_email']; ?> 👋</a>
</li>
<li class="nav-item">
    <a class="btn btn-danger ms-2" href="logout.php">Logout</a>
</li>
<?php } else { ?>

                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="login.php">Login</a>
                        <a class="btn btn-secondary ms-2" href="signup.php">Sign Up</a>
                    </li>
                <?php } ?>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Main Content -->
    <div class="container mt-4">
