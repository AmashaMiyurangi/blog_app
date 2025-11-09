<?php
/**
 * Login Page - WriteCore Blog
 * Handles user authentication
 */

// Start session
session_start();

// Include database connection (uses MySQLi $conn)
require 'db_connect.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit;
}

// Initialize errors array
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    if (!$username || !$password) {
        $errors[] = "Please fill in both fields.";
    } else {
        // Prepare SQL statement to find user by username OR email
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // Check if user exists and password matches
        if ($user && password_verify($password, $user['password'])) {
            // Authentication successful
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Set remember me cookie (7 days)
            setcookie("user", $user['username'], time() + (86400 * 7), "/");
            
            // Close statement
            $stmt->close();
            
            // Redirect to home page
            header("Location: home.php");
            exit;
        } else {
            // Authentication failed
            $errors[] = "Invalid username or password.";
        }
        
        // Close statement
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | WriteCore</title>
    <link rel="stylesheet" href="auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>Welcome Back</h2>
            
            <!-- Display error messages if any -->
            <?php if ($errors): ?>
                <div class="alert error">
                    <?php foreach ($errors as $e): ?>
                        <p><?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" action="">
                <input type="text" name="username" placeholder="Username or Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            
            <!-- Registration link -->
            <p>Don't have an account? <a href="register.php">Register now</a></p>
        </div>
    </div>
</body>
</html>