<?php
require_once '../configs/config.php';

header('Content-Type: application/json');

try {
    $phonenum = $_POST['phonenum'] ?? '';
    $email = $_POST['email'] ?? '';
    $currentUserId = $_POST['srcode'] ?? '';

    // Check phone number
    $stmt = $pdo->prepare("SELECT srcode FROM students WHERE phonenum = ? AND srcode != ?");
    $stmt->execute([$phonenum, $currentUserId]);
    if ($stmt->fetch()) {
        echo json_encode([
            'error' => true,
            'message' => 'This phone number is already registered to another account.'
        ]);
        exit;
    }

    // Check email
    $stmt = $pdo->prepare("SELECT srcode FROM students WHERE email = ? AND srcode != ?");
    $stmt->execute([$email, $currentUserId]);
    if ($stmt->fetch()) {
        echo json_encode([
            'error' => true,
            'message' => 'This email is already registered to another account.'
        ]);
        exit;
    }

    // If no duplicates found
    echo json_encode(['error' => false]);

} catch (PDOException $e) {
    echo json_encode([
        'error' => true,
        'message' => 'Database error occurred. Please try again.'
    ]);
}