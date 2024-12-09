<?php
session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

if (isset($_GET['therapist_id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT t.*, pp.image_data, pp.mime_type 
            FROM therapists t 
            LEFT JOIN profile_pictures pp ON t.therapist_id = pp.user_id 
            AND pp.user_type = 'therapist' 
            WHERE t.therapist_id = ?
        ");
        
        $stmt->execute([$_GET['therapist_id']]);
        $therapist = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($therapist) {
            // Convert image data to base64 if exists
            if ($therapist['image_data']) {
                $therapist['image_data'] = base64_encode($therapist['image_data']);
            }
            
            echo json_encode([
                'success' => true,
                'data' => $therapist
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Therapist not found'
            ]);
        }
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} 