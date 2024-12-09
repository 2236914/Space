<?php
session_start();
require_once '../configs/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit('Unauthorized access');
}

if (!isset($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('Missing message ID');
}

try {
    // First try student messages
    $stmt = $pdo->prepare("SELECT attachment_data, attachment_name, attachment_type FROM support_messages WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $attachment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$attachment) {
        // If not found, try therapist messages
        $stmt = $pdo->prepare("SELECT attachment_data, attachment_name, attachment_type FROM therapist_support_messages WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $attachment = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($attachment && $attachment['attachment_data']) {
        if (isset($_GET['download'])) {
            // Force download
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $attachment['attachment_name'] . '"');
        } else {
            // Display in browser (for images)
            header('Content-Type: ' . $attachment['attachment_type']);
        }
        echo $attachment['attachment_data'];
    } else {
        header('HTTP/1.1 404 Not Found');
        exit('Attachment not found');
    }

} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Database error');
}