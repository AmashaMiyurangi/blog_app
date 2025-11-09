<?php
session_start();
require 'db_connect.php';

// Redirect to home if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    // Validation
    if (strlen($username) < 3) $errors[] = "Username must be at least 3 characters long.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters long.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        // Check duplicates
        $check = $pdo->prepare("SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1");
        $check->execute([':u' => $username, ':e' => $email]);
        if ($check->fetch()) {
            $errors[] = "Username or email is already taken.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:u, :e, :p)");
            $insert->execute([':u' => $username, ':e' => $email, ':p' => $hash]);

            // Set session + cookie
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            setcookie("user", $username, time() + (86400 * 7), "/"); // 7-day cookie

            $success = "Registration successful! Redirecting to home...";
            header("refresh:2;url=home.php");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register | BlogApp</title>
<link rel="stylesheet" href="auth.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-box">
        <h2>Create an Account</h2>

        <?php if ($errors): ?>
            <div class="alert error">
                <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" value="<?= htmlspecialchars($username ?? '') ?>" required>
            <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email ?? '') ?>" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>

        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>
</body>
</html>
