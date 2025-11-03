<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit(); // Stop script execution
}

$user_id = $_SESSION['user_id'];

// Create Blog Post
if (isset($_POST['create_post'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $sql = "INSERT INTO blogPost (user_id, title, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $title, $content);

    if ($stmt->execute()) {
        header("Location: ../index.php");
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Update Blog Post
if (isset($_POST['update_post'])) {
    $post_id = $_POST['post_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Authorization: Make sure the user owns the post
    $sql = "UPDATE blogPost SET title = ?, content = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $title, $content, $post_id, $user_id);

    if ($stmt->execute()) {
        header("Location: ../single_post.php?id=" . $post_id);
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Delete Blog Post
if (isset($_GET['delete_post'])) {
    $post_id = $_GET['delete_post'];

    // Authorization: Make sure the user owns the post
    $sql = "DELETE FROM blogPost WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $post_id, $user_id);

    if ($stmt->execute()) {
        header("Location: ../index.php");
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>