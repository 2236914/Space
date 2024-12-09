<?php
header('Content-Type: application/json');
require_once '../configs/config.php';

if(isset($_GET['srcode'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                s.*,
                pp.image_data,
                pp.mime_type,
                pp.status as picture_status
            FROM students s 
            LEFT JOIN profile_pictures pp ON 
                s.srcode = pp.user_id 
                AND pp.user_type = 'student'
                AND pp.status = 'active'
            WHERE s.srcode = ?
        ");
        
        $stmt->execute([$_GET['srcode']]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($student) {
            if($student['image_data']) {
                $student['image_data'] = base64_encode($student['image_data']);
                $student['has_profile_picture'] = true;
            } else {
                $student['has_profile_picture'] = false;
            }
            
            unset($student['picture_status']);
            
            echo json_encode($student);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Student not found'
            ]);
        }
    } catch(PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred'
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'SR-Code not provided'
    ]);
}