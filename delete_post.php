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

// Verify post belongs to user
$sql = "SELECT images FROM posts WHERE id = ? AND author_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: your_posts.php?error=not_found');
    exit;
}

$post = $result->fetch_assoc();
$stmt->close();

// Delete associated images from server
$images = json_decode($post['images'], true);
if (!empty($images)) {
    foreach ($images as $image) {
        if (file_exists($image)) {
            unlink($image);
        }
    }
}

// Delete post from database
$sql = "DELETE FROM posts WHERE id = ? AND author_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $post_id, $user_id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header('Location: your_posts.php?deleted=1');
    exit;
} else {
    $stmt->close();
    $conn->close();
    header('Location: your_posts.php?error=delete_failed');
    exit;
}
?>