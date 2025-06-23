<?php
// You can enable error reporting below if you need to debug
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Database connection details go here
$host = "localhost";   // Server (Keep 'localhost' for local development)
$username = "root";    // Default username for XAMPP/MAMP/WAMP
$password = "";        // Leave empty if no password is set
$database = "code_to_cuisine";  // Change this to your database name
$port=3307;
$conn = new mysqli($host, $username, $password, $database, $port);

// Let's make sure the connection actually worked
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set the charset so we can handle emojis and special characters
$conn->set_charset("utf8mb4");

// Helper function to log DB errors if something goes wrong
function handleDBError($conn, $query) {
    if ($conn->error) {
        error_log("Database Error: " . $conn->error . " in query: " . $query);
        return false;
    }
    return true;
}
?>
