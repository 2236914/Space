<?php
session_start();
require_once '../configs/config.php';

if (isset($_POST['deactivate_account'])) {
    try {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('User ID not found in session');
        }
        
        $srcode = $_SESSION['user_id'];
        
        $pdo->beginTransaction();
        
        // 1. Update student status
        $update_query = "UPDATE students SET 
                        status = 'deactivated'
                        WHERE srcode = ?";
        
        $stmt_update = $pdo->prepare($update_query);
        if (!$stmt_update->execute([$srcode])) {
            throw new Exception('Failed to update student status');
        }
        
        // 2. Log the activity - Using your actual activity_logs structure
        $log_query = "INSERT INTO activity_logs 
                      (srcode, action, action_details, ip_address) 
                      VALUES (?, 'ACCOUNT_DEACTIVATION', ?, ?)";
        
        $action_details = "Student (SR-Code: " . $srcode . ") deactivated their account";
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        $stmt_log = $pdo->prepare($log_query);
        if (!$stmt_log->execute([$srcode, $action_details, $ip_address])) {
            throw new Exception('Failed to log activity');
        }
        
        // 3. Update session_logs to mark current session as inactive
        $session_update = "UPDATE session_logs 
                          SET logout_time = NOW(),
                              session_status = 'inactive' 
                          WHERE srcode = ? 
                          AND session_status = 'active'";
        
        $stmt_session = $pdo->prepare($session_update);
        if (!$stmt_session->execute([$srcode])) {
            throw new Exception('Failed to update session log');
        }
        
        // If all operations succeed, commit the transaction
        $pdo->commit();
        
        // Clear session and redirect
        session_destroy();
        header("Location: ../pages/signin.php?msg=account_deactivated");
        exit();
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Deactivation Error: " . $e->getMessage());
        header("Location: ../pages/student/account-settings.php?error=deactivation_failed&reason=" . urlencode($e->getMessage()));
        exit();
    }
}