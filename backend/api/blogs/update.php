<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST"); // Using POST for simplicity

include_once '../../config/database.php';

$data = json_decode(file_get_contents("php://input"));

// Basic validation
if (!isset($data->id) || !isset($data->title) || !isset($data->content) || !isset($data->user_id)) {
    http_response_code(400);
    echo json_encode(array("message" => "Update failed. Incomplete data."));
    exit();
}

$post_id = intval($data->id);
$current_user_id = intval($data->user_id);
$title = $conn->real_escape_string($data->title);
$content = $conn->real_escape_string($data->content);


// --- CRITICAL SECURITY CHECK ---
// First, verify that the user trying to edit the post is the original author.
$auth_query = "SELECT user_id FROM blogPost WHERE id = ? LIMIT 1";
$auth_stmt = $conn->prepare($auth_query);
$auth_stmt->bind_param("i", $post_id);
$auth_stmt->execute();
$result = $auth_stmt->get_result();

if ($result->num_rows == 1) {
    $post = $result->fetch_assoc();
    $author_id = $post['user_id'];

    // If the author's ID does not match the logged-in user's ID, deny access.
    if ($author_id != $current_user_id) {
        http_response_code(403); // 403 Forbidden
        echo json_encode(array("message" => "Authorization failed. You are not the author of this post."));
        exit();
    }
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Post not found."));
    exit();
}
$auth_stmt->close();


// --- If security check passes, proceed with the update ---
$update_query = "UPDATE blogPost SET title = ?, content = ? WHERE id = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("ssi", $title, $content, $post_id);

if ($update_stmt->execute()) {
    http_response_code(200);
    echo json_encode(array("message" => "Post was updated."));
} else {
    http_response_code(503);
    echo json_encode(array("message" => "Unable to update post."));
}

$update_stmt->close();
$conn->close();
?>