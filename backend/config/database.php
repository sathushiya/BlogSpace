<?php
// Database credentials for a standard XAMPP setup
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "blog_db";

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set character set to handle special characters and emojis
$conn->set_charset("utf8mb4");
?>