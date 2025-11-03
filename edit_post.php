<?php
session_start();
include 'backend/db.php';

// If user is not logged in, or no ID is set, redirect
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit();
}

$post_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM blogPost WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

// Authorization check: if post doesn't exist or user doesn't own it, redirect
if (!$post || $post['user_id'] != $user_id) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav>
        <div class="container">
            <a href="index.php" class="logo">MyBlog</a>
        </div>
    </nav>
    <div class="form-container">
        <h1>Edit Blog Post</h1>
        <form action="backend/blog_logic.php" method="POST">
            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
            
            <label for="title">Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
            
            <label for="content">Content</label>
            <textarea name="content" rows="15" required><?php echo htmlspecialchars($post['content']); ?></textarea>
            
            <button type="submit" name="update_post"><span class="material-symbols-outlined">save</span>Update Post</button>
        </form>
    </div>
</body>
</html>