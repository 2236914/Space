<?php
session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                entry_id,
                title,
                content,
                mood,
                DATE_FORMAT(entry_date, '%M %d, %Y') as formatted_date,
                DATE_FORMAT(created_at, '%h:%i %p') as formatted_time
            FROM journal_entries 
            WHERE srcode = ? 
            ORDER BY entry_date DESC, created_at DESC
        ");
        
        $stmt->execute([$_SESSION['user_id']]);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($entries)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'No entries found',
                'data' => []
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'data' => $entries
            ]);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to fetch entries'
        ]);
    }
} else {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'User not authenticated'
    ]);
} 