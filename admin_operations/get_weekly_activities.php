<?php
require_once '../configs/config.php';
session_start();

header('Content-Type: application/json');

try {
    // Get activities for the last 7 days
    $query = "SELECT 
        DATE_FORMAT(created_at, '%a') as day,
        COUNT(*) as count,
        DATE(created_at) as full_date,
        MAX(created_at) as latest_activity
        FROM activity_logs 
        WHERE srcode = :user_id 
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        AND action IN (
            'Logged In',
            'Liked Quote',
            'Refreshed Quote',
            'Updated Profile',
            'Logged Mood',
            'Viewed Resource',
            'UPDATE_PROFILE_PICTURE'
        )
        GROUP BY DATE(created_at)
        ORDER BY full_date ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize arrays for all days of the week
    $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $counts = array_fill(0, 7, 0);
    
    // Fill in actual data
    foreach ($results as $row) {
        $dayIndex = array_search($row['day'], $days);
        if ($dayIndex !== false) {
            $counts[$dayIndex] = (int)$row['count'];
        }
    }

    // Calculate last activity time
    $lastActivity = '';
    if (!empty($results)) {
        $latest = max(array_column($results, 'latest_activity'));
        $timeAgo = time() - strtotime($latest);
        if ($timeAgo < 3600) {
            $lastActivity = floor($timeAgo / 60) . " minutes ago";
        } elseif ($timeAgo < 86400) {
            $lastActivity = floor($timeAgo / 3600) . " hours ago";
        } else {
            $lastActivity = floor($timeAgo / 86400) . " days ago";
        }
    }

    echo json_encode([
        'days' => $days,
        'counts' => $counts,
        'last_activity' => $lastActivity
    ]);

} catch (PDOException $e) {
    error_log("Error fetching activity data: " . $e->getMessage());
    echo json_encode(['error' => 'Error loading activity data']);
} 