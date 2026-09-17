<?php
// error_check.php - Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "PHP Version: " . phpversion() . "<br>";
echo "Current Directory: " . __DIR__ . "<br>";
echo "Script: " . $_SERVER['PHP_SELF'] . "<br><br>";

// Test basic PHP
echo "Testing PHP...<br>";
$test_array = ['test' => 'value'];
echo "JSON encode test: " . json_encode($test_array) . "<br>";

// Test database connection
echo "<br>Testing database connection...<br>";
$host = 'sql109.infinityfree.com';
$username = 'if0_40960042';
$password = 'MmBKs5dtPWMbdh';
$database = 'if0_40960042_talent_coins';

try {
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        echo "Connection failed: " . $conn->connect_error . "<br>";
    } else {
        echo "✓ Connected to database!<br>";
        
        // Test query
        $result = $conn->query("SELECT 1 as test");
        if ($result) {
            echo "✓ Query executed successfully!<br>";
        } else {
            echo "✗ Query failed: " . $conn->error . "<br>";
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "<br>";
}
?>