<?php
session_start();
require_once '../configs/config.php';

$response = ['success' => false];

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    $response['message'] = 'You must be logged in to post';
    echo json_encode($response);
    exit();
}

if ($_POST['action'] === 'create_post') {
    try {
        $content = trim($_POST['content']);
        
        if (empty($content)) {
            throw new Exception('Post content cannot be empty');
        }
        
        $image_data = null;
        $image_name = null;
        
        // Handle optional image upload directly to database
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception('Invalid file type. Only JPG, PNG and GIF are allowed.');
            }
            
            // Check file size (5MB limit)
            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                throw new Exception('File is too large. Maximum size is 5MB.');
            }
            
            // Read image file content
            $image_data = file_get_contents($_FILES['image']['tmp_name']);
            $image_name = $_FILES['image']['name'];
        }
        
        // Create the post with BLOB data
        $stmt = $pdo->prepare("
            INSERT INTO posts (username, content, image_file, image_name, post_type)
            VALUES (?, ?, ?, ?, 'student')
        ");
        
        $stmt->execute([
            $_SESSION['username'],
            $content,
            $image_data,
            $image_name
        ]);
        
        $response['success'] = true;
        $response['message'] = 'Post created successfully';
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
