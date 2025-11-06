<?php
// api/get_posts.php (UPDATED TO INCLUDE IMAGE FILENAME)

if (ob_get_level()) ob_end_clean();
$response = [];

try {
    require 'db.php';
    $post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : null;
    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

    // UPDATED: Added p.image_filename to the SELECT statement
    $sql = "SELECT p.id, p.title, p.content, p.image_filename, p.created_at, u.username as author, p.user_id 
            FROM blog_posts p 
            JOIN users u ON p.user_id = u.id";

    if ($post_id) {
        $sql .= " WHERE p.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$post_id]);
        $response = $stmt->fetch();
    } elseif ($user_id) {
        $sql .= " WHERE p.user_id = ? ORDER BY p.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $response = $stmt->fetchAll();
    } else {
        $sql .= " ORDER BY p.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $response = $stmt->fetchAll();
    }
} catch (Exception $e) {
    http_response_code(500);
    $response = ['error' => 'A server error occurred while fetching posts.'];
}

if ($response === false) { $response = []; }
echo json_encode($response);
exit();