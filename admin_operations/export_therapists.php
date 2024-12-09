<?php
session_start();
require_once '../configs/config.php';

try {
    $stmt = $pdo->query("
        SELECT 
            therapist_id,
            firstname,
            lastname,
            email,
            specialization,
            license_number,
            contact_number,
            status,
            created_date
        FROM therapists 
        ORDER BY created_date DESC
    ");
    
    $therapists = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="therapists_' . date('Y-m-d') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add headers
    fputcsv($output, array_keys($therapists[0]));
    
    // Add data
    foreach ($therapists as $therapist) {
        fputcsv($output, $therapist);
    }
    
    fclose($output);
    
} catch (PDOException $e) {
    error_log("Error exporting therapists: " . $e->getMessage());
    die("Failed to export data");
} 