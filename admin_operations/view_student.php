<?php
session_start();
require_once '../configs/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (!isset($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Student ID not provided']);
    exit();
}

try {
    // Add error logging
    error_log("Attempting to fetch student with ID: " . $_GET['id']);
    
    $query = "
        SELECT 
            s.srcode,
            s.firstname,
            s.lastname,
            s.phonenum,
            s.email,
            s.department,
            s.year,
            s.section,
            s.course,
            CONCAT(s.year, '-', s.section) as year_section,
            s.status,
            DATE_FORMAT(s.created_date, '%d/%m/%y') as created_date
        FROM 
            students s
        WHERE 
            s.srcode = :srcode
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['srcode' => $_GET['id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $student]);
    } else {
        error_log("No student found with ID: " . $_GET['id']);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Student not found',
            'debug' => 'Searched for srcode: ' . $_GET['id']
        ]);
    }

} catch (PDOException $e) {
    error_log("Error fetching student: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'debug' => $e->getMessage()
    ]);
}
?> 