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
    // Prepare the SQL query
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
            s.status != 'deleted'
        ORDER BY 
            s.created_date DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the response for DataTables
    $response = [
        'data' => $students
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Error fetching students: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Database error occurred',
        'message' => $e->getMessage()
    ]);
}
?> 