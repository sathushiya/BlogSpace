<?php
session_start();
include 'backend/db.php';

// Check if ID is set
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$post_id = $_GET['id'];
$sql = "SELECT bp.*, u.username 
        FROM blogPost bp 
        JOIN user u ON bp.user_id = u.id 
        WHERE bp.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    echo "Post not found.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav>
        <div class="container">
            <a href="index.php" class="logo">MyBlog</a>
        </div>
    </nav>
    <div class="container">
        <div class="single-post">
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <p class="meta">by <?php echo htmlspecialchars($post['username']); ?> on <?php echo date('F j, Y', strtotime($post['created_at'])); ?></p>
            <div class="post-content">
                <?php echo htmlspecialchars($post['content']); ?>
            </div>
            
            <?php // Show edit/delete buttons only if the logged-in user is the author
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id']): ?>
                <div class="post-actions">
                    <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn-edit"><span class="material-symbols-outlined">edit</span>Edit</a>
                    <a href="backend/blog_logic.php?delete_post=<?php echo $post['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this post?');"><span class="material-symbols-outlined">delete</span>Delete</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>