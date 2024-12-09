<?php
require_once '../config/database.php';
require_once '../helpers/SMSHelper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session_id = $_POST['session_id'] ?? null;
    $new_status = $_POST['status'] ?? null;

    if (!$session_id || !$new_status) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Get old status
        $stmt = $pdo->prepare("SELECT status FROM therapy_sessions WHERE session_id = ?");
        $stmt->execute([$session_id]);
        $old_status = $stmt->fetchColumn();

        // Update booking status
        $stmt = $pdo->prepare("
            UPDATE therapy_sessions 
            SET status = ? 
            WHERE session_id = ?
        ");
        
        $success = $stmt->execute([$new_status, $session_id]);

        if ($success) {
            // Only send SMS for confirmed or cancelled status
            if ($new_status === 'confirmed' || $new_status === 'cancelled') {
                $sms_result = SMSHelper::sendStatusUpdateNotification($session_id);
                
                if (!$sms_result['success']) {
                    error_log("SMS notification failed: " . ($sms_result['error'] ?? 'Unknown error'));
                }
            }
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            throw new Exception("Failed to update status");
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} 