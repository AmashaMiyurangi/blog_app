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

$user_id = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);

// Fetch user's own posts (latest first)
$sql = "SELECT * FROM posts WHERE author_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Posts | WriteCore</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
<header>
    <h1>WriteCore</h1>
    <nav>
        <a href="home.php" class="btn">Home</a>
        <a href="logout.php" class="btn">Logout</a>
    </nav>
</header>

<main>
    <section class="welcome">
        <h2>Your Posts, <?= $username ?>!</h2>
        <p>Manage all your blog posts here.</p>
        
        <?php if (isset($_GET['updated'])): ?>
            <div class="success-message" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;">
                ✅ Post updated successfully!
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="success-message" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;">
                ✅ Post deleted successfully!
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;">
                ❌ Error: Unable to perform action.
            </div>
        <?php endif; ?>
        
        <div class="buttons">
            <a href="create_post.php" class="btn">Create a New Post</a>
        </div>
    </section>

    <!-- Your Posts Section -->
    <section class="recent-section">
        <h2>All Your Posts (<?= $result->num_rows ?>)</h2>
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
                        <div class="post-author-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span><?= htmlspecialchars($username) ?></span>
                        </div>
                        
                        <!-- Edit and Delete Icons on Left Side -->
                        <div class="post-action-icons">
                            <a href="edit_post.php?id=<?= $row['id'] ?>" class="action-icon edit-icon" title="Edit Post">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                <span>Edit</span>
                            </a>
                            <a href="delete_post.php?id=<?= $row['id'] ?>" class="action-icon delete-icon" title="Delete Post" onclick="return confirm('Are you sure you want to delete this post?')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                                <span>Delete</span>
                            </a>
                        </div>
                        
                        <div class="post-box-content">
                            <h3><?= htmlspecialchars($row['title']) ?></h3>
                            <p class="post-date"><small><?= date('M d, Y - h:i A', strtotime($row['created_at'])) ?></small></p>
                            <p><?= htmlspecialchars($shortContent) ?></p>
                            <a href="view_post.php?id=<?= $row['id'] ?>" class="read-btn">Read More</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>You haven't created any posts yet. <a href="create_post.php">Create your first post!</a></p>
            <?php endif; ?>
        </div>
    </section>
</main>

<footer>
    <p>© <?= date('Y') ?> WriteCore. All rights reserved.</p>
</footer>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>