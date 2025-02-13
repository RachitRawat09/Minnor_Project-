<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CodeToCuisine</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #8e44ad, #3498db);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            max-width: 900px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .left img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .right {
            padding: 40px;
        }
        .form-control {
            border-radius: 5px;
            padding: 10px;
        }
        .btn-custom {
            background: #6a11cb;
            color: white;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
        }
        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            margin-top: 10px;
        }
        .google-btn {
            background: #DB4437;
        }
        
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <!-- Left Section -->
            <div class="col-md-6 left">
                <img src="../assets/images/CODE TO CUISINE.png" alt="Login Image">
            </div>
            
            <!-- Right Section -->
            <div class="col-md-6 right">
                <h3 class="text-center">Login</h3>
                <p class="text-center">Don't have an account yet? <a href="signup.php">Sign Up</a></p>

                <form action="process_login.php" method="POST">
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="mb-3 d-flex justify-content-between">
                        <div>
                        <input type="checkbox" id="remember" name="remember">

                            <label for="remember">Remember me</label>
                        </div>
                        <a href="#">Forgot Password?</a>
                    </div>
                    <button type="submit" class="btn btn-custom mt-3">LOGIN</button>
                </form>
                <div class="text-center mt-3">or login with</div>
                <a href="google_login.php" class="google-btn">
    <i class="fab fa-google me-2"></i> Login with Google
</a>


                
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 -->
    <?php
session_start();
if (isset($_SESSION['error'])) {
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    Swal.fire({
        icon: 'error',
        title: 'Oops!',
        text: '" . $_SESSION['error'] . "'
    });
    </script>";
    unset($_SESSION['error']);
}
?>

</body>
</html>
