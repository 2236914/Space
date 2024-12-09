<?php
require_once '../configs/config.php';

header('Content-Type: application/json');

if (!isset($_GET['therapist_id'])) {
    echo json_encode(['error' => 'Missing therapist ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT therapist_id, firstname, lastname, email, specialization, license_number 
                          FROM therapists 
                          WHERE therapist_id = ?");
    $stmt->execute([$_GET['therapist_id']]);
    
    if ($therapist = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode($therapist);
    } else {
        echo json_encode(['error' => 'Therapist not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}