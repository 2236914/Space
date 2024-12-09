<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['session_id']) || !isset($data['rating'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Check if feedback already exists
    $check_stmt = $pdo->prepare("SELECT feedback_id FROM student_feedback WHERE session_id = ?");
    $check_stmt->execute([$data['session_id']]);
    
    if ($check_stmt->fetch()) {
        throw new Exception('You have already submitted feedback for this session');
    }

    // Insert feedback
    $stmt = $pdo->prepare("
        INSERT INTO student_feedback (
            session_id, 
            rating,
            comment,
            created_at
        ) VALUES (?, ?, ?, NOW())
    ");

    $stmt->execute([
        $data['session_id'],
        $data['rating'],
        $data['comment'] ?? null
    ]);

    // Commit transaction
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Rating submitted successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    error_log("Error submitting student feedback: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 