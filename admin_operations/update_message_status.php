<?php
session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if (!isset($_POST['message_id'], $_POST['status'], $_POST['sender_type'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

try {
    $table = $_POST['sender_type'] === 'student' ? 'support_messages' : 'therapist_support_messages';
    $status = $_POST['status'];
    
    // Validate status is one of the allowed values
    $allowed_statuses = ['pending', 'in_progress', 'resolved', 'archived'];
    if (!in_array($status, $allowed_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }
    
    $stmt = $pdo->prepare("UPDATE $table SET status = ? WHERE id = ?");
    $result = $stmt->execute([$status, $_POST['message_id']]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Status updated successfully',
            'new_status' => $status
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No message found with the given ID']);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>