<?php
session_start();
require_once '../configs/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing application ID']);
    exit();
}

try {
    // Log the incoming request
    error_log("Fetching application ID: " . $_GET['id']);

    $query = "SELECT id, first_name, last_name, email, phone, 
                     license_number, specialization, experience,
                     license_file_type, resume_file_type,
                     status, application_date, review_date, review_notes 
              FROM therapist_applications 
              WHERE id = ?";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$_GET['id']]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    // Log the query result
    error_log("Query result: " . print_r($application, true));

    if (!$application) {
        throw new Exception('Application not found');
    }

    // Format dates
    $application['application_date'] = date('Y-m-d H:i:s', strtotime($application['application_date']));
    if ($application['review_date']) {
        $application['review_date'] = date('Y-m-d H:i:s', strtotime($application['review_date']));
    }

    echo json_encode($application);

} catch (Exception $e) {
    error_log("Error in get_application_details: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}