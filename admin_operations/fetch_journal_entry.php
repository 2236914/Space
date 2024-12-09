<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

if (ob_get_length()) ob_clean();
ob_start();

if (!isset($_SESSION['srcode']) || !isset($_GET['entry_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    ob_end_flush();
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            entry_id,
            srcode,
            title,
            content,
            mood,
            DATE(entry_date) as entry_date,
            created_at
        FROM journal_entries 
        WHERE entry_id = :entry_id 
        AND srcode = :srcode
        LIMIT 1
    ");
    
    $stmt->execute([
        'entry_id' => (int)$_GET['entry_id'],
        'srcode' => (int)$_SESSION['srcode']
    ]);
    
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($entry) {
        $response = [
            'success' => true,
            'entry' => [
                'title' => htmlspecialchars($entry['title']),
                'content' => htmlspecialchars($entry['content']),
                'mood' => ucfirst($entry['mood']),
                'entry_date' => $entry['entry_date'],
                'created_at' => $entry['created_at']
            ]
        ];
    } else {
        $response = ['success' => false, 'message' => 'Entry not found'];
    }
    
} catch (PDOException $e) {
    error_log("Error fetching journal entry: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
}

ob_clean();
echo json_encode($response);
ob_end_flush();
exit; 