<?php
// Set required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST"); // Using POST to receive JSON body

// Include the database connection file
include_once '../../config/database.php';

// Get the data sent from the frontend
$data = json_decode(file_get_contents("php://input"));

// --- Basic Validation ---
// Check if the required IDs are present in the received data.
if (!isset($data->post_id) || !isset($data->user_id)) {
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "Delete failed. Incomplete data. Post ID and User ID are required."));
    exit();
}

// Sanitize the input
$post_id_to_delete = intval($data->post_id);
$current_user_id = intval($data->user_id);


// --- CRITICAL SECURITY CHECK ---
// Before deleting, we must verify that the user requesting the deletion is the actual author of the post.

$auth_query = "SELECT user_id FROM blogPost WHERE id = ? LIMIT 1";
$auth_stmt = $conn->prepare($auth_query);
$auth_stmt->bind_param("i", $post_id_to_delete);
$auth_stmt->execute();
$result = $auth_stmt->get_result();

if ($result->num_rows == 1) {
    $post = $result->fetch_assoc();
    $author_id = $post['user_id'];

    // Compare the post's author ID with the ID of the user who is currently logged in.
    if ($author_id != $current_user_id) {
        // If they do not match, the user is not authorized.
        http_response_code(403); // 403 Forbidden
        echo json_encode(array("message" => "Authorization failed. You are not the author of this post."));
        exit();
    }
} else {
    // If the post doesn't exist in the first place.
    http_response_code(404); // Not Found
    echo json_encode(array("message" => "Post not found."));
    exit();
}
$auth_stmt->close();


// --- If the security check passes, proceed with the deletion ---
$delete_query = "DELETE FROM blogPost WHERE id = ?";
$delete_stmt = $conn->prepare($delete_query);
$delete_stmt->bind_param("i", $post_id_to_delete);

// Execute the query
if ($delete_stmt->execute()) {
    // Check if any row was actually affected/deleted
    if ($delete_stmt->affected_rows > 0) {
        http_response_code(200); // OK
        echo json_encode(array("message" => "Post was deleted."));
    } else {
        // This case is rare but could happen if the post was deleted between the auth check and now
        http_response_code(404);
        echo json_encode(array("message" => "Post not found or already deleted."));
    }
} else {
    // If the delete query fails for some other reason
    http_response_code(503); // Service Unavailable
    echo json_encode(array("message" => "Unable to delete post."));
}

$delete_stmt->close();
$conn->close();
?>