<?php
session_start();
require '../configs/config.php'; // Adjust the path if necessary

// Set the appropriate content type for JSON response
header('Content-Type: application/json');

// Check if the request is a POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data) {
        $student_id = $data['student_id'];
        $emoticons = $data['emoticons'];
        $explanation = $data['explanation'];

        // Prepare SQL statement to insert the mood tracking data
        $stmt = $pdo->prepare("INSERT INTO moodtracker (student_id, emoticons, explanation) VALUES (?, ?, ?)");
        $success = $stmt->execute([$student_id, $emoticons, $explanation]);

        // Return response
        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->errorInfo()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
