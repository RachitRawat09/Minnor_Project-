<?php
// Enable error reporting for debugging
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Database configuration
$host = "localhost";   // Server (Keep 'localhost' for local development)
$username = "root";    // Default username for XAMPP/MAMP/WAMP
$password = "";        // Leave empty if no password is set
$database = "code_to_cuisine";  // Change this to your database name
$port=3307;
$conn = new mysqli($host, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Function to handle database errors
function handleDBError($conn, $query) {
    if ($conn->error) {
        error_log("Database Error: " . $conn->error . " in query: " . $query);
        return false;
    }
    return true;
}
?>
