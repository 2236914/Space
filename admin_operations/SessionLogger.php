<?php
class SessionLogger {
    private $pdo;

    public function __construct($pdo) {
        if (!($pdo instanceof PDO)) {
            throw new Exception('Invalid PDO connection');
        }
        $this->pdo = $pdo;
    }

    public function startSession($userType, $userId) {
        try {
            $column = $this->getUserColumn($userType);
            if (!$column) return false;

            // Deactivate existing sessions
            $this->deactivateExistingSessions($userType, $userId);

            // Start new session
            $stmt = $this->pdo->prepare("
                INSERT INTO session_logs 
                ({$column}, ip_address, session_status) 
                VALUES (?, ?, 'active')
            ");

            if ($stmt->execute([
                $userId,
                $this->getIPAddress()
            ])) {
                return $this->pdo->lastInsertId(); // Return the new session ID
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Session Start Error: " . $e->getMessage());
            return false;
        }
    }

    public function endSession($userType, $userId) {
        try {
            $column = $this->getUserColumn($userType);
            if (!$column) return false;

            $stmt = $this->pdo->prepare("
                UPDATE session_logs 
                SET logout_time = CURRENT_TIMESTAMP,
                    session_status = 'inactive'
                WHERE {$column} = ? 
                AND session_status = 'active'
            ");

            return $stmt->execute([$userId]);
        } catch (Exception $e) {
            error_log("Session End Error: " . $e->getMessage());
            return false;
        }
    }

    private function deactivateExistingSessions($userType, $userId) {
        $column = $this->getUserColumn($userType);
        if (!$column) return false;

        $stmt = $this->pdo->prepare("
            UPDATE session_logs 
            SET logout_time = CURRENT_TIMESTAMP,
                session_status = 'inactive'
            WHERE {$column} = ? 
            AND session_status = 'active'
        ");

        return $stmt->execute([$userId]);
    }

    private function getUserColumn($userType) {
        switch ($userType) {
            case 'student':
                return 'srcode';
            case 'therapist':
                return 'therapist_id';
            case 'admin':
                return 'admin_id';
            default:
                return false;
        }
    }

    private function getIPAddress() {
        // Check for proxy
        $proxy_headers = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED'
        );

        foreach ($proxy_headers as $header) {
            if (isset($_SERVER[$header])) {
                $addresses = explode(',', $_SERVER[$header]);
                return trim($addresses[0]);
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    public function getActiveSession($userType, $userId) {
        try {
            $column = $this->getUserColumn($userType);
            if (!$column) return false;

            $stmt = $this->pdo->prepare("
                SELECT * FROM session_logs 
                WHERE {$column} = ? 
                AND session_status = 'active'
                ORDER BY login_time DESC 
                LIMIT 1
            ");

            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get Session Error: " . $e->getMessage());
            return false;
        }
    }

    public function logActivity($srcode, $therapist_id, $admin_id, $action, $description, $ip_address) {
        try {
            // Debug logging
            error_log("=== Activity Logging Start ===");
            error_log("SR Code: " . ($srcode ?? 'null'));
            error_log("Action: " . $action);
            error_log("Action Details: " . $description);
            error_log("IP: " . $ip_address);
            
            // Modified SQL to match your actual table structure
            $sql = "INSERT INTO activity_logs 
                    (srcode, action, action_details, ip_address) 
                    VALUES 
                    (?, ?, ?, ?)";
            
            error_log("Executing SQL: " . $sql);
            
            $stmt = $this->pdo->prepare($sql);
            
            // Execute with only the fields that exist in your table
            $success = $stmt->execute([
                $srcode,
                $action,
                $description,
                $ip_address
            ]);
            
            if (!$success) {
                error_log("Failed to insert activity log. Error info: " . print_r($stmt->errorInfo(), true));
                return false;
            }
            
            error_log("Activity log inserted successfully. ID: " . $this->pdo->lastInsertId());
            error_log("=== Activity Logging End ===");
            return true;
            
        } catch (PDOException $e) {
            error_log("Database error in logActivity: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw new Exception('Failed to log activity: ' . $e->getMessage());
        }
    }

    public function logUserSession($userType, $userId, $action = 'login') {
        $ip = $this->getIPAddress();
        
        try {
            if ($action === 'login') {
                return $this->startSession($userType, $userId);
            } else if ($action === 'logout') {
                return $this->endSession($userType, $userId);
            }
            return false;
        } catch (Exception $e) {
            error_log("Error logging session: " . $e->getMessage());
            return false;
        }
    }
}