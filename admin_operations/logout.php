<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../configs/config.php';

try {
    $current_time = date('Y-m-d H:i:s');
    
    // Add specific session log updates for admin and therapist
    if ($_SESSION['role'] === 'admin') {
        $adminSessionStmt = $pdo->prepare("
            UPDATE admin_session_logs 
            SET logout_time = ?, session_status = 'inactive' 
            WHERE admin_id = ? AND session_status = 'active'
        ");
        $adminSessionStmt->execute([$current_time, $_SESSION['user_id']]);
    } elseif ($_SESSION['role'] === 'therapist') {
        $therapistSessionStmt = $pdo->prepare("
            UPDATE therapist_session_logs 
            SET logout_time = ?, session_status = 'inactive' 
            WHERE therapist_id = ? AND session_status = 'active'
        ");
        $therapistSessionStmt->execute([$current_time, $_SESSION['user_id']]);
    }

    // Keep existing session and activity log code
    if (isset($_SESSION['session_log_id'])) {
        // Update session log
        $stmt = $pdo->prepare("
            UPDATE session_logs 
            SET logout_time = NOW(), 
                session_status = 'inactive' 
            WHERE session_id = ?
        ");
        $stmt->execute([$_SESSION['session_log_id']]);

        // Log the logout
        $logStmt = $pdo->prepare("
            INSERT INTO activity_logs 
            (srcode, therapist_id, admin_id, action, action_details, ip_address, created_at) 
            VALUES (?, ?, ?, 'LOGOUT', ?, ?, NOW())
        ");
        
        $srcode = ($_SESSION['role'] === 'student') ? $_SESSION['user_id'] : null;
        $therapist_id = ($_SESSION['role'] === 'therapist') ? $_SESSION['user_id'] : null;
        $admin_id = ($_SESSION['role'] === 'admin') ? $_SESSION['user_id'] : null;
        
        $logStmt->execute([
            $srcode,
            $therapist_id,
            $admin_id,
            'User logged out successfully',
            $_SERVER['REMOTE_ADDR']
        ]);
    }
} catch (PDOException $e) {
    error_log("Logout error: " . $e->getMessage());
} finally {
    // Clear session
    session_destroy();
    
    // Redirect to login page
    header("Location: /Space/pages/signin.php");
    exit;
}
?>