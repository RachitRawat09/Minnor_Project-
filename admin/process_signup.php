<?php
session_start();
include '../includes/db_connect.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Hash password for security
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if email already exists
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $_SESSION['error'] = "Email already registered! Please login.";
        header("Location: signup.php"); // Redirect back to signup page
        exit();
    }
    $stmt->close();

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $hashed_password);

    if ($stmt->execute()) {
        echo "<html><head>";
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "</head><body>";
        echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Signup Successful!',
            text: 'Redirecting to Home...',
            showConfirmButton: false,
            timer: 1000
        }).then(() => {
            window.location.href='index.php';
        });
        </script>";
        echo "</body></html>";
        exit();

    } else {
        $_SESSION['error'] = "Unable to sign up. Please try again!";
        header("Location: signup.php"); // Redirect back to signup page
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
