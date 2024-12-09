<?php
session_start();
require_once '../configs/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
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
            s.phonenum as contact_number,
            s.course,
            s.year,
            s.section,
            s.department,
            s.status,
            DATE_FORMAT(s.created_date, '%Y-%m-%d') as created_date
        FROM 
            students s
        ORDER BY 
            s.created_date DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_' . date('Y-m-d') . '.csv"');

    // Create output stream
    $output = fopen('php://output', 'w');

    // Add UTF-8 BOM for proper Excel encoding
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Add headers
    fputcsv($output, [
        'SR-Code',
        'First Name',
        'Last Name',
        'Email',
        'Contact Number',
        'Course',
        'Year',
        'Section',
        'Department',
        'Status',
        'Created Date'
    ]);

    // Add data
    foreach ($students as $student) {
        fputcsv($output, [
            $student['srcode'],
            $student['firstname'],
            $student['lastname'],
            $student['email'],
            $student['contact_number'],
            $student['course'],
            $student['year'],
            $student['section'],
            $student['department'],
            $student['status'],
            $student['created_date']
        ]);
    }

    fclose($output);
    exit();

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to export students: ' . $e->getMessage()
    ]);
    exit();
}
?> 