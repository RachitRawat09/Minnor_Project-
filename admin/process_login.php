<?php
session_start();
include '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, password, role, restaurant_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $hashed_password, $role, $restaurant_id);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // If admin, check restaurant approval
            if ($role === 'admin') {
                $stmt2 = $conn->prepare("SELECT approved FROM restaurants WHERE id = ?");
                $stmt2->bind_param("i", $restaurant_id);
                $stmt2->execute();
                $stmt2->bind_result($approved);
                $stmt2->fetch();
                $stmt2->close();
                if ($approved != 1) {
                    $_SESSION['error'] = "Your restaurant registration is pending approval.";
                    header("Location: login.php");
                    exit();
                }
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_email'] = $email;
                $_SESSION['restaurant_id'] = $restaurant_id;
                $_SESSION['role'] = $role;
                // Redirect to restaurant dashboard
                echo "<html><head>";
                echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
                echo "</head><body>";
                echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Login Successful!',
                    text: 'Redirecting to Your Dashboard...',
                    showConfirmButton: false,
                    timer: 1000
                }).then(() => {
                    window.location.href='restaurant_dashboard.php';
                });
                </script>";
                echo "</body></html>";
                exit();
            } else {
                // Superadmin or other roles
                $_SESSION['role'] = $role;
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
            }
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
