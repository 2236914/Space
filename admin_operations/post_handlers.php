<?php
session_start();
require_once '../configs/config.php';

// Function to log activity
function logActivity($pdo, $action, $details = null) {
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['role'];
    $ipAddress = $_SERVER['REMOTE_ADDR'];

    try {
        if ($userRole === 'student') {
            $sql = "INSERT INTO activity_logs (srcode, action, action_details, ip_address) 
                    VALUES (?, ?, ?, ?)";
        } else {
            $sql = "INSERT INTO therapist_activity_logs (therapist_id, action, action_details, ip_address) 
                    VALUES (?, ?, ?, ?)";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $action, $details, $ipAddress]);
    } catch (PDOException $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

// At the start of the file, after session_start():
header('Content-Type: application/json');

// Handle GET requests (for getting comments)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'get_comments':
            handleGetComments($pdo);
            break;
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create_post':
            handleCreatePost($pdo);
            break;
        case 'toggle_like':
            handleToggleLike($pdo);
            break;
        case 'add_comment':
            handleAddComment($pdo);
            break;
    }
}

function handleCreatePost($pdo) {
    try {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
            throw new Exception('Unauthorized access');
        }

        $content = trim($_POST['content']);
        if (empty($content)) {
            throw new Exception('Post content cannot be empty');
        }

        $pdo->beginTransaction();

        // Get username based on role and ID
        if ($_SESSION['role'] === 'student') {
            $stmt = $pdo->prepare("SELECT username FROM students WHERE srcode = ?");
        } else {
            $stmt = $pdo->prepare("SELECT username FROM therapists WHERE therapist_id = ?");
        }
        
        $stmt->execute([$_SESSION['user_id']]);
        $username = $stmt->fetchColumn();

        if (!$username) {
            throw new Exception('Username not found');
        }

        // Insert the post with the correct post_type
        $stmt = $pdo->prepare("
            INSERT INTO posts (username, content, post_type, status) 
            VALUES (:username, :content, :post_type, 'active')
        ");

        $stmt->execute([
            ':username' => $username,
            ':content' => $content,
            ':post_type' => $_SESSION['role']  // This will be either 'student' or 'therapist'
        ]);

        $postId = $pdo->lastInsertId();

        // Handle image if present
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($_FILES['image']['tmp_name']);
            $imageName = $_FILES['image']['name'];
            
            $stmt = $pdo->prepare("
                UPDATE posts 
                SET image_file = :image_file, 
                    image_name = :image_name 
                WHERE post_id = :post_id
            ");
            
            $stmt->execute([
                ':image_file' => $imageData,
                ':image_name' => $imageName,
                ':post_id' => $postId
            ]);
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Post created successfully'
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function handleToggleLike($pdo) {
    try {
        error_log("Starting handleToggleLike");
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
            error_log("Unauthorized access attempt");
            throw new Exception('Unauthorized access');
        }

        $postId = $_POST['post_id'];
        error_log("Processing like for post ID: " . $postId);

        // Get current user's username
        if ($_SESSION['role'] === 'student') {
            $stmt = $pdo->prepare("SELECT username FROM students WHERE srcode = ?");
        } else {
            $stmt = $pdo->prepare("SELECT username FROM therapists WHERE therapist_id = ?");
        }
        $stmt->execute([$_SESSION['user_id']]);
        $username = $stmt->fetchColumn();

        error_log("Username found: " . ($username ? $username : 'not found'));

        if (!$username) {
            throw new Exception('User not found');
        }

        $pdo->beginTransaction();

        // Check if user already liked the post
        $stmt = $pdo->prepare("
            SELECT like_id FROM likes 
            WHERE post_id = ? AND username = ? AND liker_type = ?
        ");
        $stmt->execute([$postId, $username, $_SESSION['role']]);
        $existingLike = $stmt->fetch();

        if ($existingLike) {
            // Unlike
            $stmt = $pdo->prepare("DELETE FROM likes WHERE like_id = ?");
            $stmt->execute([$existingLike['like_id']]);
            $isLiked = false;
        } else {
            // Like
            $stmt = $pdo->prepare("
                INSERT INTO likes (post_id, username, liker_type) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$postId, $username, $_SESSION['role']]);
            $isLiked = true;
        }

        // Get updated like count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
        $stmt->execute([$postId]);
        $likeCount = $stmt->fetchColumn();

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'is_liked' => $isLiked,
            'like_count' => $likeCount
        ]);

    } catch (Exception $e) {
        error_log("Like error: " . $e->getMessage());
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function handleGetComments($pdo) {
    try {
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('Unauthorized access');
        }

        $postId = $_GET['post_id'];

        $stmt = $pdo->prepare("
            SELECT 
                c.*,
                CASE 
                    WHEN c.commenter_type = 'student' THEN s.firstname
                    ELSE t.firstname
                END as firstname,
                CASE 
                    WHEN c.commenter_type = 'student' THEN s.lastname
                    ELSE t.lastname
                END as lastname,
                c.username as user_id,
                c.commenter_type,
                TIMESTAMPDIFF(SECOND, c.created_at, NOW()) as seconds_ago
            FROM comments c
            LEFT JOIN students s ON c.username = s.username AND c.commenter_type = 'student'
            LEFT JOIN therapists t ON c.username = t.username AND c.commenter_type = 'therapist'
            WHERE c.post_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$postId]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format time ago for each comment
        foreach ($comments as &$comment) {
            $comment['time_ago'] = formatTimeAgo($comment['seconds_ago']);
        }

        echo json_encode([
            'success' => true,
            'comments' => $comments
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function handleAddComment($pdo) {
    try {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
            throw new Exception('Unauthorized access');
        }

        $postId = $_POST['post_id'];
        $content = trim($_POST['content']);

        if (empty($content)) {
            throw new Exception('Comment cannot be empty');
        }

        // Get current user's username
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

        $pdo->beginTransaction();

        // Insert comment
        $stmt = $pdo->prepare("
            INSERT INTO comments (post_id, username, comment_text, commenter_type) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$postId, $username, $content, $_SESSION['role']]);

        // Get updated comment count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
        $stmt->execute([$postId]);
        $commentCount = $stmt->fetchColumn();

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'comment_count' => $commentCount
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function formatTimeAgo($seconds) {
    if ($seconds < 60) {
        return "Just now";
    } elseif ($seconds < 3600) {
        $minutes = floor($seconds / 60);
        return $minutes . " minute" . ($minutes != 1 ? "s" : "") . " ago";
    } elseif ($seconds < 86400) {
        $hours = floor($seconds / 3600);
        return $hours . " hour" . ($hours != 1 ? "s" : "") . " ago";
    } elseif ($seconds < 604800) {
        $days = floor($seconds / 86400);
        return $days . " day" . ($days != 1 ? "s" : "") . " ago";
    } elseif ($seconds < 2592000) {
        $weeks = floor($seconds / 604800);
        return $weeks . " week" . ($weeks != 1 ? "s" : "") . " ago";
    } else {
        $months = floor($seconds / 2592000);
        return $months . " month" . ($months != 1 ? "s" : "") . " ago";
    }
}
