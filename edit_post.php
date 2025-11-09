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
$post_id = $_GET['id'] ?? 0;

// Fetch the post
$sql = "SELECT * FROM posts WHERE id = ? AND author_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: your_posts.php');
    exit;
}

$post = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';

    if (empty($title) || empty($content)) {
        $error = "Title and content are required!";
    } else {
        // Handle new image uploads
        $existing_images = json_decode($post['images'], true) ?? [];
        $images = $existing_images;

        if (!empty($_FILES['images']['name'][0])) {
            $upload_dir = "uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if (!empty($tmp_name) && $_FILES['images']['error'][$key] === 0) {
                    $file_name = time() . '_' . uniqid() . '_' . basename($_FILES['images']['name'][$key]);
                    $target_file = $upload_dir . $file_name;
                    
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (in_array($imageFileType, $allowed)) {
                        if (move_uploaded_file($tmp_name, $target_file)) {
                            $images[] = $target_file;
                        }
                    }
                }
            }
        }

        $images_json = !empty($images) ? json_encode($images) : NULL;

        // Update post
        $sql = "UPDATE posts SET title = ?, content = ?, images = ? WHERE id = ? AND author_id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("sssii", $title, $content, $images_json, $post_id, $user_id);
            
            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: your_posts.php?updated=1");
                exit();
            } else {
                $error = "Database Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Prepare Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post | WriteCore</title>
    <link rel="stylesheet" href="create_post.css">
    <script src="https://cdn.tiny.cloud/1/8b9tv9xrd9hiisgtikyzak4lqhwx5h4oll8u6gqy2xcnxl89/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
    <header class="header">
        <h2>WriteCore</h2>
    </header>

    <div class="container">
        <h2>Edit Post</h2>

        <?php if (isset($error)): ?>
            <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="post-form" id="postForm">
            <label for="title">Post Title:</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>

            <label for="editor">Post Content:</label>
            <textarea name="content" id="editor" required><?= htmlspecialchars($post['content']) ?></textarea>

            <?php 
            $existing_images = json_decode($post['images'], true);
            if (!empty($existing_images)): 
            ?>
                <label>Current Images:</label>
                <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                    <?php foreach ($existing_images as $img): ?>
                        <img src="<?= htmlspecialchars($img) ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <label for="images">Add More Images (Optional):</label>
            <input type="file" name="images[]" id="images" multiple accept="image/*">

            <button type="submit" class="btn" id="submitBtn">Update Post</button>
            <button type="button" class="btn" onclick="window.location.href='your_posts.php'">Cancel</button>
        </form>
    </div>

    <footer class="footer">
        <p>© 2025 WriteCore. All rights reserved.</p>
    </footer>

    <script>
    tinymce.init({
      selector: '#editor',
      height: 400,
      menubar: false,
      plugins: 'lists link image preview code table emoticons',
      toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | table | code preview emoticons',
      content_style: 'body { font-family:Poppins,sans-serif; font-size:14px; line-height:1.6; }',
      branding: false,
      toolbar_mode: 'floating',
      setup: function (editor) {
        editor.on('change', function () {
          editor.save();
        });
      }
    });

    document.getElementById('postForm').addEventListener('submit', function(e) {
        tinymce.triggerSave();
        var content = tinymce.get('editor').getContent();
        var title = document.getElementById('title').value.trim();
        
        if (!title || !content) {
            e.preventDefault();
            alert('Please fill in both title and content!');
            return false;
        }
        
        document.getElementById('submitBtn').textContent = 'Updating...';
        document.getElementById('submitBtn').disabled = true;
    });
    </script>
</body>
</html>