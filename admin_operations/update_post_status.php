<?php
session_start();
require_once '../configs/config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Log the incoming request
error_log("Received request: " . print_r($_POST, true));

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    error_log("Authentication failed: " . print_r($_SESSION, true));
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Validate input
if (!isset($_POST['post_id']) || !isset($_POST['status'])) {
    error_log("Missing required parameters");
    echo json_encode(['success' => false, 'message' => 'Post ID and status are required']);
    exit();
}

try {
    $pdo->beginTransaction();

    $post_id = intval($_POST['post_id']);
    $status = $_POST['status'];

    // Log the values we're working with
    error_log("Processing update for post_id: $post_id, status: $status");

    // Validate status
    $valid_statuses = ['active', 'hidden', 'deleted'];
    if (!in_array($status, $valid_statuses)) {
        throw new Exception("Invalid status value: $status");
    }

    // First verify the post exists
    $check_stmt = $pdo->prepare("SELECT status FROM posts WHERE post_id = ?");
    $check_stmt->execute([$post_id]);
    $post = $check_stmt->fetch();

    if (!$post) {
        throw new Exception("Post not found with ID: $post_id");
    }

    // Log current post status
    error_log("Current post status: " . print_r($post, true));

    // Update post status
    $update_stmt = $pdo->prepare("
        UPDATE posts 
        SET 
            status = ?,
            updated_at = CURRENT_TIMESTAMP 
        WHERE post_id = ?
    ");

    $update_result = $update_stmt->execute([$status, $post_id]);

    if (!$update_result) {
        throw new Exception("Failed to update post. PDO Error Info: " . print_r($update_stmt->errorInfo(), true));
    }

    // Log admin action
    $admin_id = $_SESSION['user_id'];
    $log_query = "
        INSERT INTO admin_activity_logs 
        (admin_id, action, action_details, ip_address) 
        VALUES 
        (?, 'update_post_status', ?, ?)";
    
    $log_stmt = $pdo->prepare($log_query);
    $log_stmt->execute([
        $admin_id,
        "Changed post {$post_id} status from {$post['status']} to {$status}",
        $_SERVER['REMOTE_ADDR']
    ]);

    $pdo->commit();

    error_log("Successfully updated post status");
    echo json_encode([
        'success' => true,
        'message' => 'Post status updated successfully'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    
    error_log("Error in update_post_status.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'debug' => [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
} 