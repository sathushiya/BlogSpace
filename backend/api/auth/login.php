<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/database.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(array("message" => "Login failed. Email and password are required."));
    exit();
}

$email = $data->email;
$password = $data->password;

$query = "SELECT id, username, password FROM user WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        http_response_code(200);
        echo json_encode(array(
            "message" => "Login successful.",
            "user" => array(
                "id" => $user['id'],
                "username" => $user['username']
            )
        ));
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Login failed. Incorrect password."));
    }
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Login failed. User does not exist."));
}

$stmt->close();
$conn->close();
?>