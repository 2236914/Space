<?php
// Use the correct path relative to this file
require_once __DIR__ . '/../configs/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../pages/signin.php");
    exit();
}

// Check if student has already logged mood today
$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT COUNT(*) as mood_count 
    FROM moodlog 
    WHERE srcode = ? 
    AND DATE(log_date) = ?
");
$stmt->execute([$_SESSION['user_id'], $today]);
$moodCheck = $stmt->fetch();

// If mood already logged, redirect to dashboard
if ($moodCheck['mood_count'] > 0) {
    header("Location: ../../pages/student/student.php");
    exit();
}
?>