<?php
session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id']) || !isset($_POST['content'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    // Get the username based on session user_id
    if ($_SESSION['role'] === 'student') {
        $stmt = $pdo->prepare("SELECT username FROM students WHERE srcode = ?");
    } else {
        $stmt = $pdo->prepare("SELECT username FROM therapists WHERE therapist_id = ?");
    }
    $stmt->execute([$_SESSION['user_id']]);
    $username = $stmt->fetchColumn();

    if (!$username) {
        throw new Exception('User not found');
    }

    // Insert comment with username - updated field names
    $stmt = $pdo->prepare("
        INSERT INTO comments (
            post_id, 
            username, 
            commenter_type, 
            comment_text,
            status
        ) VALUES (?, ?, ?, ?, 'active')
    ");

    $stmt->execute([
        $_POST['post_id'],
        $username,
        $_SESSION['role'],
        $_POST['content']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Comment added successfully'
    ]);

} catch (Exception $e) {
    error_log("Error in add_comment.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add comment: ' . $e->getMessage()
    ]);
}
?> 