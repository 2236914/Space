<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

try {
    // Debug logging
    error_log("UPDATE THERAPIST REQUEST");
    error_log("POST: " . print_r($_POST, true));
    error_log("FILES: " . print_r($_FILES, true));

    // Check if therapist_id exists
    if (!isset($_POST['therapist_id'])) {
        throw new Exception('Therapist ID is required');
    }

    $therapist_id = $_POST['therapist_id'];

    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception('Invalid file type. Only JPG and PNG are allowed.');
        }

        // Read file content
        $image_data = file_get_contents($file['tmp_name']);
        if ($image_data === false) {
            throw new Exception('Failed to read image file');
        }

        // Begin transaction
        $pdo->beginTransaction();

        try {
            // First, delete existing profile picture if any
            $delete_stmt = $pdo->prepare("
                DELETE FROM profile_pictures 
                WHERE user_id = ? AND user_type = 'therapist'
            ");
            $delete_stmt->execute([$therapist_id]);

            // Insert new profile picture
            $insert_stmt = $pdo->prepare("
                INSERT INTO profile_pictures 
                (user_id, user_type, image_data, mime_type) 
                VALUES (?, 'therapist', ?, ?)
            ");

            $result = $insert_stmt->execute([
                $therapist_id,
                $image_data,
                $file['type']
            ]);

            if (!$result) {
                throw new Exception('Failed to save profile picture');
            }

            // Commit transaction
            $pdo->commit();
            
            error_log("Profile picture updated successfully for therapist: " . $therapist_id);
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // If password is provided, update it
    if (!empty($_POST['password'])) {
        $hashedPassword = hash('sha256', $_POST['password']);
        $updatePassword = ", password = ?";
        $params[] = $hashedPassword;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Therapist updated successfully'
    ]);

} catch (Exception $e) {
    error_log("Error in update_therapist.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 