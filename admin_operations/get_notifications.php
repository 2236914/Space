<?php
session_start();
require_once '../configs/config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Not authenticated']));
}

if ($_SESSION['role'] !== 'student') {
    die(json_encode(['error' => 'Unauthorized access']));
}

try {
    date_default_timezone_set('Asia/Manila');
    
    // Notifications query (common for all users)
    $notifQuery = "SELECT 
        'notification' as source,
        id,
        type,
        title as header,
        message,
        created_at,
        icon,
        color_class
    FROM notifications 
    WHERE user_id = :user_id";

    // Activity logs query based on user role
    $logQuery = "";
    switch($_SESSION['role']) {
        case 'student':
            $logQuery = "SELECT 
                'activity_log' as source,
                log_id as id,
                'activity' as type,
                action as header,
                action_details as message,
                created_at,
                'edit_note' as icon,
                'bg-info' as color_class
            FROM activity_logs 
            WHERE srcode = :user_identifier";
            break;
            
        case 'therapist':
            $logQuery = "SELECT 
                'activity_log' as source,
                log_id as id,
                'activity' as type,
                action as header,
                action_details as message,
                created_at,
                'edit_note' as icon,
                'bg-info' as color_class
            FROM activity_logs 
            WHERE therapist_id = :user_identifier";
            break;
            
        case 'admin':
            $logQuery = "SELECT 
                'activity_log' as source,
                log_id as id,
                'activity' as type,
                action as header,
                action_details as message,
                created_at,
                'edit_note' as icon,
                'bg-info' as color_class
            FROM activity_logs 
            WHERE admin_id = :user_identifier";
            break;
    }

    // Mood logs query (only for students)
    $moodQuery = "";
    if ($_SESSION['role'] === 'student') {
        $moodQuery = "SELECT 
            'mood_log' as source,
            moodlog_id as id,
            'mood' as type,
            'Mood Log Added' as header,
            CONCAT('Logged mood: ', mood_name) as message,
            log_date as created_at,
            'mood' as icon,
            'bg-success' as color_class
        FROM moodlog 
        WHERE srcode = :user_identifier";
    }

    // Combine queries based on role
    $query = $notifQuery;
    if ($logQuery) {
        $query .= " UNION ($logQuery)";
    }
    if ($moodQuery) {
        $query .= " UNION ($moodQuery)";
    }
    $query .= " ORDER BY created_at DESC LIMIT 20";
    
    $stmt = $pdo->prepare($query);
    
    // Set parameters based on role
    $params = ['user_id' => $_SESSION['user_id']];
    switch($_SESSION['role']) {
        case 'student':
            $params['user_identifier'] = $_SESSION['srcode'];
            break;
        case 'therapist':
            $params['user_identifier'] = $_SESSION['therapist_id'];
            break;
        case 'admin':
            $params['user_identifier'] = $_SESSION['admin_id'];
            break;
    }
    
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Build HTML for notifications
    $html = '';
    if (empty($notifications)) {
        $html = '<div class="text-center text-muted py-4">No notifications yet</div>';
    } else {
        foreach ($notifications as $notif) {
            $html .= '<div class="notification-item d-flex py-3">';
            $html .= '<div class="my-auto">';
            $html .= '<div class="icon icon-shape icon-sm ' . htmlspecialchars($notif['color_class']) . ' shadow border-radius-sm text-center me-2 d-flex align-items-center justify-content-center">';
            $html .= '<span class="material-symbols-rounded text-white text-sm opacity-10">' . htmlspecialchars($notif['icon']) . '</span>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="d-flex flex-column justify-content-center searchable-content">';
            $html .= '<h6 class="text-sm font-weight-normal mb-1">' . htmlspecialchars($notif['header']) . '</h6>';
            $html .= '<p class="text-xs text-secondary mb-0">' . htmlspecialchars($notif['message']) . '</p>';
            $html .= '<p class="text-xs text-secondary mb-0">' . getTimeAgo($notif['created_at']) . '</p>';
            $html .= '</div>';
            $html .= '</div>';
        }
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode(['html' => $html]);

} catch (PDOException $e) {
    error_log("Error fetching notifications: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to fetch notifications']);
}

// Helper function for time formatting
function getTimeAgo($timestamp) {
    $datetime = new DateTime($timestamp);
    $now = new DateTime();
    $interval = $now->diff($datetime);
    
    if ($interval->y > 0) {
        return $datetime->format('F j, Y g:i A');
    }
    if ($interval->i == 0 && $interval->h == 0 && $interval->d == 0) {
        if ($interval->s < 30) return 'Just now';
        return $interval->s . ' seconds ago';
    }
    if ($interval->h == 0 && $interval->d == 0) {
        return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
    }
    if ($interval->d == 0) {
        return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
    }
    if ($interval->d == 1) {
        return 'Yesterday at ' . $datetime->format('g:i A');
    }
    if ($interval->d < 7) {
        return $interval->d . ' days ago';
    }
    return $datetime->format('F j, g:i A');
}
