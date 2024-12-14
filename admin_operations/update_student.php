<?php
session_start();
require_once '../configs/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    // Verify SR-Code exists (only required field)
    if (!isset($_POST['srcode']) || empty($_POST['srcode'])) {
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

    // Build update query dynamically based on provided fields
    $update_fields = [];
    $params = ['srcode' => $_POST['srcode']]; // For WHERE clause

    // Fields that can be updated
    $allowed_fields = [
        'firstname', 'lastname', 'email', 'phonenum',
        'course', 'year', 'section', 'department', 'status'
    ];

    foreach ($allowed_fields as $field) {
        if (isset($_POST[$field]) && !empty($_POST[$field])) {
            // Check if email is being changed and is already in use
            if ($field === 'email' && $_POST['email'] !== $current_data['email']) {
                $check_stmt = $pdo->prepare("SELECT srcode FROM students WHERE email = ? AND srcode != ?");
                $check_stmt->execute([$_POST['email'], $_POST['srcode']]);
                if ($check_stmt->rowCount() > 0) {
                    echo json_encode(['success' => false, 'message' => 'Email is already in use by another student']);
                    exit();
                }
            }
            $update_fields[] = "$field = :$field";
            $params[$field] = $_POST[$field];
        }
    }

    // Only proceed if there are fields to update
    if (!empty($update_fields)) {
        $query = "UPDATE students SET " . implode(', ', $update_fields) . " WHERE srcode = :srcode";
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute($params);

        if ($result) {
            // Handle profile picture update if provided
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
                $upload_dir = '../uploads/profile_pictures/students/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
                $file_name = $_POST['srcode'] . '.' . $file_ext;
                
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_dir . $file_name)) {
                    // Profile picture updated successfully
                }
            }

            echo json_encode([
                'success' => true, 
                'message' => 'Student information updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to update student information'
            ]);
        }
    } else {
        // No fields to update but profile picture might have changed
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
            $upload_dir = '../uploads/profile_pictures/students/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $file_name = $_POST['srcode'] . '.' . $file_ext;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_dir . $file_name)) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Profile picture updated successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Failed to update profile picture'
                ]);
            }
        } else {
            echo json_encode([
                'success' => true, 
                'message' => 'No changes were made'
            ]);
        }
    }

} catch (PDOException $e) {
    error_log("Error updating student: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} 