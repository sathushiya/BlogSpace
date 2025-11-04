<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// The target directory for uploads
// We go up two levels from /api/blogs/ to the root BlogSpacee/ and then into uploads/
$target_dir = dirname(__FILE__, 4) . "/uploads/";

// Check if a file was uploaded
if (!isset($_FILES['postImage'])) {
    http_response_code(400);
    echo json_encode(["message" => "No file was uploaded."]);
    exit();
}

$file = $_FILES['postImage'];
$image_name = basename($file["name"]);
$image_file_type = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

// --- Security Checks ---

// 1. Check if it's a real image
$check = getimagesize($file["tmp_name"]);
if ($check === false) {
    http_response_code(400);
    echo json_encode(["message" => "File is not a valid image."]);
    exit();
}

// 2. Check file size (e.g., max 5MB)
if ($file["size"] > 5000000) {
    http_response_code(400);
    echo json_encode(["message" => "Sorry, your file is too large (max 5MB)."]);
    exit();
}

// 3. Allow only specific file formats
$allowed_types = ["jpg", "jpeg", "png", "gif"];
if (!in_array($image_file_type, $allowed_types)) {
    http_response_code(400);
    echo json_encode(["message" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed."]);
    exit();
}

// --- Create a unique filename to prevent overwriting existing files ---
$unique_filename = uniqid('', true) . "." . $image_file_type;
$target_file = $target_dir . $unique_filename;

// --- Try to move the uploaded file to the target directory ---
if (move_uploaded_file($file["tmp_name"], $target_file)) {
    // If successful, return the new unique filename
    http_response_code(200);
    echo json_encode([
        "message" => "File uploaded successfully.",
        "filename" => $unique_filename
    ]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Sorry, there was an error uploading your file."]);
}
?>