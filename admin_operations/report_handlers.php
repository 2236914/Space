<?php
session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'report_post':
            handlePostReport($pdo);
            break;
        case 'report_user':
            handleUserReport($pdo);
            break;
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
}

function handlePostReport($pdo) {
    try {
        // Get reporter's username based on role
        if ($_SESSION['role'] === 'student') {
            $stmt = $pdo->prepare("SELECT username FROM students WHERE srcode = ?");
        } else {
            $stmt = $pdo->prepare("SELECT username FROM therapists WHERE therapist_id = ?");
        }
        $stmt->execute([$_SESSION['user_id']]);
        $reporter_username = $stmt->fetchColumn();

        if (!$reporter_username) {
            throw new Exception('Reporter not found');
        }

        // Check if user has already reported this post
        $check_stmt = $pdo->prepare("
            SELECT COUNT(*) FROM reports 
            WHERE reporter_username = ? 
            AND reported_type = 'post' 
            AND reported_id = ?
        ");
        $check_stmt->execute([$reporter_username, $_POST['post_id']]);
        
        if ($check_stmt->fetchColumn() > 0) {
            throw new Exception('You have already reported this post');
        }

        // Insert report
        $stmt = $pdo->prepare("
            INSERT INTO reports (
                reporter_username, 
                reporter_type, 
                reported_type, 
                reported_id, 
                reason
            ) VALUES (?, ?, 'post', ?, ?)
        ");

        $stmt->execute([
            $reporter_username,
            $_SESSION['role'],
            $_POST['post_id'],
            $_POST['reason']
        ]);

        // Log the report action
        logActivity($pdo, 'report_post', "Reported post ID: " . $_POST['post_id']);

        echo json_encode([
            'success' => true,
            'message' => 'Report submitted successfully'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function handleUserReport($pdo) {
    try {
        // Get reporter's username based on role
        if ($_SESSION['role'] === 'student') {
            $stmt = $pdo->prepare("SELECT username FROM students WHERE srcode = ?");
        } else {
            $stmt = $pdo->prepare("SELECT username FROM therapists WHERE therapist_id = ?");
        }
        $stmt->execute([$_SESSION['user_id']]);
        $reporter_username = $stmt->fetchColumn();

        if (!$reporter_username) {
            throw new Exception('Reporter not found');
        }

        // Check if user has already reported this user
        $check_stmt = $pdo->prepare("
            SELECT COUNT(*) FROM reports 
            WHERE reporter_username = ? 
            AND reported_type = 'user' 
            AND reported_id = ?
        ");
        $check_stmt->execute([$reporter_username, $_POST['username']]);
        
        if ($check_stmt->fetchColumn() > 0) {
            throw new Exception('You have already reported this user');
        }

        // Insert report
        $stmt = $pdo->prepare("
            INSERT INTO reports (
                reporter_username, 
                reporter_type, 
                reported_type, 
                reported_id, 
                reason
            ) VALUES (?, ?, 'user', ?, ?)
        ");

        $stmt->execute([
            $reporter_username,
            $_SESSION['role'],
            $_POST['username'],
            $_POST['reason']
        ]);

        // Log the report action
        logActivity($pdo, 'report_user', "Reported user: " . $_POST['username']);

        echo json_encode([
            'success' => true,
            'message' => 'Report submitted successfully'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

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
    } catch (Exception $e) {
        error_log("Error logging activity: " . $e->getMessage());
    }
}