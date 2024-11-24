<?php
session_start();
require_once __DIR__ . '/../configs/config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['quote_id'])) {
    die(json_encode(['success' => false, 'message' => 'Invalid request']));
}

try {
    $userId = $_SESSION['user_id'];
    $quoteId = $_POST['quote_id'];
    
    // Check if already liked
    $checkQuery = "SELECT id FROM quote_likes WHERE user_id = :user_id AND quote_id = :quote_id";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute(['user_id' => $userId, 'quote_id' => $quoteId]);
    $existing = $checkStmt->fetch();
    
    if ($existing) {
        // Unlike
        $query = "DELETE FROM quote_likes WHERE user_id = :user_id AND quote_id = :quote_id";
        $action = "Unliked Quote";
    } else {
        // Like
        $query = "INSERT INTO quote_likes (user_id, quote_id) VALUES (:user_id, :quote_id)";
        $action = "Liked Quote";
    }
    
    $stmt = $pdo->prepare($query);
    $success = $stmt->execute(['user_id' => $userId, 'quote_id' => $quoteId]);
    
    // Log the activity
    if ($success) {
        // Get quote content for activity log
        $quoteQuery = "SELECT content FROM quotes WHERE id = :quote_id";
        $quoteStmt = $pdo->prepare($quoteQuery);
        $quoteStmt->execute(['quote_id' => $quoteId]);
        $quote = $quoteStmt->fetch();
        
        $logQuery = "INSERT INTO activity_logs (srcode, action, action_details, ip_address) 
                    VALUES (:srcode, :action, :action_details, :ip_address)";
        $logStmt = $pdo->prepare($logQuery);
        $logStmt->execute([
            'srcode' => $userId,
            'action' => $action,
            'action_details' => "Quote: " . substr($quote['content'], 0, 100) . "...",
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'isLiked' => ($action === "Liked Quote"),
        'message' => substr($action, 0, -6) . 'd successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Error in like_quote.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>