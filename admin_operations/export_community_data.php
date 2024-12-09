<?php
session_start();
require_once '../configs/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_community_csv'])) {
    try {
        $user_id = $_POST['user_id'];
        
        // Fetch community data
        $stmt = $pdo->prepare("
            SELECT 
                created_at,
                type,
                content,
                status,
                report_count,
                (SELECT COUNT(*) FROM likes WHERE post_id = community_activities.id) as like_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = community_activities.id) as comment_count
            FROM community_activities 
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user_id]);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="community_report_' . date('Y-m-d') . '.csv"');

        // Create output stream
        $output = fopen('php://output', 'w');

        // Add CSV headers
        fputcsv($output, [
            'Date',
            'Activity Type',
            'Content',
            'Status',
            'Reports',
            'Likes Received',
            'Comments Received'
        ]);

        // Add data rows
        foreach ($activities as $activity) {
            fputcsv($output, [
                date('M d, Y h:i A', strtotime($activity['created_at'])),
                ucfirst($activity['type']),
                $activity['content'],
                ucfirst($activity['status']),
                $activity['report_count'] > 0 ? $activity['report_count'] . ' reports' : 'No reports',
                $activity['like_count'],
                $activity['comment_count']
            ]);
        }

        fclose($output);
        exit();

    } catch (PDOException $e) {
        error_log("Error exporting community data: " . $e->getMessage());
        $_SESSION['error'] = "Failed to export data. Please try again.";
        header("Location: ../pages/student/generate-reports.php");
        exit();
    }
}

header("Location: ../pages/student/generate-reports.php");
exit(); 