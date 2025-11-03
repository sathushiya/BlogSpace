<?php
session_start();
// If user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Post</title>
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
        <h1>Create New Blog Post</h1>
        <form action="backend/blog_logic.php" method="POST">
            <label for="title">Title</label>
            <input type="text" name="title" placeholder="Blog Title" required>
            
            <label for="content">Content</label>
            <textarea name="content" rows="15" placeholder="Write your blog content here..." required></textarea>
            
            <button type="submit" name="create_post"><span class="material-symbols-outlined">publish</span>Publish Post</button>
        </form>
    </div>
</body>
</html>