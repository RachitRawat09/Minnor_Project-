<?php
// Database configuration
$host = "localhost";   // Server (Keep 'localhost' for local development)
$username = "root";    // Default username for XAMPP/MAMP/WAMP
$password = "";        // Leave empty if no password is set
$database = "code_to_cuisine";  // Change this to your database name
$port=3307;
// Create a connection
$conn = new mysqli($host, $username, $password, $database,$port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character encoding to avoid issues with special characters
$conn->set_charset("utf8");

?>
