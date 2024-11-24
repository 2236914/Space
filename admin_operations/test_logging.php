<?php
require_once '../configs/config.php';
require_once 'SessionLogger.php';

try {
    $logger = new SessionLogger($pdo);
    
    $result = $logger->logActivity(
        'TEST123',  // srcode
        null,       // therapist_id
        null,       // admin_id
        'TEST_LOG', // action
        'Test log entry', // description
        '127.0.0.1' // ip_address
    );
    
    if ($result) {
        echo "Test log entry created successfully\n";
    } else {
        echo "Failed to create test log entry\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 