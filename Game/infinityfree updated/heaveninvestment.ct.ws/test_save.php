<?php
// test_save.php - Test the save functionality
header('Content-Type: text/plain');

echo "Testing save_data.php...\n\n";

// Create test data
$test_data = [
    'user_id' => 'talent_coins_shared_user',
    'talent_coins' => [
        ['id' => 1, 'name' => 'Test Talent', 'count' => 5, 'note' => 'Test note']
    ],
    'multiplier_active' => false,
    'multiplier_rate' => 1,
    'multiplier_seconds' => 0,
    'current_page' => 1
];

// Convert to JSON
$json_data = json_encode($test_data);

// Make the request
$ch = curl_init('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/save_data.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($json_data)
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $http_code\n";
echo "Response: $response\n";

// Also test direct database connection
echo "\n\nTesting direct database connection...\n";
$host = 'sql109.infinityfree.com';
$username = 'if0_40960042';
$password = 'MmBKs5dtPWMbdh';
$database = 'if0_40960042_talent_coins';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo "Database connection failed: " . $conn->connect_error . "\n";
} else {
    echo "✓ Database connection successful!\n";
    
    // Test table
    $result = $conn->query("SHOW TABLES LIKE 'talent_coins_data'");
    if ($result->num_rows > 0) {
        echo "✓ Table 'talent_coins_data' exists!\n";
        
        // Show table structure
        $result2 = $conn->query("DESCRIBE talent_coins_data");
        echo "Table structure:\n";
        while ($row = $result2->fetch_assoc()) {
            echo "- {$row['Field']}: {$row['Type']}\n";
        }
    } else {
        echo "✗ Table 'talent_coins_data' does NOT exist!\n";
    }
    
    $conn->close();
}
?>