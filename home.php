<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$username = htmlspecialchars($_SESSION['username']);

// Fetch recently posted blogs (latest first)
$sql = "SELECT p.*, u.username FROM posts p 
        JOIN users u ON p.author_id = u.id 
        ORDER BY p.created_at DESC LIMIT 12";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | WriteCore</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
<header>
    <h1>WriteCore</h1>
    <nav>
        <a href="logout.php" class="btn">Logout</a>
    </nav>
</header>

<main>
    <section class="welcome">
        <h2>Welcome to WriteCore, <?= $username ?>!</h2>
        <p>You're logged in successfully! Explore your blog space.</p>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;">
                ✅ Post created successfully!
            </div>
        <?php endif; ?>
        
        <!-- Buttons below welcome section -->
        <div class="buttons">
            <a href="create_post.php" class="btn">Create a New Post</a>
            <a href="your_posts.php" class="btn">Your Posts</a>
        </div>
    </section>

    <!-- Recently Posted Section -->
    <section class="recent-section">
        <h2>Recently Posted</h2>
        <div class="posts-grid">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                        $images = json_decode($row['images'], true);
                        $image = (!empty($images) && isset($images[0])) ? htmlspecialchars($images[0]) : 'default.jpg';
                        $shortContent = substr(strip_tags($row['content']), 0, 100) . '...';
                    ?>
                    <div class="post-box">
                        <img src="<?= $image ?>" alt="Post Image" onerror="this.src='default.jpg'">
                        <div class="post-box-content">
                            <h3><?= htmlspecialchars($row['title']) ?></h3>
                            <p><strong>Author:</strong> <?= htmlspecialchars($row['username']) ?></p>
                            <p class="post-date"><small><?= date('M d, Y', strtotime($row['created_at'])) ?></small></p>
                            <p><?= htmlspecialchars($shortContent) ?></p>
                            <a href="view_post.php?id=<?= $row['id'] ?>" class="read-btn">Read More</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No posts available yet. Be the first to create one!</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<footer>
    <p>© <?= date('Y') ?> WriteCore. All rights reserved.</p>
</footer>
</body>
</html>