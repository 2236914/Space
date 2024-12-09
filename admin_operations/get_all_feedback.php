<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get session ID from request
$session_id = $_GET['session_id'] ?? null;

if (!$session_id) {
    echo json_encode(['success' => false, 'message' => 'Session ID is required']);
    exit();
}

try {
    // Get therapist feedback
    $therapist_stmt = $pdo->prepare("
        SELECT *
        FROM session_feedback
        WHERE session_id = ?
    ");
    $therapist_stmt->execute([$session_id]);
    $therapist_feedback = $therapist_stmt->fetch(PDO::FETCH_ASSOC);

    // Get student feedback
    $student_stmt = $pdo->prepare("
        SELECT *
        FROM student_feedback
        WHERE session_id = ?
    ");
    $student_stmt->execute([$session_id]);
    $student_feedback = $student_stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'therapist_feedback' => $therapist_feedback,
        'student_feedback' => $student_feedback
    ]);

} catch (Exception $e) {
    error_log("Error retrieving feedback: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 