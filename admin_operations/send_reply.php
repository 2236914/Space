<?php
session_start();
require_once '../configs/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Get POST data
    $message_id = $_POST['message_id'] ?? null;
    $sender_type = $_POST['sender_type'] ?? null;
    $reply_text = $_POST['reply_text'] ?? null;

    if (!$message_id || !$sender_type || !$reply_text) {
        throw new Exception('Missing required fields');
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Determine which table to use
    $table_name = ($sender_type === 'student') ? 'support_replies' : 'therapist_support_replies';

    // Handle attachment if present
    $attachment_name = null;
    $attachment_type = null;
    $attachment_data = null;

    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $attachment_name = $_FILES['attachment']['name'];
        $attachment_type = $_FILES['attachment']['type'];
        $attachment_data = file_get_contents($_FILES['attachment']['tmp_name']);
    }

    // Insert reply
    $stmt = $pdo->prepare("
        INSERT INTO $table_name 
        (message_id, admin_id, reply_text, attachment_name, attachment_type, attachment_data) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $message_id,
        $_SESSION['user_id'],
        $reply_text,
        $attachment_name,
        $attachment_type,
        $attachment_data
    ]);

    // Update message status to 'in_progress' if it's pending
    $message_table = ($sender_type === 'student') ? 'support_messages' : 'therapist_support_messages';
    $stmt = $pdo->prepare("
        UPDATE $message_table 
        SET status = CASE 
            WHEN status = 'pending' THEN 'in_progress'
            ELSE status 
        END
        WHERE id = ?
    ");
    $stmt->execute([$message_id]);

    // Commit transaction
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Reply sent successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error in send_reply.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to send reply: ' . $e->getMessage()]);
}
?>