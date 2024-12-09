<?php
session_start();
require_once __DIR__ . '/../configs/config.php';

// Debug session
error_log("Session in get_mood_logs: " . print_r($_SESSION, true));

// Validate input
$date = isset($_GET['date']) ? $_GET['date'] : null;
$srcode = isset($_GET['srcode']) ? $_GET['srcode'] : null;

// Security check for student role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    error_log("Unauthorized access attempt: Not logged in as student");
    die('Unauthorized access: Please login as student');
}

// Get mood logs function
function getMoodHistory($srcode, $date = null) {
    global $pdo;
    
    try {
        if ($date) {
            $query = "SELECT moodlog_id, selected_emoji, mood_name, description, 
                      DATE_FORMAT(log_date, '%h:%i %p') as time,
                      DATE_FORMAT(log_date, '%M %d, %Y') as date
                      FROM moodlog 
                      WHERE srcode = :srcode 
                      AND DATE(log_date) = :date
                      ORDER BY log_date DESC";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':srcode' => $srcode,
                ':date' => $date
            ]);
        } else {
            $query = "SELECT moodlog_id, selected_emoji, mood_name, description,
                      DATE_FORMAT(log_date, '%h:%i %p') as time,
                      DATE_FORMAT(log_date, '%M %d, %Y') as date
                      FROM moodlog 
                      WHERE srcode = :srcode
                      ORDER BY log_date DESC";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([':srcode' => $srcode]);
        }
        
        return $stmt;
    } catch (PDOException $e) {
        error_log("Database error in getMoodHistory: " . $e->getMessage());
        return null;
    }
}

// Get mood logs
$moodLogs = getMoodHistory($srcode, $date);

// Return the HTML for mood logs
if ($moodLogs && $moodLogs->rowCount() > 0) {
    while ($log = $moodLogs->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <div class="mood-entry mb-4">
            <!-- Emoji and Mood -->
            <div class="d-flex align-items-center justify-content-center mb-2">
                <span class="mx-2" style="font-size: 1.5rem;">
                    <?php echo htmlspecialchars($log['selected_emoji']); ?>
                </span>
            </div>
            
            <!-- Mood Name and Date -->
            <div class="text-center mb-3">
                <span class="text-sm text-muted">
                    <?php echo htmlspecialchars($log['mood_name']); ?>
                </span>
                <br>
                <small class="text-xs text-secondary">
                    <?php echo $log['date'] . ' at ' . $log['time']; ?>
                </small>
            </div>
            
            <!-- Description Box -->
            <?php if (!empty($log['description'])) { ?>
                <div class="description-box blur shadow-blur border-radius-md">
                    <div class="overflow-auto p-3" style="max-height: 100px;">
                        <p class="text-sm mb-0">
                            <?php echo nl2br(htmlspecialchars($log['description'])); ?>
                        </p>
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php
    }
} else {
    ?>
    <div class="text-center py-4">
        <div class="icon icon-shape icon-md bg-gradient-secondary shadow text-center border-radius-lg mb-3">
            <i class="material-symbols-rounded opacity-10">calendar_today</i>
        </div>
        <h6 class="text-secondary mb-0">No moodlog that day</h6>
        <p class="text-sm text-secondary opacity-8">
            <?php echo date('F j, Y', strtotime($date)); ?>
        </p>
    </div>
    <?php
}
?> 