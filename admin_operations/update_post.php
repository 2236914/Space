<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once '../configs/config.php';

if (!isset($_POST['post_id']) || !isset($_POST['content'])) {
    echo json_encode(['success' => false, 'message' => 'Post ID and content are required']);
    exit();
}

try {
    $post_id = $_POST['post_id'];
    $content = $_POST['content'];
    $status = $_POST['status'] ?? null;

    // Start transaction
    $pdo->beginTransaction();

    // Update post content
    $query = "UPDATE posts SET content = ?, updated_at = CURRENT_TIMESTAMP";
    $params = [$content];

    // Add status to update if provided
    if ($status !== null) {
        $query .= ", status = ?";
        $params[] = $status;
    }

    $query .= " WHERE post_id = ?";
    $params[] = $post_id;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        // Log the action
        $admin_id = $_SESSION['user_id'];
        $log_query = "
            INSERT INTO admin_logs (admin_id, action_type, target_type, target_id, details)
            VALUES (?, 'update_post', 'post', ?, ?)";
        $log_stmt = $pdo->prepare($log_query);
        $log_stmt->execute([$admin_id, $post_id, "Updated post content" . ($status ? " and status to $status" : "")]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Post updated successfully']);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Post not found or no changes made']);
    }

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} 