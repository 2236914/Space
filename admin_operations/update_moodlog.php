<?php
session_start();
require_once '../configs/config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['moodlog_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

try {
    $stmt = $pdo->prepare("UPDATE moodlog SET mood_name = :mood_name, description = :description 
                          WHERE moodlog_id = :id AND srcode = :srcode");
    $result = $stmt->execute([
        'mood_name' => $_POST['mood_name'],
        'description' => $_POST['description'],
        'id' => $_POST['moodlog_id'],
        'srcode' => $_SESSION['srcode']
    ]);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update mood log']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
