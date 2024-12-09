<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'therapist') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['session_id']) || !isset($data['diagnosis']) || !isset($data['recommendations'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Check if feedback already exists
    $check_stmt = $pdo->prepare("SELECT feedback_id FROM session_feedback WHERE session_id = ?");
    $check_stmt->execute([$data['session_id']]);
    
    if ($check_stmt->fetch()) {
        throw new Exception('Feedback already submitted for this session');
    }

    // Insert feedback
    $stmt = $pdo->prepare("
        INSERT INTO session_feedback (
            session_id, 
            diagnosis,
            recommendations,
            follow_up,
            follow_up_notes
        ) VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['session_id'],
        $data['diagnosis'],
        $data['recommendations'],
        $data['follow_up'] ?? 'no',
        $data['follow_up_notes'] ?? null
    ]);

    // Update session status
    $update_stmt = $pdo->prepare("
        UPDATE therapy_sessions 
        SET status = 'completed' 
        WHERE session_id = ?
    ");
    $update_stmt->execute([$data['session_id']]);

    // Commit transaction
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Session feedback submitted successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    error_log("Error submitting feedback: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 