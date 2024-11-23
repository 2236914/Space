<?php
function logActivity($pdo, $data) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs 
            (srcode, therapist_id, admin_id, action, action_details, ip_address) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['srcode'] ?? null,
            $data['therapist_id'] ?? null,
            $data['admin_id'] ?? null,
            $data['action'],
            $data['action_details'],
            $_SERVER['REMOTE_ADDR']
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Error logging activity: " . $e->getMessage());
        return false;
    }
}

function logSession($pdo, $data, $type = 'login') {
    try {
        if ($type === 'login') {
            // Create new session log
            $stmt = $pdo->prepare("
                INSERT INTO session_logs 
                (srcode, therapist_id, admin_id, ip_address) 
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['srcode'] ?? null,
                $data['therapist_id'] ?? null,
                $data['admin_id'] ?? null,
                $_SERVER['REMOTE_ADDR']
            ]);
        } else {
            // Update existing session log with logout time
            $stmt = $pdo->prepare("
                UPDATE session_logs 
                SET logout_time = CURRENT_TIMESTAMP,
                    session_status = 'inactive'
                WHERE (srcode = ? OR therapist_id = ? OR admin_id = ?)
                AND session_status = 'active'
            ");
            
            $stmt->execute([
                $data['srcode'] ?? null,
                $data['therapist_id'] ?? null,
                $data['admin_id'] ?? null
            ]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Error logging session: " . $e->getMessage());
        return false;
    }
}
?>