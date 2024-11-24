<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../configs/config.php';
require_once 'profile_operations.php';
require_once 'SessionLogger.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $response = ['status' => 'error', 'message' => ''];
    
    try {
        $profileOps = new ProfileOperations($pdo);
        $sessionLogger = new SessionLogger($pdo);
        $file = $_FILES['profile_picture'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        
        // Get file info
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedType = finfo_file($fileInfo, $file['tmp_name']);
        finfo_close($fileInfo);
        
        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($detectedType, $allowedTypes) || !in_array($extension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Only JPG, JPEG, and PNG files are allowed.');
        }
        
        // Validate file size (5MB max)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new Exception('File size too large. Maximum size is 5MB.');
        }
        
        // Read file data
        $fileData = file_get_contents($file['tmp_name']);
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Deactivate old profile picture
        $stmt = $pdo->prepare("
            UPDATE profile_pictures 
            SET status = 'inactive' 
            WHERE user_id = ? AND user_type = ? AND status = 'active'
        ");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['role']]);
        
        // Insert new profile picture
        $stmt = $pdo->prepare("
            INSERT INTO profile_pictures 
            (user_id, user_type, file_name, file_type, file_data) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $_SESSION['role'],
            $file['name'],
            $file['type'],
            $fileData
        ]);
        
        // Log the activity
        $user_id = $_SESSION['user_id'];
        $user_type = $_SESSION['role'];
        
        $srcode = ($user_type === 'student') ? $user_id : null;
        $therapist_id = ($user_type === 'therapist') ? $user_id : null;
        $admin_id = ($user_type === 'admin') ? $user_id : null;
        
        $sessionLogger->logActivity(
            $srcode,
            $therapist_id, 
            $admin_id,
            'UPDATE_PROFILE_PICTURE',
            'Updated profile picture: ' . $file['name'],
            $_SERVER['REMOTE_ADDR']
        );
        
        $pdo->commit();
        $response['status'] = 'success';
        $response['message'] = 'Profile picture updated successfully';
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $response['message'] = $e->getMessage();
        error_log("Profile picture upload error: " . $e->getMessage());
    }
    
    echo json_encode($response);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
exit;
?>