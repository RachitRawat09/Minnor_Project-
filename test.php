<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PHP Configuration Test</h1>";

// Test PHP Version
echo "<h2>PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";

// Test Database Connection
echo "<h2>Database Connection Test</h2>";
include 'includes/db_connect.php';

if ($conn->connect_error) {
    echo "Database Connection Failed: " . $conn->connect_error;
} else {
    echo "Database Connection Successful!<br>";
    
    // Test Query
    $test_query = "SHOW TABLES";
    $result = $conn->query($test_query);
    
    if ($result) {
        echo "Tables in database:<br>";
        while ($row = $result->fetch_array()) {
            echo "- " . $row[0] . "<br>";
        }
    } else {
        echo "Query failed: " . $conn->error;
    }
}

// Test File Permissions
echo "<h2>File Permissions Test</h2>";
$directories = [
    'admin',
    'customer',
    'includes',
    'assets',
    'uploads',
    'qr_codes'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        echo "$dir directory exists and is " . (is_writable($dir) ? "writable" : "not writable") . "<br>";
    } else {
        echo "$dir directory does not exist<br>";
    }
}

// Test PHP Extensions
echo "<h2>PHP Extensions Test</h2>";
$required_extensions = [
    'mysqli',
    'gd',
    'json',
    'session'
];

foreach ($required_extensions as $ext) {
    echo "$ext extension is " . (extension_loaded($ext) ? "loaded" : "not loaded") . "<br>";
}

// Test Upload Directory
echo "<h2>Upload Directory Test</h2>";
$upload_dir = 'uploads';
if (is_dir($upload_dir)) {
    echo "Upload directory exists<br>";
    echo "Upload directory permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "<br>";
} else {
    echo "Upload directory does not exist<br>";
}

// Test Session
echo "<h2>Session Test</h2>";
session_start();
$_SESSION['test'] = 'working';
echo "Session is " . (isset($_SESSION['test']) ? "working" : "not working") . "<br>";
?> 