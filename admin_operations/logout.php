<?php
session_start();
require_once '../configs/config.php';
require_once 'log_helper.php';

if (isset($_SESSION['user_id'])) {
    // Log the logout activity
    logActivity($pdo, [
        'srcode' => $_SESSION['role'] === 'student' ? $_SESSION['user_id'] : null,
        'therapist_id' => $_SESSION['role'] === 'therapist' ? $_SESSION['user_id'] : null,
        'admin_id' => in_array($_SESSION['role'], ['admin', 'superadmin']) ? $_SESSION['user_id'] : null,
        'action' => 'Logout',
        'action_details' => ucfirst($_SESSION['role']) . ' logged out'
    ]);
    
    // Update session log
    logSession($pdo, [
        'srcode' => $_SESSION['role'] === 'student' ? $_SESSION['user_id'] : null,
        'therapist_id' => $_SESSION['role'] === 'therapist' ? $_SESSION['user_id'] : null,
        'admin_id' => in_array($_SESSION['role'], ['admin', 'superadmin']) ? $_SESSION['user_id'] : null
    ], 'logout');
    
    // Clear session
    session_destroy();
}

header("Location: ../pages/signin.php");
exit();
?>