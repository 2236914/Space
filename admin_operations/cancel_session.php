<?php
require_once '../configs/config.php';
session_start();

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['session_id'])) {
        throw new Exception('Session ID is required');
    }

    // Verify the session belongs to the current user
    $stmt = $pdo->prepare("
        SELECT * FROM therapy_sessions 
        WHERE session_id = ? AND srcode = ?
    ");
    $stmt->execute([$input['session_id'], $_SESSION['srcode']]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Session not found or unauthorized');
    }

    // Update session status
    $update = $pdo->prepare("
        UPDATE therapy_sessions 
        SET status = 'cancelled' 
        WHERE session_id = ?
    ");
    
    if ($update->execute([$input['session_id']])) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to cancel session');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 