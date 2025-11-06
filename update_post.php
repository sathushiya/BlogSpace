<?php
// api/update_post.php

if (ob_get_level()) ob_end_clean();
header('Content-Type: application/json');
ini_set('session.save_path', realpath(dirname($_SERVER['DOCUMENT_ROOT']) . '/../tmp'));
session_start();

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to edit a post.';
} else {
    try {
        require 'db.php';
        
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        $post_id = isset($_POST['post-id']) ? (int)$_POST['post-id'] : 0;
        $user_id = $_SESSION['user_id'];

        if (empty($title) || empty($content) || $post_id === 0) {
            $response['message'] = 'Invalid data provided.';
        } else {
            // --- Authorization Check ---
            // First, find who owns the post they are trying to edit.
            $stmt = $pdo->prepare("SELECT user_id FROM blog_posts WHERE id = ?");
            $stmt->execute([$post_id]);
            $post = $stmt->fetch();

            if (!$post || $post['user_id'] !== $user_id) {
                http_response_code(403); // Forbidden
                $response['message'] = 'Authorization failed. You do not own this post.';
            } else {
                // If authorization passes, proceed with the update.
                // Note: Image updates are not handled in this version for simplicity.
                $updateStmt = $pdo->prepare("UPDATE blog_posts SET title = ?, content = ? WHERE id = ?");
                $updateStmt->execute([$title, $content, $post_id]);
                $response = ['success' => true, 'message' => 'Post updated successfully.'];
            }
        }
    } catch (Exception $e) {
        http_response_code(500);
        $response['message'] = 'Server error: ' . $e->getMessage();
        error_log('Update Post Error: ' . $e->getMessage());
    }
}

echo json_encode($response);
exit();