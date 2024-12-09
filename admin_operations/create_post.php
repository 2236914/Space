<?php
session_start();
header('Content-Type: application/json');

require_once '../configs/config.php';
require_once '../includes/BadWordFilter.php'; // Include the BadWordFilter

// Debug session data
error_log("POST: " . print_r($_POST, true));
error_log("SESSION: " . print_r($_SESSION, true));

// Check if we have a logged-in user
if (!isset($_SESSION['user_id'])) {
    error_log("No user_id in session");
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Please log in']);
    exit();
}

try {
    // Check for bad words
    if (BadWordFilter::containsBadWords($_POST['content'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Your post contains inappropriate language.']);
        exit();
    }

    // Get username based on user_id
    if ($_SESSION['role'] === 'student') {
        $user_stmt = $pdo->prepare("SELECT username FROM students WHERE srcode = ?");
    } else {
        $user_stmt = $pdo->prepare("SELECT username FROM therapists WHERE therapist_id = ?");
    }
    
    $user_stmt->execute([$_SESSION['user_id']]);
    $username = $user_stmt->fetchColumn();

    // Insert the post
    $stmt = $pdo->prepare("
        INSERT INTO posts (username, content, post_type, status) 
        VALUES (?, ?, ?, 'active')
    ");

    $stmt->execute([
        $username,
        $_POST['content'],
        $_SESSION['role']
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Post created successfully']);
    exit();

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit();
}
?>
