<?php
session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT 
            therapist_id,
            firstname,
            lastname,
            email,
            specialization,
            license_number,
            contact_number,
            status,
            created_date
        FROM therapists 
        ORDER BY created_date DESC
    ");
    
    $therapists = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
        "recordsTotal" => count($therapists),
        "recordsFiltered" => count($therapists),
        "data" => $therapists
    ]);

} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "draw" => 1,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Database error occurred"
    ]);
} 