<?php
require_once __DIR__ . '/configs/config.php';
require_once __DIR__ . '/api/helpers/SMSHelper.php';

function testStatusChange($session_id, $new_status) {
    global $pdo;
    
    try {
        // Update status
        $stmt = $pdo->prepare("
            UPDATE therapy_sessions 
            SET status = ? 
            WHERE session_id = ?
        ");
        $stmt->execute([$new_status, $session_id]);
        
        // Send notification
        $result = SMSHelper::sendStatusUpdateNotification($session_id);
        
        echo "Status updated to: $new_status\n";
        echo "SMS Result: ";
        print_r($result);
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

// Test only confirm status
echo "Testing Confirm Status:\n";
testStatusChange(1, 'confirmed');