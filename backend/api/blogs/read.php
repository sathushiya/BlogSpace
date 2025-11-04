<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../../config/database.php';

// Added p.image_filename to the SELECT statement
$query = "SELECT p.id, p.title, p.content, p.image_filename, p.created_at, u.username AS author
          FROM blogPost p
          LEFT JOIN user u ON p.user_id = u.id
          ORDER BY p.created_at DESC";

$result = $conn->query($query);
if ($result->num_rows > 0) {
    $posts_arr = ["records" => []];
    while ($row = $result->fetch_assoc()) {
        array_push($posts_arr["records"], $row);
    }
    http_response_code(200);
    echo json_encode($posts_arr);
} else {
    http_response_code(404);
    echo json_encode(["message" => "No blog posts found."]);
}
$conn->close();
?>