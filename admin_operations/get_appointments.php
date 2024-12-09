<?php
session_start();
require_once '../configs/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$therapist_id = $_SESSION['user_id'];
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

try {
    $query = "SELECT 
        ts.*,
        s.firstname,
        s.lastname,
        s.email
    FROM therapy_sessions ts
    LEFT JOIN students s ON ts.srcode = s.srcode
    WHERE ts.therapist_id = ?";
    
    if ($status !== 'all') {
        $query .= " AND ts.status = ?";
    }
    
    $query .= " ORDER BY ts.session_date, ts.session_time";
    
    $stmt = $pdo->prepare($query);
    
    if ($status !== 'all') {
        $stmt->execute([$therapist_id, $status]);
    } else {
        $stmt->execute([$therapist_id]);
    }
    
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dates and times for display
    foreach ($appointments as &$appointment) {
        $appointment['session_date'] = date('M d, Y', strtotime($appointment['session_date']));
        $appointment['session_time'] = date('h:i A', strtotime($appointment['session_time']));
    }
    
    echo json_encode($appointments);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} 