<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Include config
require_once 'config.php';

$user_id = 'talent_coins_shared_user'; // Fixed user ID

$conn = getDBConnection();

// Check if table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'talent_coins_data'");
if (mysqli_num_rows($table_check) == 0) {
    // Return empty data
    echo json_encode([
        'success' => true,
        'data' => [
            'talentCoins' => [],
            'multiplierActive' => false,
            'multiplierRate' => 1,
            'multiplierSeconds' => 0,
            'currentPage' => 1
        ]
    ]);
} else {
    // Get data
    $sql = "SELECT * FROM talent_coins_data WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $talent_coins = json_decode($row['talent_coins_json'], true);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'talentCoins' => $talent_coins ?: [],
                'multiplierActive' => (bool)$row['multiplier_active'],
                'multiplierRate' => (int)$row['multiplier_rate'],
                'multiplierSeconds' => (int)$row['multiplier_seconds'],
                'currentPage' => (int)$row['current_page']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'data' => [
                'talentCoins' => [],
                'multiplierActive' => false,
                'multiplierRate' => 1,
                'multiplierSeconds' => 0,
                'currentPage' => 1
            ]
        ]);
    }
}

mysqli_close($conn);
?>