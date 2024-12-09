<?php
session_start();
require_once '../configs/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    exit('Unauthorized');
}

if (!isset($_GET['id']) || !isset($_GET['type'])) {
    http_response_code(400);
    exit('Missing parameters');
}

try {
    $id = $_GET['id'];
    $type = $_GET['type'];
    $download = isset($_GET['download']) && $_GET['download'] === 'true';

    // Validate file type
    if (!in_array($type, ['license', 'resume'])) {
        throw new Exception('Invalid file type');
    }

    $query = "SELECT {$type}_file, {$type}_file_type FROM therapist_applications WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result || !$result["{$type}_file"]) {
        throw new Exception('File not found');
    }

    $file_content = $result["{$type}_file"];
    $file_type = $result["{$type}_file_type"];
    $file_extension = pathinfo($file_type, PATHINFO_EXTENSION) ?: 'pdf';
    $file_name = $type . '.' . $file_extension;

    // Set headers based on whether it's a download or view
    header('Content-Type: ' . $file_type);
    if ($download) {
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
    } else {
        header('Content-Disposition: inline; filename="' . $file_name . '"');
    }

    // Output file content
    echo $file_content;
    exit();

} catch (Exception $e) {
    error_log('File error: ' . $e->getMessage());
    http_response_code(500);
    exit('Error: ' . $e->getMessage());
} 