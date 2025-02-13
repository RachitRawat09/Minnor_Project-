<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_email'] = $email;

            // Show success message & redirect after delay
            echo "<html><head>";
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "</head><body>";
            echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Login Successful!',
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
            $_SESSION['error'] = "Incorrect password!";
            header("Location: login.php");
            exit(); // ✅ Fix: Stop execution after redirect
        }
    } else {
        $_SESSION['error'] = "No account found! Please sign up.";
        header("Location: signup.php"); 
        exit(); // ✅ Fix: Stop execution after redirect
    }
}
?>
