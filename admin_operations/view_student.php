<?php
session_start();
require_once '../configs/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Student ID is required']);
    exit();
}

try {
    $srcode = $_GET['id'];
    
    // Fetch student details
    $query = "
        SELECT 
            s.srcode,
            s.firstname,
            s.lastname,
            s.email,
            s.phonenum as contact_number,
            s.course,
            s.year,
            s.section,
            s.department,
            s.status,
            DATE_FORMAT(s.created_date, '%d/%m/%y') as created_date
        FROM 
            students s
        WHERE 
            s.srcode = :srcode
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['srcode' => $srcode]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        echo json_encode([
            'success' => true,
            'data' => $student
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Student not found'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch student details: ' . $e->getMessage()
    ]);
}
?> 