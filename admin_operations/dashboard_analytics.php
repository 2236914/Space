<?php
class DashboardAnalytics {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Get total check-ins
    public function getTotalCheckins() {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM moodlog");
            $stmt->execute();
            return number_format($stmt->fetch()['total']);
        } catch (PDOException $e) {
            return 0;
        }
    }

    // Calculate student growth
    public function getStudentGrowth() {
        try {
            // Get today's and yesterday's counts
            $sql = "SELECT 
                (SELECT COUNT(*) FROM students WHERE DATE(created_at) = CURDATE()) as today_count,
                (SELECT COUNT(*) FROM students WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)) as yesterday_count";
            
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Debug line - remove after testing
            error_log("Today: " . $result['today_count'] . ", Yesterday: " . $result['yesterday_count']);
            
            if ($result['yesterday_count'] > 0) {
                $percentChange = round((($result['today_count'] - $result['yesterday_count']) / $result['yesterday_count']) * 100, 1);
                return [
                    'percent' => $percentChange,
                    'class' => $percentChange >= 0 ? 'text-success' : 'text-danger',
                    'today' => $result['today_count'],
                    'yesterday' => $result['yesterday_count']
                ];
            }
            
            // If there were no students yesterday but there are today
            if ($result['today_count'] > 0) {
                return [
                    'percent' => 100,
                    'class' => 'text-success',
                    'new' => true
                ];
            }
            
            // If no students both days
            return [
                'percent' => 0,
                'class' => 'text-secondary',
                'no_change' => true
            ];
            
        } catch (PDOException $e) {
            // Log the actual error for debugging
            error_log("Error in getStudentGrowth: " . $e->getMessage());
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    public function getNewStudents() {
        try {
            // Get today's new student count
            $sql = "SELECT COUNT(*) as new_students 
                    FROM students 
                    WHERE DATE(created_at) = CURDATE()";
            
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'count' => $result['new_students'],
                'class' => $result['new_students'] > 0 ? 'text-success' : 'text-secondary'
            ];
            
        } catch (PDOException $e) {
            error_log("Error in getNewStudents: " . $e->getMessage());
            return ['error' => true];
        }
    }

    public function getUserTotalCheckins($srcode) {
        try {
            $query = "SELECT COUNT(*) as total FROM session_logs 
                     WHERE srcode = ? AND login_time IS NOT NULL";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$srcode]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return number_format($result['total']); // Format number with commas for readability
        } catch (PDOException $e) {
            error_log("Error getting user checkins: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalStudents() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM students");
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting total students: " . $e->getMessage());
            return 0;
        }
    }
}
?>