<?php
session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                therapist_id,
                firstname,
                lastname,
                email,
                specialization,
                license_number,
                contact_number,
                dob,
                age,
                status,
                created_date
            FROM therapists 
            WHERE therapist_id = ?
        ");
        
        $stmt->execute([$_GET['id']]);
        $therapist = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($therapist) {
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
        error_log("Error fetching therapist: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching therapist details'
        ]);
    }
}