<?php
require_once '../includes/session.php';
require_once '../includes/check_auth.php';
require_once '../database/database.php';

header('Content-Type: application/json');

// Check if user is authorized
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($data['session_id']) || !isset($data['diagnosis'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Required fields are missing'
        ]);
        exit;
    }

    // Check if feedback already exists
    $checkQuery = "SELECT feedback_id FROM session_feedback WHERE session_id = ?";
    $stmt = $pdo->prepare($checkQuery);
    $stmt->execute([$data['session_id']]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update existing feedback
        $query = "UPDATE session_feedback SET 
            diagnosis = ?,
            recommendations = ?,
            follow_up = ?,
            follow_up_notes = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE session_id = ?";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $data['diagnosis'],
            $data['recommendations'] ?? null,
            $data['follow_up'] ?? 'no',
            $data['follow_up_notes'] ?? null,
            $data['session_id']
        ]);
    } else {
        // Insert new feedback
        $query = "INSERT INTO session_feedback 
            (session_id, diagnosis, recommendations, follow_up, follow_up_notes) 
            VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $data['session_id'],
            $data['diagnosis'],
            $data['recommendations'] ?? null,
            $data['follow_up'] ?? 'no',
            $data['follow_up_notes'] ?? null
        ]);
    }

    // Update session status to completed
    $updateSession = "UPDATE therapy_sessions SET status = 'completed' WHERE session_id = ?";
    $stmt = $pdo->prepare($updateSession);
    $stmt->execute([$data['session_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Feedback saved successfully'
    ]);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error saving feedback'
    ]);
} 