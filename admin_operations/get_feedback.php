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

// Get feedback ID from request
$feedback_id = $_GET['feedback_id'] ?? null;

if (!$feedback_id) {
    echo json_encode(['success' => false, 'message' => 'Feedback ID is required']);
    exit();
}

try {
    // Prepare query based on user role
    if ($_SESSION['role'] === 'therapist') {
        $stmt = $pdo->prepare("
            SELECT sf.*, ts.therapist_id
            FROM session_feedback sf
            JOIN therapy_sessions ts ON sf.session_id = ts.session_id
            WHERE sf.feedback_id = ? AND ts.therapist_id = ?
        ");
        $stmt->execute([$feedback_id, $_SESSION['user_id']]);
    } else {
        throw new Exception('Invalid user role');
    }

    $feedback = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$feedback) {
        throw new Exception('Feedback not found or access denied');
    }

    echo json_encode([
        'success' => true,
        'diagnosis' => $feedback['diagnosis'],
        'recommendations' => $feedback['recommendations'],
        'follow_up' => $feedback['follow_up'],
        'follow_up_notes' => $feedback['follow_up_notes'],
        'created_at' => $feedback['created_at']
    ]);

} catch (Exception $e) {
    error_log("Error retrieving feedback: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 