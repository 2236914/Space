<?php
session_start();
require_once '../configs/config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Not authenticated']));
}

try {
    // Get last 7 days of activity
    $query = "SELECT 
        DATE_FORMAT(created_at, '%a') as day,
        COUNT(*) as count
        FROM activity_logs 
        WHERE srcode = :srcode 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        AND action IN (
            'Logged In',
            'Liked Quote',
            'Refreshed Quote',
            'Updated Profile',
            'Logged Mood',
            'Viewed Resource'
        )
        GROUP BY DATE_FORMAT(created_at, '%a')
        ORDER BY FIELD(day, 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun')";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['srcode' => $_SESSION['user_id']]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Initialize all days with 0
    $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $data = array_fill_keys($days, 0);
    
    // Fill in actual data
    foreach ($results as $row) {
        $data[$row['day']] = (int)$row['count'];
    }
    
    echo json_encode([
        'labels' => array_keys($data),
        'data' => array_values($data)
    ]);
} catch (Exception $e) {
    error_log("Error fetching activity data: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to fetch data']);
}