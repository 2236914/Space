<?php
session_start();
require_once '../configs/config.php';
require_once 'profile_operations.php';

if (isset($_GET['user_id']) && isset($_GET['user_type'])) {
    try {
        $profileOps = new ProfileOperations($pdo);
        
        // Convert role to user_type if needed
        $user_type = $_GET['user_type'];
        if ($user_type === 'superadmin' || $user_type === 'moderator') {
            $user_type = 'admin';
        }

        $picture = $profileOps->getProfilePicture($_GET['user_id'], $user_type);
        
        if ($picture) {
            header("Content-Type: " . $picture['file_type']);
            echo $picture['file_data'];
        } else {
            // Return default image
            $default_image_path = '../assets/img/default-avatar.jpg';
            header("Content-Type: image/jpeg");
            echo file_get_contents($default_image_path);
        }
    } catch (Exception $e) {
        error_log("Error fetching profile picture: " . $e->getMessage());
        // Return default image on error
        $default_image_path = '../assets/img/default-avatar.jpg';
        header("Content-Type: image/jpeg");
        echo file_get_contents($default_image_path);
    }
    exit;
}
?>