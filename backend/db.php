<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "blog_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  // Stop the script and show an error if the connection fails
  die("Connection failed: " . $conn->connect_error);
}
?>