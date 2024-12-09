<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set up error logging with timestamp and detailed information
$logFile = __DIR__ . '/upload_errors.log';

// Create log file if it doesn't exist
if (!file_exists($logFile)) {
    touch($logFile);
    chmod($logFile, 0666);
}

// Enable comprehensive error logging
ini_set('log_errors', 1);
ini_set('error_log', $logFile);
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set error handler to catch all errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log(sprintf(
        "Error [%s]: %s\nFile: %s\nLine: %s\n",
        $errno,
        $errstr,
        $errfile,
        $errline
    ));
    return false;
});

// Set exception handler
set_exception_handler(function($e) {
    error_log(sprintf(
        "Uncaught Exception: %s\nFile: %s\nLine: %s\nTrace:\n%s\n",
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    ));
});

// Clear any previous output
if (ob_get_level()) ob_end_clean();

// Set JSON header
header('Content-Type: application/json');

// Log start of request with detailed information
error_log("\n=== New Upload Request (" . date('Y-m-d H:i:s') . ") ===");
error_log("Remote IP: " . $_SERVER['REMOTE_ADDR']);
error_log("User Agent: " . $_SERVER['HTTP_USER_AGENT']);
error_log("Session Data: " . print_r($_SESSION, true));
error_log("POST Data: " . print_r($_POST, true));
error_log("FILES Data: " . print_r($_FILES, true));

try {
    error_log("Loading required files...");
    require_once __DIR__ . '/../configs/config.php';
    require_once __DIR__ . '/profile_operations.php';
    require_once __DIR__ . '/SessionLogger.php';
    error_log("Required files loaded successfully");

    // Verify session
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        error_log("Session validation failed. Session data: " . print_r($_SESSION, true));
        throw new Exception('User not authenticated');
    }
    error_log("Session validated successfully");

    // Verify file upload
    if (!isset($_FILES['profile_picture'])) {
        error_log("No file uploaded. FILES array: " . print_r($_FILES, true));
        throw new Exception('No file uploaded');
    }

    $file = $_FILES['profile_picture'];
    error_log("File details: " . print_r($file, true));

    // Validate upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("File upload error code: " . $file['error']);
        throw new Exception('File upload failed with error code: ' . $file['error']);
    }

    // Read file
    error_log("Reading file data...");
    $fileData = file_get_contents($file['tmp_name']);
    if ($fileData === false) {
        error_log("Failed to read file: " . $file['tmp_name']);
        throw new Exception('Failed to read uploaded file');
    }
    error_log("File data read successfully. Size: " . strlen($fileData) . " bytes");

    // Database operations
    error_log("Starting database transaction...");
    $pdo->beginTransaction();

    try {
        // Simple REPLACE INTO query to update/insert the profile picture
        error_log("Updating profile picture...");
        $stmt = $pdo->prepare("
            REPLACE INTO profile_pictures 
            (user_id, user_type, image_data, mime_type) 
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_SESSION['user_id'],
            $_SESSION['role'],
            $fileData,
            $file['type']
        ]);
        error_log("Profile picture updated successfully");

        $pdo->commit();
        
        $response = [
            'status' => 'success',
            'message' => 'Profile picture updated successfully'
        ];
        echo json_encode($response);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => 'Failed to update profile picture: ' . $e->getMessage()
    ];
    echo json_encode($response);
}

error_log("=== Request Complete (" . date('Y-m-d H:i:s') . ") ===\n");
exit;