<?php
require_once '../configs/config.php';

if (isset($_GET['post_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT image_file, image_name FROM posts WHERE post_id = ?");
        $stmt->execute([$_GET['post_id']]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($post && $post['image_file']) {
            header("Content-Type: image/jpeg");
            echo $post['image_file'];
            exit;
        }
    } catch (PDOException $e) {
        error_log("Error retrieving post image: " . $e->getMessage());
    }
}

// Return default image if post image not found
header("Location: ../assets/img/default-post-image.jpg");
?> 