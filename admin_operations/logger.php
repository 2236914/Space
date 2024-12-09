<?php
class Logger {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Log an activity for any user type (student, therapist, or admin)
     *
     * @param array $data Array containing user identification and action details
     * @return bool
     */
    public function logActivity($data): bool {
        try {
            // Debug logging
            error_log("Attempting to log activity:");
            error_log(print_r($data, true));

            // Modified SQL to match your actual table structure
            $sql = "INSERT INTO activity_logs 
                    (srcode, action, action_details, ip_address) 
                    VALUES 
                    (:srcode, :action, :action_details, :ip_address)";
                    
            $stmt = $this->pdo->prepare($sql);

            // Execute with only the fields that exist in your table
            $result = $stmt->execute([
                ':srcode' => !empty($data['srcode']) ? (int)$data['srcode'] : null,
                ':action' => $data['action'],
                ':action_details' => $data['action_details'],
                ':ip_address' => $data['ip_address'] ?? $_SERVER['REMOTE_ADDR']
            ]);

            if ($result) {
                error_log("Activity logged successfully. ID: " . $this->pdo->lastInsertId());
                return true;
            } else {
                error_log("Failed to log activity. PDO Error: " . print_r($stmt->errorInfo(), true));
                return false;
            }

        } catch (PDOException $e) {
            error_log("Database error in logActivity: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get activities for a specific user
     *
     * @param array $params Array containing user type and ID
     * @param int $limit Number of records to return
     * @return array
     */
    public function getActivities($params, $limit = 10) {
        try {
            $conditions = [];
            $queryParams = [];

            if (isset($params['srcode'])) {
                $conditions[] = "srcode = :srcode";
                $queryParams[':srcode'] = $params['srcode'];
            }

            $whereClause = !empty($conditions) ? "WHERE " . implode(" OR ", $conditions) : "";

            $sql = "SELECT * FROM activity_logs
                    $whereClause
                    ORDER BY created_at DESC
                    LIMIT :limit";

            $stmt = $this->pdo->prepare($sql);

            foreach ($queryParams as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error fetching activities: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Log user activity with simplified interface
     *
     * @param mixed $userId
     * @param string $userType
     * @param string $action
     * @param string $actionDetails
     * @return bool
     */
    public function logUserActivity($userId, $userType, $action, $actionDetails = '') {
        if ($userType !== 'student') {
            error_log("Invalid user type for activity logging");
            return false;
        }

        $data = [
            'srcode' => $userId,
            'action' => $action,
            'action_details' => $actionDetails,
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ];

        return $this->logActivity($data);
    }
}
