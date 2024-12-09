<?php
require_once '../configs/config.php';
session_start();

header('Content-Type: application/json');

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Invalid request data: ' . json_last_error_msg());
    }

    // Debug log
    error_log('Booking Data: ' . print_r($data, true));
    error_log('Session srcode: ' . $_SESSION['srcode']);

    // Validate required fields
    if (empty($data['therapist_id']) || empty($data['session_date']) || 
        empty($data['session_time']) || empty($data['session_type'])) {
        throw new Exception('Missing required fields: ' . 
            implode(', ', array_filter(['therapist_id' => empty($data['therapist_id']),
                                      'session_date' => empty($data['session_date']),
                                      'session_time' => empty($data['session_time']),
                                      'session_type' => empty($data['session_type'])], 
                                     function($v) { return $v; })));
    }

    // Insert the booking
    $stmt = $pdo->prepare("INSERT INTO therapy_sessions (
        srcode,
        therapist_id,
        session_date,
        session_time,
        session_type,
        notes,
        status
    ) VALUES (?, ?, ?, ?, ?, ?, 'pending')");

    $result = $stmt->execute([
        $_SESSION['srcode'],
        $data['therapist_id'],
        $data['session_date'],
        $data['session_time'],
        $data['session_type'],
        $data['notes'] ?? null
    ]);

    if (!$result) {
        throw new Exception('Database error: ' . implode(' ', $stmt->errorInfo()));
    }

    echo json_encode([
        'success' => true,
        'message' => 'Session booked successfully'
    ]);

} catch (Exception $e) {
    error_log('Booking Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 