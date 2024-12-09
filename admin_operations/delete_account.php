<?php
session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['srcode'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

try {
    $srcode = $_SESSION['srcode'];
    $reason = $_POST['delete_reason'];
    $details = $_POST['delete_details'];
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Update student record
    $stmt = $pdo->prepare("
        UPDATE students 
        SET status = 'deleted',
            deleted_at = CURRENT_TIMESTAMP,
            delete_reason = ?,
            delete_details = ?
        WHERE srcode = ?
    ");
    
    $stmt->execute([$reason, $details, $srcode]);
    
    // Log the deletion in activity log if you have one
    $logStmt = $pdo->prepare("
        INSERT INTO activity_logs (
            srcode,
            action,
            action_details,
            ip_address
        ) VALUES (?, 'ACCOUNT_DELETED', ?, ?)
    ");
    
    $logStmt->execute([
        $srcode,
        "Account deleted. Reason: $reason",
        $_SERVER['REMOTE_ADDR']
    ]);
    
    $pdo->commit();
    
    // Clear session
    session_destroy();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Account successfully deleted'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Account deletion error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to delete account. Please try again later.'
    ]);
}
?> 