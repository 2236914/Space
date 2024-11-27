<?php
session_start();
require_once '../configs/config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    die(json_encode(['error' => 'Unauthorized access']));
}

try {
    $stmt = $pdo->prepare("SELECT * FROM moodlog WHERE moodlog_id = :id AND srcode = :srcode");
    $stmt->execute([
        'id' => $_GET['id'],
        'srcode' => $_SESSION['srcode']
    ]);
    
    $moodlog = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($moodlog) {
        echo json_encode($moodlog);
    } else {
        echo json_encode(['error' => 'Mood log not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
