<?php
session_start();
require_once '../config/dbcon.php';  // Adjust the path to your database connection file

$response = ['success' => false];

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $_SESSION['user_id'];
    $quoteId = $data['quote_id'];
    
    // Toggle like (delete if exists, insert if doesn't)
    $checkQuery = "SELECT id FROM quote_likes WHERE user_id = :user_id AND quote_id = :quote_id";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute(['user_id' => $userId, 'quote_id' => $quoteId]);
    
    if ($checkStmt->rowCount() > 0) {
        // Unlike - remove the record
        $query = "DELETE FROM quote_likes WHERE user_id = :user_id AND quote_id = :quote_id";
    } else {
        // Like - add new record
        $query = "INSERT INTO quote_likes (user_id, quote_id) VALUES (:user_id, :quote_id)";
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $userId, 'quote_id' => $quoteId]);
    
    $response['success'] = true;
    $response['action'] = $checkStmt->rowCount() > 0 ? 'unliked' : 'liked';
} catch (Exception $e) {
    $response['message'] = 'Error toggling like';
    error_log("Quote like toggle error: " . $e->getMessage());
}

header('Content-Type: application/json');
echo json_encode($response);