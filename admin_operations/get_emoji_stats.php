<?php
session_start();
require_once '../configs/config.php';

try {
    $stmt = $pdo->prepare("
        SELECT 
            emoji_used,
            COUNT(*) as count,
            (COUNT(*) * 100.0 / (
                SELECT COUNT(*) 
                FROM moodlog 
                WHERE srcode = ?
            )) as percentage
        FROM moodlog 
        WHERE srcode = ?
        GROUP BY emoji_used
        ORDER BY count DESC
    ");
    
    $stmt->execute([$_SESSION['srcode'], $_SESSION['srcode']]);
    $emojiStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $values = [];

    foreach ($emojiStats as $stat) {
        $labels[] = $stat['emoji_used'];
        $values[] = round($stat['percentage'], 1); // Round to 1 decimal place
    }

    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'values' => $values
    ]);

} catch (PDOException $e) {
    error_log("Error fetching emoji stats: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch emoji statistics'
    ]);
}
?> 