<?php
// database.php - All-in-one solution
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Database config
$host = 'sql109.infinityfree.com';
$user = 'if0_40960042';
$pass = 'MmBKs5dtPWMbdh';
$dbname = 'if0_40960042_talent_coins';

// Connect
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    // If connection fails, return default data
    echo json_encode([
        'success' => true,
        'data' => [
            'talentCoins' => [],
            'multiplierActive' => false,
            'multiplierRate' => 1,
            'multiplierSeconds' => 0,
            'currentPage' => 1
        ],
        'message' => 'Using defaults (DB connection failed)'
    ]);
    exit();
}

// Determine action
$action = isset($_GET['action']) ? $_GET['action'] : 'load';
$user_id = 'talent_coins_shared_user';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'save') {
    // SAVE data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if ($data) {
        $talent_coins_json = json_encode($data['talent_coins'] ?? []);
        $multiplier_active = isset($data['multiplier_active']) ? ($data['multiplier_active'] ? 1 : 0) : 0;
        $multiplier_rate = $data['multiplier_rate'] ?? 1;
        $multiplier_seconds = $data['multiplier_seconds'] ?? 0;
        $current_page = $data['current_page'] ?? 1;
        
        // Create table if not exists
        $conn->query("CREATE TABLE IF NOT EXISTS talent_coins_data (
            user_id VARCHAR(50) PRIMARY KEY,
            talent_coins_json TEXT,
            multiplier_active TINYINT DEFAULT 0,
            multiplier_rate INT DEFAULT 1,
            multiplier_seconds INT DEFAULT 0,
            current_page INT DEFAULT 1,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Insert or update
        $sql = "INSERT INTO talent_coins_data (user_id, talent_coins_json, multiplier_active, multiplier_rate, multiplier_seconds, current_page) 
                VALUES ('$user_id', '" . $conn->real_escape_string($talent_coins_json) . "', $multiplier_active, $multiplier_rate, $multiplier_seconds, $current_page)
                ON DUPLICATE KEY UPDATE 
                talent_coins_json = VALUES(talent_coins_json),
                multiplier_active = VALUES(multiplier_active),
                multiplier_rate = VALUES(multiplier_rate),
                multiplier_seconds = VALUES(multiplier_seconds),
                current_page = VALUES(current_page)";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Saved']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Save failed: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
    }
} else {
    // LOAD data
    // Create table if not exists
    $conn->query("CREATE TABLE IF NOT EXISTS talent_coins_data (
        user_id VARCHAR(50) PRIMARY KEY,
        talent_coins_json TEXT,
        multiplier_active TINYINT DEFAULT 0,
        multiplier_rate INT DEFAULT 1,
        multiplier_seconds INT DEFAULT 0,
        current_page INT DEFAULT 1,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    $result = $conn->query("SELECT * FROM talent_coins_data WHERE user_id = '$user_id'");
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $talent_coins = json_decode($row['talent_coins_json'], true);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'talentCoins' => $talent_coins ?: [],
                'multiplierActive' => (bool)$row['multiplier_active'],
                'multiplierRate' => (int)$row['multiplier_rate'],
                'multiplierSeconds' => (int)$row['multiplier_seconds'],
                'currentPage' => (int)$row['current_page']
            ],
            'message' => 'Loaded'
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
            ],
            'message' => 'No data found'
        ]);
    }
}

$conn->close();
?>