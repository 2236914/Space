<?php
session_start();

// Debug
error_log("Checking session: " . print_r($_SESSION, true));

$response = [
    'logged_in' => false,
    'message' => ''
];

// Check if user is logged in and session is valid
if (isset($_SESSION['user_id']) && isset($_SESSION['srcode'])) {
    // Add additional checks if needed
    $response['logged_in'] = true;
    $response['message'] = 'Session valid';
} else {
    $response['message'] = 'Session invalid or expired';
}

header('Content-Type: application/json');
echo json_encode($response);
?>