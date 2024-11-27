<?php
header('Content-Type: application/json');
session_start();

require_once '../configs/config.php';
require_once 'SessionLogger.php';

try {
    // Begin transaction
    $conn->beginTransaction();

    // Get the current data to compare changes
    $stmt = $conn->prepare("SELECT * FROM students WHERE srcode = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $oldData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debug: Log received data
    error_log("POST Data received: " . print_r($_POST, true));
    error_log("Current user ID: " . $_SESSION['user_id']);

    // Initialize arrays for query building
    $updateFields = [];
    $params = [];
    $changes = [];

    // Fields that can be updated
    $allowedFields = [
        'firstname', 'lastname', 'email', 'phonenum', 
        'course', 'year', 'section', 'department', 
        'personality', 'address'
    ];

    // Build update query dynamically
    foreach ($allowedFields as $field) {
        // Check if the field exists in POST data (even if empty)
        if (isset($_POST[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $_POST[$field];
            
            // Track changes
            if ($oldData[$field] !== $_POST[$field]) {
                $changes[] = "$field: {$oldData[$field]} → {$_POST[$field]}";
            }
        }
    }

    // Debug: Log query components
    error_log("Update Fields: " . print_r($updateFields, true));
    error_log("Parameters: " . print_r($params, true));

    if (empty($updateFields)) {
        throw new Exception('No fields to update');
    }

    // Add srcode to params for WHERE clause
    $params[] = $_SESSION['user_id'];

    // Construct and execute update query
    $sql = "UPDATE students SET " . implode(', ', $updateFields) . " WHERE srcode = ?";
    error_log("SQL Query: " . $sql);

    $updateStmt = $conn->prepare($sql);
    $success = $updateStmt->execute($params);

    if (!$success) {
        error_log("Update Error: " . print_r($updateStmt->errorInfo(), true));
        throw new Exception('Failed to update profile');
    }

    // Log the activity if there were changes
    if (!empty($changes)) {
        $sessionLogger = new SessionLogger($conn);
        $action = "Profile Update";
        $action_details = "Updated profile fields: " . implode(', ', $changes);

        $logSuccess = $sessionLogger->logActivity(
            $_SESSION['user_id'],
            null,
            null,
            $action,
            $action_details,
            $_SERVER['REMOTE_ADDR']
        );

        if (!$logSuccess) {
            throw new Exception('Failed to log activity');
        }
    }

    // Commit transaction
    $conn->commit();

    // Debug: Log success
    error_log("Profile update successful. Changes: " . print_r($changes, true));

    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'changes' => $changes
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log("Profile update error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 