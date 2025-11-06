<?php
// db.php (FINAL VERSION that uses the .env file system)

// Include the config file that loads the environment variables from .env
require_once 'config.php';

// Configuration is now read from the environment
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');

// Error & Output Handling
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

// --- Database Connection ---
try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [ 
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
        PDO::ATTR_EMULATE_PREPARES => false, 
    ];
    $pdo = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    http_response_code(500);
    // This message is helpful for both local and live debugging.
    echo json_encode(['success' => false, 'message' => 'Database connection failed. Please check your .env file credentials.']);
    exit();
}