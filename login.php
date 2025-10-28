<?php
// login.php
session_start();
// Redirect if already logged in
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Simple CRUD</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .login-container {
            max-width: 350px;
            margin-top: 80px;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container mx-auto">
            <h2 class="text-center mb-4 text-success">App Login</h2>
            
            <?php 
            if (isset($_GET['error']) && $_GET['error'] == 'invalid') {
                echo '<div class="alert alert-danger">Invalid email or password.</div>';
            } elseif (isset($_GET['success'])) {
                if ($_GET['success'] == 'registered') {
                    echo '<div class="alert alert-success">Registration successful! Please log in.</div>';
                } elseif ($_GET['success'] == 'logout') {
                    echo '<div class="alert alert-info">You have been logged out.</div>';
                }
            }
            ?>

            <form action="src/handlers/auth.php" method="POST">
                <input type="hidden" name="login" value="1">
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Log In</button>
            </form>

            <p class="mt-3 text-center">
                Need an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
