<?php
session_start();
error_log('Session data in get_dashboard_data.php: ' . print_r($_SESSION, true));

// Check if user is logged in and is a therapist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'therapist') {
    error_log('Access denied - User ID: ' . ($_SESSION['user_id'] ?? 'not set') . 
              ', Role: ' . ($_SESSION['role'] ?? 'not set'));
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

require_once '../configs/config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    $therapist_id = $_SESSION['therapist_id'] ?? null;
    
    if (!$therapist_id) {
        throw new Exception('Therapist ID not found in session');
    }

    $today = date('Y-m-d');
    $response = [
        'today_appointments' => 0,
        'pending_sessions' => 0,
        'student_interactions' => 0,
        'completion_rate' => 0,
        'session_types' => ['online' => 0, 'face-to-face' => 0],
        'upcoming_schedule' => []
    ];

    // Get today's appointments
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM therapy_sessions 
        WHERE therapist_id = ? 
        AND session_date = ? 
        AND status = 'confirmed'
    ");
    $stmt->execute([$therapist_id, $today]);
    $response['today_appointments'] = $stmt->fetchColumn();

    // Get pending sessions
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM therapy_sessions 
        WHERE therapist_id = ? 
        AND status = 'pending'
    ");
    $stmt->execute([$therapist_id]);
    $response['pending_sessions'] = $stmt->fetchColumn();

    // Get student interactions (from posts and comments)
    $stmt = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) 
             FROM posts 
             WHERE username = ? 
             AND post_type = 'therapist' 
             AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             AND status = 'active') +
            (SELECT COUNT(*) 
             FROM comments 
             WHERE username = ? 
             AND commenter_type = 'therapist' 
             AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             AND status = 'active')
        as total_interactions
    ");
    $stmt->execute([$_SESSION['username'], $_SESSION['username']]);
    $response['student_interactions'] = $stmt->fetchColumn();

    // Get session type distribution for last 30 days
    $stmt = $pdo->prepare("
        SELECT 
            session_type,
            COUNT(*) as count
        FROM therapy_sessions
        WHERE therapist_id = ?
        AND session_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
        AND status IN ('confirmed', 'completed')
        GROUP BY session_type
    ");
    $stmt->execute([$therapist_id]);
    $response['session_types'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Get upcoming schedule
    $stmt = $pdo->prepare("
        SELECT 
            ts.session_date,
            ts.session_time,
            ts.session_type,
            s.firstname,
            s.lastname
        FROM therapy_sessions ts
        JOIN students s ON ts.srcode = s.srcode
        WHERE ts.therapist_id = ?
        AND ts.session_date >= CURRENT_DATE
        AND ts.session_date <= DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
        AND ts.status = 'confirmed'
        ORDER BY ts.session_date, ts.session_time
        LIMIT 5
    ");
    $stmt->execute([$therapist_id]);
    $response['upcoming_schedule'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate completion rate (completed sessions / total sessions) for last 30 days
    $stmt = $pdo->prepare("
        SELECT 
            ROUND(
                (COUNT(CASE WHEN status = 'completed' THEN 1 END) * 100.0) / 
                COUNT(*), 1
            ) as completion_rate
        FROM therapy_sessions
        WHERE therapist_id = ?
        AND session_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
    ");
    $stmt->execute([$therapist_id]);
    $response['completion_rate'] = $stmt->fetchColumn() ?: 0;

    // Debug information
    error_log('Response data: ' . print_r($response, true));
    
    echo json_encode($response);

} catch (Exception $e) {
    error_log('Dashboard Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
} 

// Add debug logging
error_log('Session data in dashboard: ' . print_r($_SESSION, true)); 