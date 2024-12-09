<?php
require_once __DIR__ . '/../configs/session_config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../configs/config.php';

// Add debugging to see what's happening
error_log('Session check - user_id: ' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));
error_log('Session check - role: ' . (isset($_SESSION['role']) ? $_SESSION['role'] : 'not set'));

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    // Add more specific error message
    if (!isset($_SESSION['user_id'])) {
        error_log('Login failed: user_id not set');
    } else if (empty($_SESSION['user_id'])) {
        error_log('Login failed: user_id empty');
    } else if ($_SESSION['role'] !== 'student') {
        error_log('Login failed: role is ' . $_SESSION['role']);
    }
    
    // Consider using absolute path
    header('Location: /Space/pages/signin.php?error=not_logged_in');
    exit();
}

try {
    // Check if student has already logged mood today
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as mood_count 
        FROM moodlog 
        WHERE srcode = ? 
        AND DATE(log_date) = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $today]);
    $moodCheck = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($moodCheck['mood_count'] > 0) {
        // If mood already logged, redirect directly to student dashboard
        header('Location: /Space/pages/student/student.php');
        exit();
    }
    
} catch (PDOException $e) {
    error_log("Error checking mood log: " . $e->getMessage());
    header('Location: /Space/pages/student/student.php?error=database_error');
    exit();
}

// If no mood logged today, continue with the rest of the page
?>