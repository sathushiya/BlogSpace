<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../../config/database.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->username) || !isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to register. Data is incomplete."));
    exit();
}

$username = $conn->real_escape_string($data->username);
$email = $conn->real_escape_string($data->email);
$password = $data->password;
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

$query = "INSERT INTO user (username, email, password, role) VALUES (?, ?, ?, 'user')";
$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $username, $email, $hashed_password);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(array("message" => "User was successfully registered."));
} else {
    http_response_code(503);
    echo json_encode(array("message" => "Unable to register the user. Email may already exist."));
}

$stmt->close();
$conn->close();
?>