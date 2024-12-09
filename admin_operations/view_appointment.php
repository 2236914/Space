<?php
session_start();
require_once '../configs/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'therapist') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Appointment ID is required');
    }

    $query = "SELECT 
        ts.session_id,
        ts.srcode,
        ts.therapist_id,
        ts.session_date,
        ts.session_time,
        ts.session_type,
        ts.status,
        ts.notes,
        ts.created_at,
        ts.updated_at,
        s.firstname,
        s.lastname,
        s.email,
        s.course,
        s.year_level
    FROM therapy_sessions ts
    JOIN students s ON ts.srcode = s.srcode
    WHERE ts.session_id = :session_id 
    AND ts.therapist_id = :therapist_id";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'session_id' => $_GET['id'],
        'therapist_id' => $_SESSION['user_id']
    ]);

    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        throw new Exception('Appointment not found');
    }

    $appointment['session_date'] = date('F d, Y', strtotime($appointment['session_date']));
    $appointment['session_time'] = date('h:i A', strtotime($appointment['session_time']));
    $appointment['created_at'] = date('F d, Y h:i A', strtotime($appointment['created_at']));
    $appointment['updated_at'] = date('F d, Y h:i A', strtotime($appointment['updated_at']));

    $appointment['status'] = ucfirst($appointment['status']);
    
    $appointment['session_type'] = ucfirst($appointment['session_type']);

    echo json_encode([
        'status' => 'success',
        'data' => $appointment
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 