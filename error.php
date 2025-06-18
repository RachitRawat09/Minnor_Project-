<?php
$error_code = $_SERVER['REDIRECT_STATUS'] ?? 403;
$error_message = 'Access Forbidden';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?php echo $error_code; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code"><?php echo $error_code; ?></div>
        <h1 class="mb-4"><?php echo $error_message; ?></h1>
        <p class="text-muted mb-4">Sorry, you don't have permission to access this page.</p>
        <a href="/" class="btn btn-primary">Go to Homepage</a>
    </div>
</body>
</html> 