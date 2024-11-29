<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../configs/config.php';

try {
    // Get parameters
    $user_id = $_GET['user_id'] ?? null;
    $user_type = $_GET['user_type'] ?? null;

    if (!$user_id || !$user_type) {
        throw new Exception('Missing parameters');
    }

    // Get the most recent active profile picture
    $stmt = $pdo->prepare("
        SELECT image_data, mime_type 
        FROM profile_pictures 
        WHERE user_id = ? 
        AND user_type = ? 
        AND status = 'active' 
        ORDER BY upload_date DESC 
        LIMIT 1
    ");
    
    $stmt->execute([$user_id, $user_type]);
    $picture = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($picture) {
        // Set proper content type header
        header('Content-Type: ' . $picture['mime_type']);
        // Output image data
        echo $picture['image_data'];
    } else {
        // If no profile picture found, redirect to default avatar
        header('Location: ../assets/img/default-avatar.png');
    }

} catch (Exception $e) {
    error_log("Profile picture retrieval error: " . $e->getMessage());
    // Redirect to default avatar on error
    header('Location: ../assets/img/default-avatar.png');
}
?>