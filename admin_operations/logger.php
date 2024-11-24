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
    public function logActivity($data) {
        try {
            $sql = "INSERT INTO activity_logs 
                    (srcode, therapist_id, admin_id, action, action_details, ip_address) 
                    VALUES 
                    (:srcode, :therapist_id, :admin_id, :action, :action_details, :ip_address)";
            
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                ':srcode' => $data['srcode'] ?? null,
                ':therapist_id' => $data['therapist_id'] ?? null,
                ':admin_id' => $data['admin_id'] ?? null,
                ':action' => $data['action'],
                ':action_details' => $data['action_details'] ?? null,
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
        } catch (Exception $e) {
            error_log('Logging error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get activities for a specific user (student, therapist, or admin)
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
            if (isset($params['therapist_id'])) {
                $conditions[] = "therapist_id = :therapist_id";
                $queryParams[':therapist_id'] = $params['therapist_id'];
            }
            if (isset($params['admin_id'])) {
                $conditions[] = "admin_id = :admin_id";
                $queryParams[':admin_id'] = $params['admin_id'];
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
}