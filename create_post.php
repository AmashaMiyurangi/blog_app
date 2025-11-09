<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db_connect.php';

// Redirect user to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $author_id = $_SESSION['user_id'];
    $date = date('Y-m-d H:i:s');

    // Validate inputs
    if (empty($title) || empty($content)) {
        $error = "Title and content are required!";
    } else {
        // Handle image uploads (multiple images)
        $images = [];
        if (!empty($_FILES['images']['name'][0])) {
            $upload_dir = "uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if (!empty($tmp_name) && $_FILES['images']['error'][$key] === 0) {
                    $file_name = time() . '_' . uniqid() . '_' . basename($_FILES['images']['name'][$key]);
                    $target_file = $upload_dir . $file_name;
                    
                    // Validate image type
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

        // Insert post into database
        $sql = "INSERT INTO posts (title, content, images, author_id, created_at) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("sssis", $title, $content, $images_json, $author_id, $date);
            
            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: home.php?success=1");
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
    <title>Create Post | WriteCore</title>
    <link rel="stylesheet" href="create_post.css">

    <!-- TinyMCE Rich Text Editor -->
    <script src="https://cdn.tiny.cloud/1/8b9tv9xrd9hiisgtikyzak4lqhwx5h4oll8u6gqy2xcnxl89/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
    <header class="header">
        <h2>WriteCore</h2>
    </header>

    <div class="container">
        <h2>Create a New Post</h2>

        <?php if (isset($error)): ?>
            <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Post Form -->
        <form method="POST" enctype="multipart/form-data" class="post-form" id="postForm">
            <!-- Post Title -->
            <label for="title">Post Title:</label>
            <input type="text" id="title" name="title" placeholder="Enter your post title" required>

            <!-- Post Content -->
            <label for="editor">Post Content:</label>
            <textarea name="content" id="editor" required></textarea>

            <!-- Image Upload -->
            <label for="images">Upload Images (Optional):</label>
            <input type="file" name="images[]" id="images" multiple accept="image/*">

            <!-- Submit Button -->
            <button type="submit" class="btn" id="submitBtn">Publish Post</button>

            <!-- Back Button -->
            <button type="button" class="btn" onclick="window.location.href='home.php'">Back</button>
        </form>
    </div>

    <footer class="footer">
        <p>© 2025 WriteCore. All rights reserved.</p>
    </footer>

    <script>
    // Initialize TinyMCE
    tinymce.init({
      selector: '#editor',
      height: 400,
      menubar: false,
      plugins: 'lists link image preview code table emoticons',
      toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | table | code preview emoticons',
      content_style: 'body { font-family:Poppins,sans-serif; font-size:14px; line-height:1.6; }',
      branding: false,
      toolbar_mode: 'floating',
      placeholder: "Write your blog content here...",
      setup: function (editor) {
        editor.on('change', function () {
          editor.save();
        });
      }
    });

    // Handle form submission
    document.getElementById('postForm').addEventListener('submit', function(e) {
        // Save TinyMCE content to textarea
        tinymce.triggerSave();
        
        // Get the content
        var content = tinymce.get('editor').getContent();
        var title = document.getElementById('title').value.trim();
        
        // Validate
        if (!title || !content) {
            e.preventDefault();
            alert('Please fill in both title and content!');
            return false;
        }
        
        // Show loading state
        document.getElementById('submitBtn').textContent = 'Publishing...';
        document.getElementById('submitBtn').disabled = true;
    });
    </script>
</body>
</html>