<?php
require_once '../configs/config.php';

if (isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT attachment_data, attachment_name, attachment_type 
            FROM support_messages 
            WHERE id = ?
        ");
        $stmt->execute([$_GET['id']]);
        $attachment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($attachment && $attachment['attachment_data']) {
            header('Content-Type: ' . $attachment['attachment_type']);
            header('Content-Disposition: attachment; filename="' . $attachment['attachment_name'] . '"');
            echo $attachment['attachment_data'];
            exit;
        }
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Failed to retrieve attachment']);
    }
}

header('HTTP/1.1 404 Not Found');
echo json_encode(['error' => 'Attachment not found']);