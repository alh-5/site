<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost'); // InfinityFree often uses localhost
define('DB_USER', 'if0_40960042');
define('DB_PASS', 'MmBKs5dtPWMbdh');
define('DB_NAME', 'if0_40960042_talent_coins');

// Create connection
function getDBConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    return $conn;
}
?>