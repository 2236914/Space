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

if (!isset($_GET['session_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Session ID is required'
    ]);
    exit;
}

try {
    $query = "SELECT 
        sf.*,
        ts.session_date,
        ts.session_time,
        ts.session_type,
        s.firstname as student_fname,
        s.lastname as student_lname,
        t.firstname as therapist_fname,
        t.lastname as therapist_lname
    FROM session_feedback sf
    JOIN therapy_sessions ts ON sf.session_id = ts.session_id
    JOIN students s ON ts.srcode = s.srcode
    JOIN therapists t ON ts.therapist_id = t.therapist_id
    WHERE sf.session_id = ?";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$_GET['session_id']]);
    $feedback = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($feedback) {
        // Format dates for display
        $feedback['session_date'] = date('F j, Y', strtotime($feedback['session_date']));
        $feedback['session_time'] = date('g:i A', strtotime($feedback['session_time']));
        
        // Format names
        $feedback['student_name'] = $feedback['student_fname'] . ' ' . $feedback['student_lname'];
        $feedback['therapist_name'] = $feedback['therapist_fname'] . ' ' . $feedback['therapist_lname'];
    }

    echo json_encode([
        'success' => true,
        'data' => $feedback
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching the feedback'
    ]);
} 