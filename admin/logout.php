<?php
session_start();
session_destroy();

// Show SweetAlert message before redirecting to home page
echo "<html><head>";
echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
echo "</head><body>";
echo "<script>
Swal.fire({
    icon: 'success',
    title: 'Logout Successful!',
    text: 'Redirecting to Home...',
    showConfirmButton: false,
    timer: 1000
}).then(() => {
    window.location.href='index.php';
});
</script>";
echo "</body></html>";
exit();
?>
