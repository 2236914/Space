<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once '../configs/config.php';

if (!isset($_GET['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'Post ID is required']);
    exit();
}

try {
    $post_id = $_GET['post_id'];
    
    // Get post details with engagement counts
    $query = "
        SELECT 
            p.*,
            COUNT(DISTINCT l.like_id) as likes,
            COUNT(DISTINCT c.comment_id) as comment_count,
            (
                SELECT COUNT(*)
                FROM reports r 
                WHERE r.reported_type = 'post' 
                AND r.reported_id = CAST(p.post_id AS CHAR)
            ) as report_count
        FROM posts p
        LEFT JOIN likes l ON p.post_id = l.post_id
        LEFT JOIN comments c ON p.post_id = c.post_id AND c.status = 'active'
        WHERE p.post_id = ?
        GROUP BY 
            p.post_id, p.username, p.content, p.image_file, 
            p.image_name, p.post_type, p.created_at, 
            p.updated_at, p.status";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        echo json_encode(['success' => false, 'message' => 'Post not found']);
        exit();
    }

    // Convert image data to base64 if exists
    if ($post['image_file']) {
        $post['image_file'] = base64_encode($post['image_file']);
    }

    // Get comments
    $comment_query = "
        SELECT 
            c.comment_id,
            c.username,
            c.comment_text,
            c.commenter_type,
            c.created_at,
            c.updated_at,
            c.status
        FROM comments c
        WHERE c.post_id = ? AND c.status = 'active'
        ORDER BY c.created_at DESC";
    
    $stmt = $pdo->prepare($comment_query);
    $stmt->execute([$post_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get reports
    $report_query = "
        SELECT 
            r.report_id,
            r.reporter_username,
            r.reporter_type,
            r.report_type,
            r.reason,
            r.status as report_status,
            r.created_at
        FROM reports r
        WHERE r.reported_type = 'post' 
        AND r.reported_id = ?
        ORDER BY r.created_at DESC";
    
    $stmt = $pdo->prepare($report_query);
    $stmt->execute([$post_id]);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add comments and reports to post data
    $post['comments'] = $comments;
    $post['reports'] = $reports;

    echo json_encode(['success' => true, 'data' => $post]);

} catch (PDOException $e) {
    error_log('Database Error in get_post_details.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
} 