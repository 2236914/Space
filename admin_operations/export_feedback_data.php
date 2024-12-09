<?php
session_start();
require_once '../configs/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_feedback_csv'])) {
    try {
        $srcode = $_POST['srcode'];
        
        // Fetch feedback data
        $stmt = $pdo->prepare("
            SELECT 
                ts.session_date,
                sf.diagnosis,
                sf.recommendations,
                sf.follow_up,
                sf.follow_up_notes,
                sf.created_at
            FROM session_feedback sf
            JOIN therapy_sessions ts ON sf.session_id = ts.session_id
            WHERE ts.srcode = ?
            ORDER BY ts.session_date DESC
        ");
        $stmt->execute([$srcode]);
        $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="session_feedback_' . date('Y-m-d') . '.csv"');

        // Create output stream
        $output = fopen('php://output', 'w');

        // Add CSV headers
        fputcsv($output, [
            'Session Date',
            'Diagnosis',
            'Recommendations',
            'Follow-up Required',
            'Follow-up Notes',
            'Feedback Date'
        ]);

        // Add data rows
        foreach ($feedbacks as $feedback) {
            fputcsv($output, [
                date('M d, Y', strtotime($feedback['session_date'])),
                $feedback['diagnosis'],
                $feedback['recommendations'],
                $feedback['follow_up'],
                $feedback['follow_up_notes'],
                date('M d, Y h:i A', strtotime($feedback['created_at']))
            ]);
        }

        fclose($output);
        exit();

    } catch (PDOException $e) {
        error_log("Error exporting feedback data: " . $e->getMessage());
        $_SESSION['error'] = "Failed to export data. Please try again.";
        header("Location: ../pages/student/generate-reports.php");
        exit();
    }
}

header("Location: ../pages/student/generate-reports.php");
exit(); 