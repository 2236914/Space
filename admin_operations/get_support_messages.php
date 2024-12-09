<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    require_once '../configs/config.php';
    session_start();

    if (!isset($pdo)) {
        throw new Exception('Database connection not established');
    }

    if (!isset($_SESSION['srcode'])) {
        throw new Exception('User not authenticated');
    }

    $srcode = $_SESSION['srcode'];
    
    $query = "
        SELECT 
            m.id,
            m.srcode,
            m.email,
            m.message as message_text,
            m.attachment_name,
            m.attachment_type,
            m.status,
            m.created_at,
            m.reply_count,
            r.reply_id,
            r.reply_text,
            r.attachment_name as reply_attachment_name,
            r.attachment_type as reply_attachment_type,
            r.created_at as reply_created_at,
            r.admin_id,
            a.firstname as admin_firstname,
            a.lastname as admin_lastname
        FROM support_messages m
        LEFT JOIN support_replies r ON m.id = r.message_id
        LEFT JOIN admins a ON r.admin_id = a.admin_id
        WHERE m.srcode = ?
        ORDER BY m.created_at DESC, r.created_at ASC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$srcode]);
    
    $messages = [];
    $current_message = null;
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($current_message === null || $current_message['id'] !== $row['id']) {
            if ($current_message !== null) {
                $messages[] = $current_message;
            }
            
            $current_message = [
                'id' => $row['id'],
                'message_text' => htmlspecialchars($row['message_text']),
                'attachment_name' => $row['attachment_name'],
                'attachment_type' => $row['attachment_type'],
                'status' => $row['status'],
                'created_at' => date('M j, Y g:i A', strtotime($row['created_at'])),
                'reply_count' => $row['reply_count'],
                'has_reply' => false,
                'replies' => []
            ];
        }
        
        if ($row['reply_id']) {
            $current_message['has_reply'] = true;
            $current_message['replies'][] = [
                'reply_id' => $row['reply_id'],
                'reply_text' => htmlspecialchars($row['reply_text']),
                'attachment_name' => $row['reply_attachment_name'],
                'attachment_type' => $row['reply_attachment_type'],
                'created_at' => date('M j, Y g:i A', strtotime($row['reply_created_at'])),
                'admin_id' => $row['admin_id'],
                'admin_name' => $row['admin_firstname'] . ' ' . $row['admin_lastname']
            ];
        }
    }
    
    if ($current_message !== null) {
        $messages[] = $current_message;
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'debug' => [
            'srcode' => $srcode,
            'message_count' => count($messages)
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in get_support_messages.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}