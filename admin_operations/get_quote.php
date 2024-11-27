<?php
require_once '../configs/config.php';

try {
    // Get a random quote from the database
    $stmt = $pdo->query("SELECT * FROM quotes ORDER BY RAND() LIMIT 1");
    $quote = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($quote) {
        echo json_encode([
            'success' => true,
            'content' => $quote['content'],
            'author' => $quote['author']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'No quotes found in database'
        ]);
    }
} catch (PDOException $e) {
    error_log("Quote fetch error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
}
?>
