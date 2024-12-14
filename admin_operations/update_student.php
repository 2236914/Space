<?php
session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    if (!isset($_POST['srcode'])) {
        echo json_encode(['success' => false, 'message' => 'Student SR-Code is required']);
        exit();
    }

    // Get current student data
    $get_current = $pdo->prepare("SELECT * FROM students WHERE srcode = ?");
    $get_current->execute([$_POST['srcode']]);
    $current_data = $get_current->fetch(PDO::FETCH_ASSOC);

    if (!$current_data) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit();
    }

    $update_fields = [];
    $params = ['srcode' => $_POST['srcode']];

    // Fields that can be updated
    $allowed_fields = [
        'firstname', 'lastname', 'email', 'phonenum',
        'course', 'year', 'section', 'department', 'status',
        'address', 'personality'
    ];

    foreach ($allowed_fields as $field) {
        if (isset($_POST[$field])) {
            // Email validation
            if ($field === 'email' && $_POST['email'] !== $current_data['email']) {
                $check_stmt = $pdo->prepare("SELECT srcode FROM students WHERE email = ? AND srcode != ?");
                $check_stmt->execute([$_POST['email'], $_POST['srcode']]);
                if ($check_stmt->rowCount() > 0) {
                    echo json_encode(['success' => false, 'message' => 'Email is already in use']);
                    exit();
                }
            }

            // Phone number validation
            if ($field === 'phonenum' && !empty($_POST['phonenum'])) {
                if (!preg_match('/^09[0-9]{9}$/', $_POST['phonenum'])) {
                    echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
                    exit();
                }
            }

            // Year validation
            if ($field === 'year' && !empty($_POST['year'])) {
                if (!is_numeric($_POST['year']) || $_POST['year'] < 1 || $_POST['year'] > 5) {
                    echo json_encode(['success' => false, 'message' => 'Year must be between 1 and 5']);
                    exit();
                }
            }

            $update_fields[] = "$field = :$field";
            $params[$field] = $_POST[$field];
        }
    }

    // Start transaction
    $pdo->beginTransaction();

    // Update student information if there are fields to update
    if (!empty($update_fields)) {
        $query = "UPDATE students SET " . implode(', ', $update_fields) . " WHERE srcode = :srcode";
        $stmt = $pdo->prepare($query);
        if (!$stmt->execute($params)) {
            throw new PDOException("Failed to update student information");
        }
    }

    // Handle profile picture
    $profile_updated = false;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $file_type = $_FILES['profile_picture']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception('Invalid file type. Only JPG, JPEG, and PNG are allowed.');
        }

        // Read image file
        $image_data = file_get_contents($_FILES['profile_picture']['tmp_name']);
        
        // Check if profile picture already exists
        $check_stmt = $pdo->prepare("SELECT id FROM profile_pictures WHERE user_id = ? AND user_type = 'student'");
        $check_stmt->execute([$_POST['srcode']]);
        
        if ($check_stmt->rowCount() > 0) {
            // Update existing profile picture
            $update_stmt = $pdo->prepare("UPDATE profile_pictures SET 
                image_data = ?, 
                mime_type = ?, 
                upload_date = CURRENT_TIMESTAMP 
                WHERE user_id = ? AND user_type = 'student'");
            $profile_updated = $update_stmt->execute([$image_data, $file_type, $_POST['srcode']]);
        } else {
            // Insert new profile picture
            $insert_stmt = $pdo->prepare("INSERT INTO profile_pictures 
                (user_id, user_type, image_data, mime_type) 
                VALUES (?, 'student', ?, ?)");
            $profile_updated = $insert_stmt->execute([$_POST['srcode'], $image_data, $file_type]);
        }

        if (!$profile_updated) {
            throw new PDOException("Failed to update profile picture");
        }
    }

    // Commit transaction
    $pdo->commit();

    // Success response
    $message = [];
    if (!empty($update_fields)) $message[] = 'Student information updated';
    if ($profile_updated) $message[] = 'Profile picture updated';
    
    echo json_encode([
        'success' => true,
        'message' => !empty($message) ? implode(' and ', $message) . ' successfully' : 'No changes were made'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Error updating student: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 