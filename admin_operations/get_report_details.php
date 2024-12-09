<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once '../configs/config.php';

if (!isset($_GET['report_id'])) {
    echo json_encode(['success' => false, 'message' => 'Report ID is required']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            p.content as post_content,
            p.image_file as post_image,
            p.created_at as post_date,
            CASE 
                WHEN p.post_type = 'student' THEN s.firstname
                ELSE t.firstname
            END as reporter_firstname,
            CASE 
                WHEN p.post_type = 'student' THEN s.lastname
                ELSE t.lastname
            END as reporter_lastname
        FROM reports r
        LEFT JOIN posts p ON r.reported_type = 'post' AND r.reported_id = p.post_id
        LEFT JOIN students s ON r.reporter_username = s.username AND r.reporter_type = 'student'
        LEFT JOIN therapists t ON r.reporter_username = t.username AND r.reporter_type = 'therapist'
        WHERE r.report_id = ?
    ");
    
    $stmt->execute([$_GET['report_id']]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($report) {
        echo json_encode(['success' => true, 'data' => $report]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Report not found']);
    }
} catch (PDOException $e) {
    error_log("Error fetching report details: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} 