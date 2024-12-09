<?php
session_start();
require_once '../configs/config.php';

// Check if user is logged in and request is valid
if (!isset($_SESSION['srcode']) || !isset($_POST['export_journal_csv'])) {
    header("Location: ../pages/student/generate-reports.php");
    exit();
}

try {
    // Fetch journal entries for the user
    $stmt = $pdo->prepare("
        SELECT 
            title,
            content,
            mood,
            DATE_FORMAT(entry_date, '%M %d, %Y') as formatted_date,
            DATE_FORMAT(created_at, '%M %d, %Y %h:%i %p') as created_date
        FROM journal_entries 
        WHERE srcode = :srcode 
        ORDER BY entry_date DESC, created_at DESC
    ");
    
    $stmt->execute(['srcode' => $_SESSION['srcode']]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="journal_entries_' . date('Y-m-d') . '.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for proper Excel encoding
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add CSV headers
    fputcsv($output, [
        'Entry Date',
        'Title',
        'Content',
        'Mood',
        'Created At'
    ]);
    
    // Add data rows
    foreach ($entries as $entry) {
        fputcsv($output, [
            $entry['formatted_date'],
            $entry['title'],
            $entry['content'],
            ucfirst($entry['mood']),
            $entry['created_date']
        ]);
    }
    
    // Close the output stream
    fclose($output);
    exit();

} catch (PDOException $e) {
    error_log("Error exporting journal entries: " . $e->getMessage());
    $_SESSION['error'] = "Failed to export journal entries.";
    header("Location: ../pages/student/generate-reports.php");
    exit();
} 