<?php
// create_post.php (FINAL VERSION - Saves images to the ROOT folder)

if (ob_get_level()) ob_end_clean();
header('Content-Type: application/json');
ini_set('session.save_path', realpath(dirname($_SERVER['DOCUMENT_ROOT']) . '/../tmp'));
session_start();

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    $response['message'] = 'You must be logged in to create a post.';
} else {
    try {
        require 'db.php';
        
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        
        if (empty($title) || empty($content)) {
            $response['message'] = 'Title and content cannot be empty.';
        } else {
            $image_filename = null;

            // --- Image Upload Handling ---
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image = $_FILES['image'];
                
                // UPDATED: Define the destination as the current directory (the root folder).
                $upload_dir = '.'; 

                // Check if the root directory is writable.
                if (!is_writable($upload_dir)) {
                    throw new Exception("Server Error: The root directory (htdocs) does not have write permissions. Please check folder permissions.");
                }
                
                // Validate file type securely
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime_type = $finfo->file($image['tmp_name']);
                if (!in_array($mime_type, $allowed_types)) {
                    throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed.');
                }

                // Generate a unique filename
                $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
                $unique_id = uniqid('', true);
                $image_filename = "post-{$_SESSION['user_id']}-{$unique_id}.{$extension}";
                
                // Set the final destination path
                $destination_path = $upload_dir . '/' . $image_filename;
                
                // Move the uploaded file to its final destination (the root folder)
                if (!move_uploaded_file($image['tmp_name'], $destination_path)) {
                    throw new Exception('Failed to save the uploaded file on the server.');
                }
            }

            // --- Database Insertion ---
            $user_id = $_SESSION['user_id'];
            $stmt = $pdo->prepare("INSERT INTO blog_posts (user_id, title, content, image_filename) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $title, $content, $image_filename]);

            $response = ['success' => true, 'message' => 'Post created successfully.'];
        }
    } catch (Exception $e) {
        http_response_code(500);
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
exit();