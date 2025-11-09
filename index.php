<?php
session_start();

/*// If already logged in, go to home page automatically
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit;
}*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Welcome to WriteCore</title>
<link rel="stylesheet" href="index.css">
</head>
<body>

<!-- Header -->
<header>
    <h1>WriteCore</h1>
</header>

<!-- Main Section -->
<div class="container">
    <div class="content-box">
        <h2>Welcome to WriteCore</h2>
        <p>
            WriteCore is a platform where your words find meaning.  
            Share your thoughts, experiences, and creative ideas with the world.  
            Join a community of passionate writers and readers today.  
            Start your blogging journey and inspire others through your voice!
        </p>

        <div class="buttons">
            <button class="btn" id="getStarted">Get Started</button>
            <button class="btn" id="loginBtn">Login</button>
        </div>
    </div>
</div>

<!-- Footer -->
<footer>
    &copy; <?php echo date("Y"); ?> WriteCore. All rights reserved.
</footer>

<!-- Inline JavaScript for Interactivity -->
<script>
    // Button click redirections with smooth glow
    document.getElementById("getStarted").onclick = function() {
        this.classList.add('glow');
        setTimeout(() => window.location.href = "register.php", 300);
    };

    document.getElementById("loginBtn").onclick = function() {
        this.classList.add('glow');
        setTimeout(() => window.location.href = "login.php", 300);
    };

    // Smooth fade-in animation for box
    window.onload = () => {
        const box = document.querySelector('.content-box');
        box.style.opacity = 0;
        box.style.transition = "opacity 1s ease";
        setTimeout(() => box.style.opacity = 1, 200);
    };
</script>

</body>
</html>

