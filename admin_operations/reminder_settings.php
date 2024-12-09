<?php
session_start();
require_once '../configs/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $reminder_time = $_POST['reminder_time'] ?? '17:00:00';
    $is_enabled = $_POST['is_enabled'] ?? true;

    try {
        $stmt = $pdo->prepare("INSERT INTO reminder_settings (user_id, reminder_time, is_enabled) 
                              VALUES (?, ?, ?) 
                              ON DUPLICATE KEY UPDATE 
                              reminder_time = VALUES(reminder_time),
                              is_enabled = VALUES(is_enabled)");
        $stmt->execute([$user_id, $reminder_time, $is_enabled]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("SELECT reminder_time, is_enabled FROM reminder_settings WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode($settings ?: ['reminder_time' => '17:00:00', 'is_enabled' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>