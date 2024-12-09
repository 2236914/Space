<?php
// Turn off error reporting for production
error_reporting(E_ALL);
ini_set('display_errors', 1); // Temporarily enable for debugging

// Set JSON header first
header('Content-Type: application/json');

try {
    require_once '../configs/config.php';

    if (!isset($_POST['srcode'])) {
        throw new Exception('SR-Code not provided');
    }

    // Debug: Print the received SR-Code
    error_log("Received SR-Code: " . $_POST['srcode']);

    $stmt = $pdo->prepare("
        SELECT 
            srcode,
            firstname,
            lastname,
            phonenum,
            email,
            department,
            year,
            section,
            course,
            address,
            personality,
            status,
            created_date,
            username
        FROM students 
        WHERE srcode = ?
    ");
    
    if (!$stmt->execute([$_POST['srcode']])) {
        throw new Exception('Query execution failed');
    }

    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        throw new Exception('Student not found');
    }

    // Debug: Print fetched data
    error_log("Fetched student data: " . print_r($student, true));
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'data' => $student
    ]);

} catch (Exception $e) {
    error_log("Error in fetch_student.php: " . $e->getMessage());
    // Return error response
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    error_log("Database error in fetch_student.php: " . $e->getMessage());
    // Return database error response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred'
    ]);
} 