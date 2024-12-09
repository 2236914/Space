<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once '../configs/config.php';

if (!isset($_POST['report_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Report ID and status are required']);
    exit();
}

try {
    $valid_statuses = ['pending', 'reviewed', 'resolved'];
    if (!in_array($_POST['status'], $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit();
    }

    $stmt = $pdo->prepare("
        UPDATE reports 
        SET status = ? 
        WHERE report_id = ?
    ");
    
    $result = $stmt->execute([$_POST['status'], $_POST['report_id']]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Report status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update report status']);
    }
} catch (PDOException $e) {
    error_log("Error updating report status: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} 