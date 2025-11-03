<?php
session_start();
include 'backend/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Simple Blog</title>
    <!-- Google Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav>
        <div class="container">
            <a href="index.php" class="logo">MyBlog</a>
            <ul>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span></li>
                    <li><a href="create_post.php"><span class="material-symbols-outlined">edit_square</span>Create Post</a></li>
                    <li><a href="backend/auth.php?logout=true"><span class="material-symbols-outlined">logout</span>Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container blog-list">
        <h1>All Blog Posts</h1>
        <div>
            <?php
            // Prepare and execute the query to fetch all posts with their author's username
            // We join blogPost (aliased as bp) with user (aliased as u)
            $sql = "SELECT bp.id, bp.title, bp.created_at, u.username 
                    FROM blogPost bp 
                    JOIN user u ON bp.user_id = u.id 
                    ORDER BY bp.created_at DESC";
            
            $result = $conn->query($sql);

            // Check if there are any posts
            if ($result && $result->num_rows > 0) {
                // Loop through each post and display it
                while($row = $result->fetch_assoc()) {
                    echo "<div class='blog-post-summary'>";
                    echo "<h2><a href='single_post.php?id=" . $row['id'] . "'>" . htmlspecialchars($row['title']) . "</a></h2>";
                    // Format the date for better readability
                    echo "<p class='meta'>by " . htmlspecialchars($row['username']) . " on " . date('F j, Y', strtotime($row['created_at'])) . "</p>";
                    echo "</div>";
                }
            } else {
                // If no posts are found, display a message
                echo "<p>No blog posts have been made yet. Be the first to create one!</p>";
            }
            
            // Close the database connection
            $conn->close();
            ?>
        </div>
    </div>
</body>
</html>