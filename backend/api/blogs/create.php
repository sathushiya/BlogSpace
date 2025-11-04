<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/database.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->title) || !isset($data->content) || !isset($data->user_id)) {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create post. Data is incomplete."));
    exit();
}

$title = $conn->real_escape_string($data->title);
$content = $conn->real_escape_string($data->content);
$user_id = intval($data->user_id);
// Get image filename if it exists, otherwise null
$image_filename = isset($data->image_filename) ? $conn->real_escape_string($data->image_filename) : null;

$query = "INSERT INTO blogPost (title, content, image_filename, user_id) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);
// 'sssi' -> string, string, string, integer
$stmt->bind_param("sssi", $title, $content, $image_filename, $user_id);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(array("message" => "Post was created."));
} else {
    http_response_code(503);
    echo json_encode(array("message" => "Unable to create post."));
}
$stmt->close();
$conn->close();
?>