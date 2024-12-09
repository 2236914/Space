<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

try {
    // Debug logging
    error_log("ADD THERAPIST REQUEST");
    error_log("POST: " . print_r($_POST, true));
    error_log("FILES: " . print_r($_FILES, true));

    $pdo->beginTransaction();

    // Hash password using sha256 to match login check
    $hashedPassword = hash('sha256', $_POST['password']);

    // First insert the therapist details
    $stmt = $pdo->prepare("
        INSERT INTO therapists (
            firstname, lastname, email, contact_number, 
            dob, specialization, license_number, password,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");

    $result = $stmt->execute([
        $_POST['firstname'],
        $_POST['lastname'],
        $_POST['email'],
        $_POST['contact_number'],
        $_POST['dob'],
        $_POST['specialization'],
        $_POST['license_number'],
        $hashedPassword  // Use sha256 hashed password
    ]);

    if (!$result) {
        throw new Exception('Failed to add therapist');
    }

    // Get the newly inserted therapist_id
    $therapist_id = $pdo->lastInsertId();

    // Handle profile picture if uploaded
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        $image_data = file_get_contents($file['tmp_name']);

        $stmt = $pdo->prepare("
            INSERT INTO profile_pictures (
                user_id, user_type, image_data, mime_type
            ) VALUES (?, 'therapist', ?, ?)
        ");

        $result = $stmt->execute([
            $therapist_id,
            $image_data,
            $file['type']
        ]);

        if (!$result) {
            throw new Exception('Failed to save profile picture');
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Therapist added successfully'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error in add_therapist.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}