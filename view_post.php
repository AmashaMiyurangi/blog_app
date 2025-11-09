<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and include database connection
session_start();
include 'db_connect.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get the logged-in username
$username = htmlspecialchars($_SESSION['username']);

// Get post ID from URL parameter
$post_id = $_GET['id'] ?? 0;

// Fetch the specific post with author details using JOIN
$sql = "SELECT p.*, u.username as author_name 
        FROM posts p 
        JOIN users u ON p.author_id = u.id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if post exists
if ($result->num_rows === 0) {
    header('Location: home.php?error=post_not_found');
    exit;
}

// Fetch post data
$post = $result->fetch_assoc();
$stmt->close();

// Decode JSON images array
$images = json_decode($post['images'], true);

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> | WriteCore</title>
    <link rel="stylesheet" href="view_post.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <h1>WriteCore</h1>
        <nav>
            <a href="home.php" class="btn">Home</a>
            <a href="your_posts.php" class="btn">Your Posts</a>
            <a href="logout.php" class="btn">Logout</a>
        </nav>
    </header>

    <!-- Main Content Area -->
    <main>
        <!-- Post Container -->
        <article class="post-container">
            
            <!-- Post Header with Author Info -->
            <div class="post-header">
                <div class="author-info">
                    <!-- Author Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span class="author-name"><?= htmlspecialchars($post['author_name']) ?></span>
                </div>
                <!-- Post Date -->
                <span class="post-date"><?= date('F d, Y - g:i A', strtotime($post['created_at'])) ?></span>
            </div>

            <!-- Post Title -->
            <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>

            <!-- Post Images Gallery (if images exist) -->
            <?php if (!empty($images)): ?>
                <div class="post-images">
                    <?php foreach ($images as $image): ?>
                        <img src="<?= htmlspecialchars($image) ?>" alt="Post Image" class="post-image" onerror="this.style.display='none'">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Post Content (HTML content from TinyMCE) -->
            <div class="post-content">
                <?= $post['content'] ?>
            </div>

            <!-- Action Buttons at Bottom -->
            <div class="post-actions">
                <!-- View More Posts Button - Goes to home.php -->
                <a href="home.php" class="action-btn primary-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    View More Posts
                </a>

                <!-- Back Button -->
                <a href="javascript:history.back()" class="action-btn secondary-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Go Back
                </a>
            </div>

        </article>
    </main>

    <!-- Footer Section -->
    <footer>
        <p>© <?= date('Y') ?> WriteCore. All rights reserved.</p>
    </footer>
</body>
</html>