<?php
session_start();
require_once '../config/dbcon.php';  // Adjust the path to your database connection file

$response = ['success' => false];

try {
    $userId = $_SESSION['user_id'];
    $today = date('Y-m-d');
    
    // Check refresh count
    $query = "INSERT INTO quote_refreshes (user_id, refresh_date, refresh_count) 
              VALUES (:user_id, :today, 1)
              ON DUPLICATE KEY UPDATE 
              refresh_count = IF(refresh_count < 5, refresh_count + 1, refresh_count)";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $userId, 'today' => $today]);
    
    if ($stmt->rowCount() > 0) {
        $response['success'] = true;
    } else {
        $response['message'] = 'Daily refresh limit reached';
    }
} catch (Exception $e) {
    $response['message'] = 'Error refreshing quote';
    error_log("Quote refresh error: " . $e->getMessage());
}

header('Content-Type: application/json');
echo json_encode($response);