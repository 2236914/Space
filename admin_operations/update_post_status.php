<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once '../configs/config.php';

if (!isset($_POST['post_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Post ID and status are required']);
    exit();
}

try {
    $post_id = intval($_POST['post_id']);  // Ensure post_id is an integer
    $status = $_POST['status'];

    // Debug: Log received data
    error_log("Updating post ID: " . $post_id . " to status: " . $status);

    // Validate status against ENUM values from database
    $valid_statuses = ['active', 'hidden', 'deleted'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value']);
        exit();
    }

    // First, check if the post exists and get its current status
    $check_query = "SELECT status FROM posts WHERE post_id = ?";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->execute([$post_id]);
    $post = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        echo json_encode(['success' => false, 'message' => 'Post not found']);
        exit();
    }

    // Update post status
    $update_query = "UPDATE posts SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE post_id = ?";
    $update_stmt = $pdo->prepare($update_query);
    $result = $update_stmt->execute([$status, $post_id]);

    if ($result) {
        // Log the action in admin_logs
        $admin_id = $_SESSION['user_id'];
        $log_query = "
            INSERT INTO admin_logs 
            (admin_id, action_type, target_type, target_id, details) 
            VALUES 
            (?, 'update_post_status', 'post', ?, ?)";
        
        $log_stmt = $pdo->prepare($log_query);
        $log_stmt->execute([
            $admin_id, 
            $post_id, 
            "Changed post status from {$post['status']} to {$status}"
        ]);

        echo json_encode([
            'success' => true, 
            'message' => 'Post status updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to update post status'
        ]);
    }

} catch (PDOException $e) {
    // Log the detailed error
    error_log('Database Error in update_post_status.php: ' . $e->getMessage());
    error_log('Error Code: ' . $e->getCode());
    error_log('Stack Trace: ' . $e->getTraceAsString());
    
    // Send a more detailed error message for debugging
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred',
        'debug' => [
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ]
    ]);
} 