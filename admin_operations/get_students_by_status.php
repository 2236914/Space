<?php
header('Content-Type: application/json');
require_once '../configs/config.php';

if (isset($_GET['status'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                s.*,
                pp.image_data,
                pp.mime_type
            FROM students s
            LEFT JOIN profile_pictures pp ON 
                s.srcode = pp.user_id 
                AND pp.user_type = 'student'
                AND pp.status = 'active'
            WHERE s.status = ?
            ORDER BY s.created_date DESC
        ");
        
        $stmt->execute([$_GET['status']]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert image data to base64
        foreach ($students as &$student) {
            if ($student['image_data']) {
                $student['image_data'] = base64_encode($student['image_data']);
            }
        }
        
        echo json_encode($students);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} 