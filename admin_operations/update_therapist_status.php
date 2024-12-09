<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['therapist_id']) || !isset($_POST['status'])) {
        throw new Exception('Missing required parameters');
    }

    $stmt = $pdo->prepare("
        UPDATE therapists 
        SET status = ?, 
            updated_at = CURRENT_TIMESTAMP 
        WHERE therapist_id = ?
    ");

    $result = $stmt->execute([
        $_POST['status'],
        $_POST['therapist_id']
    ]);

    if (!$result) {
        throw new Exception('Failed to update therapist status');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Therapist status updated successfully'
    ]);

} catch (Exception $e) {
    error_log("Error in update_therapist_status.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 