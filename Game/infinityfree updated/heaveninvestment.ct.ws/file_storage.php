<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Simple file-based storage for InfinityFree
$data_file = 'talent_coins_data.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if ($data) {
        file_put_contents($data_file, json_encode($data, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true, 'message' => 'Saved to file']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
    }
} else {
    // Load data
    if (file_exists($data_file)) {
        $content = file_get_contents($data_file);
        $data = json_decode($content, true);
        
        echo json_encode([
            'success' => true,
            'data' => $data ?: [
                'talentCoins' => [],
                'multiplierActive' => false,
                'multiplierRate' => 1,
                'multiplierSeconds' => 0,
                'currentPage' => 1
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
?>