<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../configs/config.php';

header('Content-Type: application/json');

// Modify the authentication check to be more specific
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'User not authenticated',
        'redirect' => '../signin.php'  // Use relative path
    ]);
    exit;
}

try {
    // Get mood data for the last 7 days
    $query = "SELECT 
        DATE(log_date) as date,
        mood_level,
        notes
    FROM moodlog 
    WHERE srcode = ? 
    AND log_date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
    ORDER BY log_date ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    
    if (!$stmt) {
        throw new Exception("Database query failed");
    }

    $mood_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Initialize arrays for the last 7 days
    $dates = [];
    $values = [];
    
    // Fill in data for all 7 days
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $found = false;
        
        foreach ($mood_data as $data) {
            if ($data['date'] === $date) {
                $dates[] = date('D', strtotime($date));
                $values[] = (int)$data['mood_level'];
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $dates[] = date('D', strtotime($date));
            $values[] = null; // null for missing data points
        }
    }

    echo json_encode([
        'success' => true,
        'labels' => $dates,
        'values' => $values
    ]);

} catch (Exception $e) {
    error_log("Mood data error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch mood data: ' . $e->getMessage()
    ]);
}
?> 