<?php
session_start();
require_once '../configs/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

try {
    // Fetch all students
    $query = "
        SELECT 
            s.srcode,
            s.firstname,
            s.lastname,
            s.email,
            s.phonenum,
            s.course,
            s.year,
            s.section,
            s.department,
            CONCAT(s.year, '-', s.section) as year_section,
            s.status,
            DATE_FORMAT(s.created_date, '%d/%m/%y') as created_date
        FROM 
            students s
        ORDER BY 
            s.created_date DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add this debugging
    error_log('Students data: ' . print_r($students, true));
    
    $response = [
        'draw' => isset($_GET['draw']) ? intval($_GET['draw']) : 1,
        'recordsTotal' => count($students),
        'recordsFiltered' => count($students),
        'data' => $students
    ];

    // Add this debugging
    error_log('Response data: ' . print_r($response, true));

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Failed to fetch students: ' . $e->getMessage(),
        'data' => []
    ]);
}
?> 