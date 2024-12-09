<?php
session_start();
require_once '../configs/config.php';

try {
    // Get mood data for the last 30 days
    $stmt = $pdo->prepare("
        SELECT 
            DATE(log_date) as date,
            mood_level,
            mood_description
        FROM moodlog 
        WHERE srcode = ? 
        AND log_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
        ORDER BY log_date ASC
    ");
    
    $stmt->execute([$_SESSION['srcode']]);
    $moodLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dates = [];
    $levels = [];
    $descriptions = [];

    foreach ($moodLogs as $log) {
        $dates[] = date('M d', strtotime($log['date']));
        $levels[] = $log['mood_level'];
        $descriptions[] = $log['mood_description'];
    }

    echo json_encode([
        'success' => true,
        'dates' => $dates,
        'levels' => $levels,
        'descriptions' => $descriptions
    ]);

} catch (PDOException $e) {
    error_log("Error fetching mood trends: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch mood trends'
    ]);
}
?> 