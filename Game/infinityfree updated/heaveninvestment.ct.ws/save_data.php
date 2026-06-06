<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Include config
require_once 'config.php';

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data']);
    exit;
}

$conn = getDBConnection();

// Prepare data
$user_id = 'talent_coins_shared_user'; // Fixed user ID
$talent_coins_json = json_encode($data['talent_coins'] ?? []);
$multiplier_active = isset($data['multiplier_active']) ? (int)$data['multiplier_active'] : 0;
$multiplier_rate = isset($data['multiplier_rate']) ? (int)$data['multiplier_rate'] : 1;
$multiplier_seconds = isset($data['multiplier_seconds']) ? (int)$data['multiplier_seconds'] : 0;
$current_page = isset($data['current_page']) ? (int)$data['current_page'] : 1;

// First, check if table exists, create if not
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'talent_coins_data'");
if (mysqli_num_rows($table_check) == 0) {
    // Create table
    $create_table = "CREATE TABLE talent_coins_data (
        user_id VARCHAR(50) PRIMARY KEY,
        talent_coins_json TEXT,
        multiplier_active TINYINT DEFAULT 0,
        multiplier_rate INT DEFAULT 1,
        multiplier_seconds INT DEFAULT 0,
        current_page INT DEFAULT 1,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    mysqli_query($conn, $create_table);
    
    // Insert initial record
    $insert = "INSERT INTO talent_coins_data (user_id, talent_coins_json) VALUES ('$user_id', '$talent_coins_json')";
    mysqli_query($conn, $insert);
} else {
    // Update existing record
    $sql = "UPDATE talent_coins_data SET 
            talent_coins_json = '$talent_coins_json',
            multiplier_active = $multiplier_active,
            multiplier_rate = $multiplier_rate,
            multiplier_seconds = $multiplier_seconds,
            current_page = $current_page
            WHERE user_id = '$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        if (mysqli_affected_rows($conn) == 0) {
            // No rows updated, insert new
            $insert = "INSERT INTO talent_coins_data (user_id, talent_coins_json, multiplier_active, multiplier_rate, multiplier_seconds, current_page) 
                      VALUES ('$user_id', '$talent_coins_json', $multiplier_active, $multiplier_rate, $multiplier_seconds, $current_page)";
            mysqli_query($conn, $insert);
        }
    }
}

echo json_encode(['success' => true, 'message' => 'Saved']);
mysqli_close($conn);
?>