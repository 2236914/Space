<?php
// Add this test line
error_log("Test log entry - " . date('Y-m-d H:i:s'));

// Enable detailed error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log errors to file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Ensure we're sending JSON
header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sendJsonResponse($status, $message, $data = null) {
    try {
        $response = json_encode([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ]);
        
        if ($response === false) {
            throw new Exception('JSON encode failed: ' . json_last_error_msg());
        }
        
        echo $response;
    } catch (Exception $e) {
        error_log("JSON Response Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Internal server error'
        ]);
    }
    exit;
}

function logActivity($pdo, $user_id, $action, $details) {
    try {
        $query = "INSERT INTO activity_logs (srcode, action, action_details, created_at) 
                 VALUES (:srcode, :action, :details, NOW())";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'srcode' => $user_id,
            'action' => $action,
            'details' => $details
        ]);
    } catch (PDOException $e) {
        error_log("Error logging activity: " . $e->getMessage());
    }
}

try {
    // Load required files
    require_once(__DIR__ . '/../configs/config.php');
    require_once 'SessionLogger.php';
    require_once 'error_logger.php';

    // Log incoming request
    error_log("Received request: " . print_r($_POST, true));
    error_log("Session data: " . print_r($_SESSION, true));

    // Create PDO instance
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Initialize logger
    $logger = new ErrorLogger($pdo);
    
    // Log connection success
    $logger->logDebug('Database connection established');

    // Verify user authentication
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Validate input
    if (empty($_POST['selected_emoji']) || empty($_POST['mood_name']) || empty($_POST['description'])) {
        throw new Exception('Missing required fields');
    }

    // Begin transaction
    $pdo->beginTransaction();

    try {
        // Generate ID
        $stmt = $pdo->query("SELECT COALESCE(MAX(CAST(SUBSTRING(moodlog_id, 6) AS UNSIGNED)), 0) as max_id FROM moodlog");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_id = $result['max_id'] + 1;
        $moodlog_id = sprintf("MOOD-%04d", $next_id);

        // Log before insert
        $logger->logDebug('Preparing insert', [
            'moodlog_id' => $moodlog_id,
            'data' => $_POST
        ]);

        // Insert mood
        $stmt = $pdo->prepare("
            INSERT INTO moodlog 
            (moodlog_id, srcode, selected_emoji, mood_name, description) 
            VALUES (?, ?, ?, ?, ?)
        ");

        $success = $stmt->execute([
            $moodlog_id,
            $_SESSION['user_id'],
            $_POST['selected_emoji'],
            $_POST['mood_name'],
            $_POST['description']
        ]);

        if (!$success) {
            throw new Exception('Insert failed: ' . implode(', ', $stmt->errorInfo()));
        }

        // Log success
        $logger->logInfo('Insert successful', [
            'moodlog_id' => $moodlog_id,
            'rows_affected' => $stmt->rowCount()
        ]);

        // Commit transaction
        $pdo->commit();

        logActivity($pdo, $_SESSION['user_id'], 'Mood Log', 'Added new mood entry: ' . $_POST['mood_name']);

        sendJsonResponse('success', 'Mood logged successfully', ['moodlog_id' => $moodlog_id]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error in save_mood.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    if (isset($logger)) {
        $logger->logError($e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'post' => $_POST
        ]);
    }
    
    sendJsonResponse('error', $e->getMessage());
}
?>