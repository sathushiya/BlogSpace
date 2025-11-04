<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    // Added p.image_filename to the SELECT statement
    $query = "SELECT p.id, p.user_id, p.title, p.content, p.image_filename, p.created_at, u.username AS author
              FROM blogPost p
              LEFT JOIN user u ON p.user_id = u.id
              WHERE p.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        http_response_code(200);
        echo json_encode($result->fetch_assoc());
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Post not found."]);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(["message" => "No post ID provided."]);
}
$conn->close();
?>