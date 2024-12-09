<?php
header('Content-Type: application/json');
require_once '../configs/config.php';
require_once 'Logger.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $srcode = $_POST['srcode'];
        $status = $_POST['status'];
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Update student status
        $stmt = $pdo->prepare("
            UPDATE students 
            SET status = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE srcode = ?
        ");
        
        $stmt->execute([$status, $srcode]);
        
        // Log the status change
        $logger = new Logger($pdo);
        $logger->logActivity([
            'admin_id' => $_SESSION['user_id'],
            'action' => 'UPDATE_STUDENT_STATUS',
            'action_details' => "Student status updated to {$status} for SR-Code: {$srcode}",
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);
        
        $pdo->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Student status updated successfully'
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} 