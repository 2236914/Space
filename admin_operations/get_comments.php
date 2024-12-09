<?php
session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['post_id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

try {
    // Updated query to use comment_text instead of content
    $stmt = $pdo->prepare("
        SELECT 
            c.comment_id,
            c.post_id,
            c.comment_text,
            c.username,
            c.commenter_type,
            c.created_at,
            CASE 
                WHEN c.commenter_type = 'student' THEN s.srcode
                ELSE t.therapist_id
            END as user_id,
            TIMESTAMPDIFF(SECOND, c.created_at, NOW()) as seconds_ago
        FROM comments c
        LEFT JOIN students s ON c.username = s.username AND c.commenter_type = 'student'
        LEFT JOIN therapists t ON c.username = t.username AND c.commenter_type = 'therapist'
        WHERE c.post_id = ? 
        AND c.status = 'active'
        ORDER BY c.created_at DESC
    ");
    
    $stmt->execute([$_GET['post_id']]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the time for each comment
    foreach ($comments as &$comment) {
        $seconds_ago = $comment['seconds_ago'];
        if ($seconds_ago < 60) {
            $comment['time_ago'] = "Just now";
        } elseif ($seconds_ago < 3600) {
            $minutes = floor($seconds_ago / 60);
            $comment['time_ago'] = $minutes . " minute" . ($minutes != 1 ? "s" : "") . " ago";
        } elseif ($seconds_ago < 86400) {
            $hours = floor($seconds_ago / 3600);
            $comment['time_ago'] = $hours . " hour" . ($hours != 1 ? "s" : "") . " ago";
        } else {
            $days = floor($seconds_ago / 86400);
            $comment['time_ago'] = $days . " day" . ($days != 1 ? "s" : "") . " ago";
        }
    }

    echo json_encode($comments);

} catch (PDOException $e) {
    error_log("Error in get_comments.php: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 