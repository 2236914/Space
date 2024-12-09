<?php
session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    try {
        $srcode = $_SESSION['user_id'];
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $mood = $_POST['mood'];
        $entry_date = $_POST['date'];
        
        // Validation
        if (empty($content) || empty($title) || empty($mood) || empty($entry_date)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'All fields are required'
            ]);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO journal_entries 
                (srcode, title, content, mood, entry_date, created_at) 
            VALUES 
                (?, ?, ?, ?, ?, NOW())
        ");
        
        if ($stmt->execute([$srcode, $title, $content, $mood, $entry_date])) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Journal entry saved successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to save journal entry'
            ]);
        }

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error occurred'
        ]);
    }
} else {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
} 