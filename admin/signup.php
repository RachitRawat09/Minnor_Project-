<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - CodeToCuisine</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ff00ff, #6100ff);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            max-width: 900px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .left {
            background: linear-gradient(135deg, #25007a, #8f00ff);
            color: white;
            padding: 0;
            text-align: center;
        }
        .left img{
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
       
        .right {
            padding: 40px;
        }
        .input-group input {
            padding: 10px;
            border-radius: 5px;
        }
        .btn-custom {
            background: linear-gradient(135deg, #ff00ff, #6100ff);
            color: white;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
        }
        .google-btn {
            background: #DB4437;
            color: white;
            text-decoration: none;
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            display: block;
            width: 100%;
        }
    </style>
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>

    <div class="container">
        <div class="row">
            <!-- Left Section -->
            <div class="col-md-5 d-flex align-items-center justify-content-center left">
                <img class="img-fluid" src="../assets/images/CODE TO CUISINE.png" alt="Sign up here">
            </div>
            
            <!-- Right Section -->
            <div class="col-md-7 right">
                <h2 class="text-center">Sign Up</h2>
                
                <!-- Signup Form -->
                <form action="process_signup.php" method="POST">
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn btn-custom mt-3">CONTINUE</button>
                </form>
                
                <div class="text-center mt-3">OR</div>

                <a href="google_login.php" class="google-btn mt-3">
                    <i class="fab fa-google me-2"></i> Sign up with Google
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Show SweetAlert Messages -->
    <?php
    session_start();
    if (isset($_SESSION['success'])) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '" . $_SESSION['success'] . "',
                showConfirmButton: false,
                timer: 3000
            });
        </script>";
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '" . $_SESSION['error'] . "'
            });
        </script>";
        unset($_SESSION['error']);
    }
    ?>

</body>
</html>
