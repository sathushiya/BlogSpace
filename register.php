<?php
// api/register.php (FINAL CONFIRMED VERSION)

// Aggressively clean any pre-existing output buffers to prevent corruption.
if (ob_get_level()) ob_end_clean();

// The db.php file will set the JSON header and handle general error reporting.

// Prepare a default response array. This is the only thing we will output.
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

try {
    // Include the database connection.
    require 'db.php';
    
    // Get the data from the JavaScript POST request.
    $data = json_decode(file_get_contents('php://input'), true);

    // --- Input Validation ---
    if (is_null($data)) {
        $response['message'] = 'Invalid request data. Please try again.';
    } elseif (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
        $response['message'] = 'Please fill out all required fields.';
    } elseif (strlen($data['password']) < 6) {
        $response['message'] = 'Your password must be at least 6 characters long.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'The email address format is not valid.';
    } else {
        // If validation passes, proceed.
        $username = trim($data['username']);
        $email = trim($data['email']);
        $password = $data['password'];
        
        // Hash the password for secure storage.
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and execute the database query.
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashed_password]);

        // If we get here, it was successful.
        $response = ['success' => true, 'message' => 'Registration successful! You can now log in.'];
    }
} catch (PDOException $e) {
    // This specifically catches database errors, like a duplicate email.
    if ($e->errorInfo[1] == 1062) { // 1062 is MySQL's error code for a duplicate entry.
        $response['message'] = 'This email address is already registered.';
    } else {
        http_response_code(500);
        $response['message'] = 'A database error occurred. Please try again later.';
        // For your own debugging: error_log('Registration DB Error: ' . $e->getMessage());
    }
} catch (Exception $e) {
    // This catches any other unexpected errors.
    http_response_code(500);
    $response['message'] = 'A general server error occurred. Please try again later.';
    // For your own debugging: error_log('Registration General Error: ' . $e->getMessage());
}

// Finally, encode and output the single, clean response object, then stop the script.
echo json_encode($response);
exit();