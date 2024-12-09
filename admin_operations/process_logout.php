<?php
session_start();
require_once '../configs/config.php';
require_once 'SessionLogger.php';

$sessionLogger = new SessionLogger($pdo);

// For students
if (isset($_SESSION['user_id'])) {
    $sessionLogger->endSession('student', $_SESSION['user_id']);
}
// For therapists
elseif (isset($_SESSION['therapist_id'])) {
    $sessionLogger->endSession('therapist', $_SESSION['therapist_id']);
}
// For admins
elseif (isset($_SESSION['admin_id'])) {
    $sessionLogger->endSession('admin', $_SESSION['admin_id']);
}

// Clear session data
session_destroy();

// Redirect to login page
header('Location: ../index.php');
exit();