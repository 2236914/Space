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

    public function getUserPostCount($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) 
                FROM posts 
                WHERE username = (
                    SELECT username 
                    FROM students 
                    WHERE srcode = ?
                )
                AND status = 'active'
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting user post count: " . $e->getMessage());
            return 0;
        }
    }

    public function getUserYesterdayPostCount($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) 
                FROM posts 
                WHERE username = (
                    SELECT username 
                    FROM students 
                    WHERE srcode = ?
                )
                AND DATE(created_at) = DATE(NOW() - INTERVAL 1 DAY)
                AND status = 'active'
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting yesterday's post count: " . $e->getMessage());
            return 0;
        }
    }

    // Update the session counting queries
    public function getStudentSessionCounts($srcode) {
        try {
            // Total Sessions (all sessions regardless of status)
            $total = $this->pdo->prepare("
                SELECT COUNT(*) FROM therapy_sessions 
                WHERE srcode = ?
            ");
            
            // Upcoming Sessions (ONLY pending or confirmed AND future dates)
            $upcoming = $this->pdo->prepare("
                SELECT COUNT(*) FROM therapy_sessions 
                WHERE srcode = ? 
                AND session_date >= CURRENT_DATE 
                AND status IN ('pending', 'confirmed')
                AND status != 'cancelled'
            ");
            
            // Completed Sessions
            $completed = $this->pdo->prepare("
                SELECT COUNT(*) FROM therapy_sessions 
                WHERE srcode = ? 
                AND status = 'completed'
            ");
            
            // Cancelled Sessions
            $cancelled = $this->pdo->prepare("
                SELECT COUNT(*) FROM therapy_sessions 
                WHERE srcode = ? 
                AND status = 'cancelled'
            ");

            // Execute all queries
            $total->execute([$srcode]);
            $upcoming->execute([$srcode]);
            $completed->execute([$srcode]);
            $cancelled->execute([$srcode]);

            // Debug output
            error_log("Upcoming sessions count query result: " . $upcoming->fetchColumn());

            return [
                'total' => $total->fetchColumn(),
                'upcoming' => $upcoming->fetchColumn(),
                'completed' => $completed->fetchColumn(),
                'cancelled' => $cancelled->fetchColumn()
            ];
        } catch (PDOException $e) {
            error_log("Error in getStudentSessionCounts: " . $e->getMessage());
            return ['total' => 0, 'upcoming' => 0, 'completed' => 0, 'cancelled' => 0];
        }
    }

    // Student Engagement Analytics
    public function getStudentSocialMetrics($months = 6) {
        try {
            // Fetch active users
            $active_users = $this->pdo->prepare("
                SELECT 
                    DATE_FORMAT(session_date, '%Y-%m') as month,
                    COUNT(DISTINCT srcode) as active_users
                FROM therapy_sessions
                WHERE session_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(session_date, '%Y-%m')
                ORDER BY month
            ");
            $active_users->execute();
            $active_users_result = $active_users->fetchAll(PDO::FETCH_ASSOC);

            // Fetch post activity
            $post_activity = $this->pdo->prepare("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as post_count
                FROM posts
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month
            ");
            $post_activity->execute();
            $post_activity_result = $post_activity->fetchAll(PDO::FETCH_ASSOC);

            // Get interaction metrics (if needed)
            $interaction_metrics = $this->pdo->prepare("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as interaction_count
                FROM (
                    SELECT created_at FROM likes
                    UNION ALL
                    SELECT created_at FROM comments
                ) interactions
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month
            ");
            $interaction_metrics->execute([$months]);
            $interaction_metrics_result = $interaction_metrics->fetchAll(PDO::FETCH_ASSOC);

            return [
                'active_users' => $active_users_result,
                'post_activity' => $post_activity_result,
                'interaction_metrics' => $interaction_metrics_result
            ];
        } catch (PDOException $e) {
            error_log("Error in getStudentEngagementMetrics: " . $e->getMessage());
            return [
                'active_users' => [],
                'post_activity' => [],
                'interaction_metrics' => []
            ];
        }
    }

    // Session Analytics
    public function getSessionAnalytics($weeks = 4) {
        try {
            $completion_rates = $this->pdo->prepare("
                SELECT 
                    DATE_FORMAT(session_date, '%Y-%u') as week,
                    COUNT(*) as total_sessions,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_sessions,
                    (SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) as completion_rate
                FROM therapy_sessions
                WHERE session_date >= DATE_SUB(NOW(), INTERVAL ? WEEK)
                GROUP BY DATE_FORMAT(session_date, '%Y-%u')
                ORDER BY week
            ");
            $completion_rates->execute([$weeks]);
            
            return [
                'completion_rates' => $completion_rates->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (PDOException $e) {
            error_log("Error in getSessionAnalytics: " . $e->getMessage());
            return [
                'completion_rates' => []
            ];
        }
    }

    // Predictive Analysis
    public function getPredictiveInsights() {
        try {
            // Identify patterns in student engagement
            $engagement_patterns = $this->pdo->query("
                SELECT 
                    HOUR(created_at) as hour_of_day,
                    COUNT(*) as activity_count,
                    DAYNAME(created_at) as day_of_week
                FROM (
                    SELECT created_at FROM posts WHERE post_type = 'student'
                    UNION ALL
                    SELECT created_at FROM comments
                    UNION ALL
                    SELECT created_at FROM likes
                ) all_activity
                GROUP BY HOUR(created_at), DAYNAME(created_at)
                ORDER BY activity_count DESC
            ")->fetchAll(PDO::FETCH_ASSOC);

            // Identify students who might need support
            $at_risk_students = $this->pdo->query("
                SELECT 
                    s.srcode,
                    s.firstname,
                    s.lastname,
                    COUNT(DISTINCT p.post_id) as post_count,
                    COUNT(DISTINCT l.like_id) as like_count,
                    COUNT(DISTINCT c.comment_id) as comment_count,
                    DATEDIFF(NOW(), s.last_login) as days_since_login
                FROM students s
                LEFT JOIN posts p ON s.username = p.username
                LEFT JOIN likes l ON s.username = l.username
                LEFT JOIN comments c ON s.username = c.username
                GROUP BY s.srcode
                HAVING 
                    (post_count + like_count + comment_count) < 5
                    OR days_since_login > 14
                ORDER BY days_since_login DESC
            ")->fetchAll(PDO::FETCH_ASSOC);

            return [
                'peak_activity_times' => $engagement_patterns,
                'at_risk_students' => $at_risk_students
            ];
        } catch (PDOException $e) {
            error_log("Error in getPredictiveInsights: " . $e->getMessage());
            return false;
        }
    }

    public function getTherapistMonthlyStats($therapist_id) {
        try {
            $query = "SELECT 
                MONTH(session_date) as month,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_sessions,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_sessions
                FROM therapy_sessions 
                WHERE therapist_id = ? 
                AND session_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY MONTH(session_date)
                ORDER BY MONTH(session_date)";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$therapist_id]);
            
            // Initialize arrays for all months
            $months = array_fill(1, 12, ['completed' => 0, 'cancelled' => 0]);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $months[$row['month']] = [
                    'completed' => (int)$row['completed_sessions'],
                    'cancelled' => (int)$row['cancelled_sessions']
                ];
            }
            
            return [
                'completed' => array_column($months, 'completed'),
                'cancelled' => array_column($months, 'cancelled')
            ];
        } catch (PDOException $e) {
            error_log("Error in getTherapistMonthlyStats: " . $e->getMessage());
            return null;
        }
    }

    public function getTherapistWeeklyCompletion($therapist_id) {
        try {
            $query = "SELECT 
                DAYOFWEEK(session_date) as day_of_week,
                COUNT(*) as total_sessions,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_sessions
                FROM therapy_sessions 
                WHERE therapist_id = ? 
                AND session_date >= DATE_SUB(CURDATE(), INTERVAL 4 WEEK)
                GROUP BY DAYOFWEEK(session_date)
                ORDER BY DAYOFWEEK(session_date)";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$therapist_id]);
            
            $completion_rates = array_fill(1, 7, 0);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $day_index = $row['day_of_week'] - 1;
                $completion_rate = $row['total_sessions'] > 0 
                    ? round(($row['completed_sessions'] / $row['total_sessions']) * 100, 1)
                    : 0;
                $completion_rates[$day_index] = $completion_rate;
            }
            
            return $completion_rates;
        } catch (PDOException $e) {
            error_log("Error in getTherapistWeeklyCompletion: " . $e->getMessage());
            return array_fill(0, 7, 0);
        }
    }

    public function getTherapistPeakActivity($therapist_id) {
        try {
            $query = "SELECT 
                DAYNAME(session_date) as peak_day,
                HOUR(session_time) as peak_hour,
                COUNT(*) as session_count
                FROM therapy_sessions 
                WHERE therapist_id = ? 
                AND status = 'completed'
                GROUP BY DAYNAME(session_date), HOUR(session_time)
                ORDER BY session_count DESC
                LIMIT 1";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$therapist_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $hour = $result['peak_hour'];
                $ampm = $hour >= 12 ? 'PM' : 'AM';
                $hour = $hour % 12;
                $hour = $hour ? $hour : 12;
                return $result['peak_day'] . "s at " . $hour . $ampm;
            }
            
            return "Not enough data";
        } catch (PDOException $e) {
            error_log("Error in getTherapistPeakActivity: " . $e->getMessage());
            return "Not available";
        }
    }

    public function getTherapistTrends($therapist_id) {
        try {
            $trends = [
                'growth_rate' => 0,
                'common_times' => [],
                'cancellation_patterns' => []
            ];
            
            // Growth rate calculation
            $growth_query = "SELECT 
                COUNT(CASE WHEN session_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 MONTH) 
                    AND DATE_SUB(CURDATE(), INTERVAL 1 MONTH) THEN 1 END) as last_month,
                COUNT(CASE WHEN session_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) 
                    AND CURDATE() THEN 1 END) as this_month
                FROM therapy_sessions 
                WHERE therapist_id = ?";
            
            $stmt = $this->pdo->prepare($growth_query);
            $stmt->execute([$therapist_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['last_month'] > 0) {
                $trends['growth_rate'] = round((($result['this_month'] - $result['last_month']) / $result['last_month']) * 100, 1);
            }
            
            return $trends;
        } catch (PDOException $e) {
            error_log("Error in getTherapistTrends: " . $e->getMessage());
            return [
                'growth_rate' => 0,
                'common_times' => [],
                'cancellation_patterns' => []
            ];
        }
    }

    public function getAdminOverviewStats() {
        try {
            $stats = [
                'total_students' => 0,
                'total_therapists' => 0,
                'total_sessions' => 0,
                'completion_rate' => 0
            ];

            // Get total students
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM students WHERE status = 'active'");
            $stats['total_students'] = $stmt->fetchColumn();

            // Get total therapists
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM therapists WHERE status = 'active'");
            $stats['total_therapists'] = $stmt->fetchColumn();

            // Get session stats
            $query = "SELECT 
                COUNT(*) as total_sessions,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_sessions
                FROM therapy_sessions";
            $stmt = $this->pdo->query($query);
            $session_stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stats['total_sessions'] = $session_stats['total_sessions'];
            if ($session_stats['total_sessions'] > 0) {
                $stats['completion_rate'] = round(($session_stats['completed_sessions'] / $session_stats['total_sessions']) * 100, 1);
            }

            return $stats;
        } catch (PDOException $e) {
            error_log("Error in getAdminOverviewStats: " . $e->getMessage());
            return $stats;
        }
    }

    public function getSystemPerformanceMetrics() {
        try {
            $metrics = [
                'response_time' => [],
                'active_users' => [],
                'error_rates' => []
            ];

            // Get average response times by hour
            $query = "SELECT 
                HOUR(created_at) as hour,
                AVG(response_time) as avg_response
                FROM system_logs
                WHERE DATE(created_at) = CURDATE()
                GROUP BY HOUR(created_at)
                ORDER BY hour";
            $stmt = $this->pdo->query($query);
            $metrics['response_time'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get active users count by hour
            $query = "SELECT 
                HOUR(login_time) as hour,
                COUNT(DISTINCT srcode) as user_count
                FROM session_logs
                WHERE DATE(login_time) = CURDATE()
                GROUP BY HOUR(login_time)
                ORDER BY hour";
            $stmt = $this->pdo->query($query);
            $metrics['active_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $metrics;
        } catch (PDOException $e) {
            error_log("Error in getSystemPerformanceMetrics: " . $e->getMessage());
            return $metrics;
        }
    }

    public function getTherapistPerformanceStats() {
        try {
            $query = "SELECT 
                t.therapist_id,
                t.firstname,
                t.lastname,
                COUNT(ts.session_id) as total_sessions,
                COUNT(CASE WHEN ts.status = 'completed' THEN 1 END) as completed_sessions,
                AVG(CASE WHEN ts.status = 'completed' THEN ts.rating ELSE NULL END) as avg_rating
                FROM therapists t
                LEFT JOIN therapy_sessions ts ON t.therapist_id = ts.therapist_id
                WHERE t.status = 'active'
                GROUP BY t.therapist_id
                ORDER BY completed_sessions DESC";
            
            $stmt = $this->pdo->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getTherapistPerformanceStats: " . $e->getMessage());
            return [];
        }
    }

    public function getStudentEngagementMetrics($days = 30) {
        try {
            $metrics = [
                'daily_logins' => [],
                'session_attendance' => 0,
                'mood_check_rate' => 0
            ];

            // Get daily login counts
            $query = "SELECT 
                DATE(login_time) as date,
                COUNT(DISTINCT srcode) as login_count
                FROM session_logs
                WHERE login_time >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(login_time)
                ORDER BY date";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$days]);
            $metrics['daily_logins'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate session attendance rate
            $query = "SELECT 
                COUNT(*) as total_sessions,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as attended_sessions
                FROM therapy_sessions
                WHERE session_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$days]);
            $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($attendance['total_sessions'] > 0) {
                $metrics['session_attendance'] = round(($attendance['attended_sessions'] / $attendance['total_sessions']) * 100, 1);
            }

            return $metrics;
        } catch (PDOException $e) {
            error_log("Error in getStudentEngagementMetrics: " . $e->getMessage());
            return $metrics;
        }
    }
}
?>