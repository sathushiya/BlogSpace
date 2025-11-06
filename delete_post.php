<?php
// api/delete_post.php

if (ob_get_level()) ob_end_clean();
header('Content-Type: application/json');
ini_set('session.save_path', realpath(dirname($_SERVER['DOCUMENT_ROOT']) . '/../tmp'));
session_start();

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to delete a post.';
} else {
    try {
        require 'db.php';
        
        $data = json_decode(file_get_contents('php://input'), true);
        $post_id = isset($data['id']) ? (int)$data['id'] : 0;
        $user_id = $_SESSION['user_id'];

        if ($post_id === 0) {
            $response['message'] = 'Invalid Post ID.';
        } else {
            // --- Authorization Check ---
            $stmt = $pdo->prepare("SELECT user_id FROM blog_posts WHERE id = ?");
            $stmt->execute([$post_id]);
            $post = $stmt->fetch();

            if (!$post || $post['user_id'] !== $user_id) {
                http_response_code(403); // Forbidden
                $response['message'] = 'Authorization failed. You do not own this post.';
            } else {
                // If authorization passes, delete the post.
                // Note: This does not delete the associated image file from the server.
                $deleteStmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
                $deleteStmt->execute([$post_id]);
                $response = ['success' => true, 'message' => 'Post deleted successfully.'];
            }
        }
    } catch (Exception $e) {
        http_response_code(500);
        $response['message'] = 'Server error: ' . $e->getMessage();
        error_log('Delete Post Error: ' . $e->getMessage());
    }
}

echo json_encode($response);
exit();