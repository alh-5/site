<?php
// test_db.php - Test database connection
$host = 'sql109.infinityfree.com';
$username = 'if0_40960042';
$password = 'MmBKs5dtPWMbdh';
$database = 'if0_40960042_talent_coins';

echo "Testing database connection...<br>";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "<br>");
} else {
    echo "✓ Connected successfully to database!<br>";
    
    // Test if table exists
    $sql = "SHOW TABLES LIKE 'talent_coins_data'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "✓ Table 'talent_coins_data' exists!<br>";
        
        // Show table structure
        $sql = "DESCRIBE talent_coins_data";
        $result = $conn->query($sql);
        
        echo "<br>Table structure:<br>";
        while ($row = $result->fetch_assoc()) {
            echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . "<br>";
        }
        
        // Check if our user exists
        $sql = "SELECT * FROM talent_coins_data WHERE user_id = 'talent_coins_shared_user'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            echo "<br>✓ User 'talent_coins_shared_user' exists in database!<br>";
            $row = $result->fetch_assoc();
            echo "Last updated: " . $row['last_updated'] . "<br>";
        } else {
            echo "<br>✗ User 'talent_coins_shared_user' NOT found in database!<br>";
            
            // Try to create the user
            $sql = "INSERT INTO talent_coins_data (user_id, talent_coins_json) VALUES ('talent_coins_shared_user', '[]')";
            if ($conn->query($sql) === TRUE) {
                echo "✓ Created user 'talent_coins_shared_user' successfully!<br>";
            } else {
                echo "✗ Error creating user: " . $conn->error . "<br>";
            }
        }
    } else {
        echo "✗ Table 'talent_coins_data' does NOT exist!<br>";
        echo "Please run the SQL to create the table.<br>";
    }
}

$conn->close();
?>