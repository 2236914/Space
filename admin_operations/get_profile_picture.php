<?php
require_once '../configs/config.php';

try {
    if (!isset($_GET['user_id']) || !isset($_GET['user_type'])) {
        throw new Exception('User ID and type are required');
    }

    $stmt = $pdo->prepare("
        SELECT image_data, mime_type 
        FROM profile_pictures 
        WHERE user_id = ? 
        AND user_type = ? 
        AND status = 'active'
    ");

    $stmt->execute([$_GET['user_id'], $_GET['user_type']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        header("Content-Type: " . $result['mime_type']);
        echo $result['image_data'];
    } else {
        // Return default image path if no profile picture found
        header("Location: ../assets/img/default-avatar.png");
    }
} catch (Exception $e) {
    error_log("Error in get_profile_picture.php: " . $e->getMessage());
    header("Location: ../assets/img/default-avatar.png");
}
?>