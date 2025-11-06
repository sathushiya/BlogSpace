<?php
// api/login.php (FINAL, AGGRESSIVE CLEAN-UP VERSION)

// This is the most important part. It erases any accidental output (like warnings or spaces)
// that may have occurred before this script runs.
if (ob_get_level()) ob_end_clean();

// The db.php file now handles the header, so it's already set.
// It also handles error reporting.

// Session management
ini_set('session.save_path', realpath(dirname($_SERVER['DOCUMENT_ROOT']) . '/../tmp'));
session_start();

// Prepare a default response array. We will only modify this.
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

try {
    // Include the database connection AFTER setting up the response.
    require 'db.php'; 
    
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if JSON decoding was successful and data exists
    if (is_null($data)) {
        $response['message'] = 'Invalid request data.';
    } elseif (empty($data['email']) || empty($data['password'])) {
        $response['message'] = 'Email and password fields are required.';
    } else {
        $email = $data['email'];
        $password = $data['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $response = ['success' => true, 'message' => 'Login successful!'];
        } else {
            $response['message'] = 'Invalid email or password.';
        }
    }
} catch (Exception $e) {
    // This will catch any fatal error during the process.
    http_response_code(500);
    $response['message'] = 'A server error occurred. Please try again later.';
    // For your own debugging, you can log the real error:
    // error_log('Login Error: ' . $e->getMessage());
}

// Finally, encode and output the single response object, then stop the script.
// No other echo statements should exist in this file.
echo json_encode($response);
exit();