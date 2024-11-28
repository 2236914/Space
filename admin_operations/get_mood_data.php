<?php
session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    $user_id = $_SESSION['user_id'];
    error_log("Fetching mood data for user: " . $user_id);
    
    // Get mood data for the last 30 days
    $query = "SELECT DATE(log_date) as date, 
              AVG(CASE 
                  WHEN selected_emoji LIKE '%😊%' THEN 5
                  WHEN selected_emoji LIKE '%😄%' THEN 4
                  WHEN selected_emoji LIKE '%😐%' THEN 3
                  WHEN selected_emoji LIKE '%😔%' THEN 2
                  WHEN selected_emoji LIKE '%😢%' THEN 1
                  ELSE 3 END) as mood_score
              FROM moodlog 
              WHERE srcode = ? 
              AND log_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
              GROUP BY DATE(log_date)
              ORDER BY date ASC";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    
    $labels = [];
    $values = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $labels[] = date('M d', strtotime($row['date']));
        $values[] = round($row['mood_score'], 1);
    }
    
    error_log("Mood data fetched - Labels: " . json_encode($labels));
    error_log("Mood data fetched - Values: " . json_encode($values));
    
    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'values' => $values
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_mood_data.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error: ' . $e->getMessage()
    ]);
}
?> 