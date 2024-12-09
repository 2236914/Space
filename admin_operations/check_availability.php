<?php
require_once '../configs/config.php';

header('Content-Type: application/json');

if (!isset($_GET['therapist_id']) || !isset($_GET['date'])) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

try {
    // Get all booked sessions for the given date and therapist
    $stmt = $pdo->prepare("SELECT session_time 
                          FROM therapy_sessions 
                          WHERE therapist_id = ? 
                          AND session_date = ? 
                          AND status != 'cancelled'");
    $stmt->execute([$_GET['therapist_id'], $_GET['date']]);
    $bookedTimes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Define available time slots (8AM to 5PM)
    $timeSlots = [
        '08:00:00', '09:00:00', '10:00:00', '11:00:00', 
        '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00'
    ];
    
    // Mark which slots are available
    $availability = [];
    foreach ($timeSlots as $time) {
        $availability[$time] = !in_array($time, $bookedTimes);
    }

    echo json_encode([
        'success' => true,
        'availability' => $availability
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 