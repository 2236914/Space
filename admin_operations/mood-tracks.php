<?php
class MoodTracks {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Get today's mood
    public function getTodayMood($srcode) {
        try {
            $query = "SELECT moodlog_id, mood_name as mood, selected_emoji, log_date 
                     FROM moodlog 
                     WHERE srcode = ? AND DATE(log_date) = CURDATE()
                     ORDER BY log_date DESC LIMIT 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$srcode]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting today's mood: " . $e->getMessage());
            return null;
        }
    }

    // Get yesterday's mood
    public function getYesterdayMood($srcode) {
        try {
            $query = "SELECT mood_name as mood 
                     FROM moodlog 
                     WHERE srcode = ? AND DATE(log_date) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                     ORDER BY log_date DESC LIMIT 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$srcode]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting yesterday's mood: " . $e->getMessage());
            return null;
        }
    }

    // Get weekly entries count
    public function getWeeklyEntriesCount($srcode) {
        try {
            $query = "SELECT COUNT(DISTINCT DATE(log_date)) as entry_count 
                     FROM moodlog 
                     WHERE srcode = ? 
                     AND log_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$srcode]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting weekly entries: " . $e->getMessage());
            return ['entry_count' => 0];
        }
    }

    // Get current mood streak
    public function getCurrentStreak($srcode) {
        try {
            $query = "WITH RECURSIVE dates AS (
                       SELECT DATE(log_date) as log_date
                       FROM moodlog
                       WHERE srcode = ?
                       GROUP BY DATE(log_date)
                       ORDER BY log_date DESC
                     )
                     SELECT COUNT(*) as streak
                     FROM (
                       SELECT log_date,
                              @rn := @rn + 1 as rn
                       FROM dates
                       CROSS JOIN (SELECT @rn := 0) as vars
                       WHERE log_date >= DATE_SUB(CURDATE(), INTERVAL @rn DAY)
                     ) as consecutive
                     WHERE log_date = DATE_SUB(CURDATE(), INTERVAL rn-1 DAY)";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$srcode]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting streak: " . $e->getMessage());
            return ['streak' => 0];
        }
    }

    // Get journal statistics
    public function getJournalStats($srcode) {
        try {
            $query = "SELECT 
                      (SELECT COUNT(*) FROM journal_entries WHERE srcode = ?) as total_entries,
                      (SELECT COUNT(*) FROM journal_entries 
                       WHERE srcode = ? 
                       AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)) as new_entries";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$srcode, $srcode]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting journal stats: " . $e->getMessage());
            return ['total_entries' => 0, 'new_entries' => 0];
        }
    }

    // Helper function for mood colors
    public static function getMoodColor($mood) {
        $colors = [
            'Happy' => 'success',
            'Excited' => 'info',
            'Calm' => 'primary',
            'Neutral' => 'secondary',
            'Sad' => 'warning',
            'Angry' => 'danger',
            'Anxious' => 'warning',
            'Stressed' => 'danger'
        ];
        return $colors[$mood] ?? 'dark';
    }

    // Helper function for mood icons
    public static function getMoodIcon($mood) {
        $icons = [
            'Happy' => 'sentiment_very_satisfied',
            'Excited' => 'mood',
            'Calm' => 'spa',
            'Neutral' => 'sentiment_neutral',
            'Sad' => 'sentiment_dissatisfied',
            'Angry' => 'mood_bad',
            'Anxious' => 'psychology',
            'Stressed' => 'warning'
        ];
        return $icons[$mood] ?? 'mood';
    }

    // Log a new mood
    public function logMood($srcode, $mood, $emoji, $description = '') {
        try {
            $query = "INSERT INTO moodlog (srcode, mood_name, selected_emoji, description) 
                     VALUES (?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([$srcode, $mood, $emoji, $description]);
        } catch (PDOException $e) {
            error_log("Error logging mood: " . $e->getMessage());
            return false;
        }
    }

    // Get all moods for today
    public function getAllTodayMoods($srcode) {
        try {
            $query = "SELECT moodlog_id, 
                     REPLACE(TRIM(selected_emoji), ',', '') as selected_emoji,
                     mood_name as mood, 
                     log_date 
                     FROM moodlog 
                     WHERE srcode = ? AND DATE(log_date) = CURDATE()
                     ORDER BY log_date DESC";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$srcode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting today's moods: " . $e->getMessage());
            return null;
        }
    }

    // Add these methods to the MoodTracks class

    public function getMoodEntryByDate($srcode, $date) {
        try {
            $query = "SELECT * FROM moodlog 
                      WHERE srcode = ? 
                      AND DATE(log_date) = ?
                      ORDER BY log_date DESC 
                      LIMIT 1";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$srcode, $date]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting mood entry: " . $e->getMessage());
            return null;
        }
    }

    public function logActivity($srcode, $action, $details, $ipAddress) {
        try {
            $query = "INSERT INTO activity_logs (srcode, action, action_details, ip_address) 
                      VALUES (?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([
                $srcode,
                $action,
                $details,
                $ipAddress
            ]);
        } catch (PDOException $e) {
            error_log("Error logging activity: " . $e->getMessage());
            return false;
        }
    }

    // Add this as a method in your MoodTracks class
    public function getMoodHistory($userId, $selectedDate = null) {
        try {
            $query = "SELECT moodlog_id, selected_emoji, mood_name, description, 
                      DATE_FORMAT(log_date, '%Y-%m-%d %h:%i %p') as formatted_date 
                      FROM moodlog 
                      WHERE srcode = :userId";
            if ($selectedDate) {
                $query .= " AND DATE(log_date) = :selectedDate";
            }
            $query .= " ORDER BY log_date DESC";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
            if ($selectedDate) {
                $stmt->bindValue(':selectedDate', $selectedDate, PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getMoodHistory: " . $e->getMessage());
            return null;
        }
    }

    public function logMoodActivity($srcode, $moodNames) {
        try {
            $selected_moods = is_array($moodNames) ? implode(', ', $moodNames) : $moodNames;
            $action = "Mood Log";
            $action_details = "Student logged mood(s): " . $selected_moods;
            $ip_address = $_SERVER['REMOTE_ADDR'];
            
            $query = "INSERT INTO activity_logs (srcode, action, action_details, ip_address) 
                      VALUES (:srcode, :action, :action_details, :ip_address)";
                      
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([
                ':srcode' => $srcode,
                ':action' => $action,
                ':action_details' => $action_details,
                ':ip_address' => $ip_address
            ]);
        } catch (PDOException $e) {
            error_log("Error in logMoodActivity: " . $e->getMessage());
            throw $e;
        }
    }

    // Then in your saveMood method:
    public function saveMood($srcode, $moodData) {
        try {
            $this->pdo->beginTransaction();
            
            // Save the mood entry
            $query = "INSERT INTO moodlog (srcode, mood_name, description, log_date) 
                     VALUES (:srcode, :mood_name, :description, :log_date)";
            
            $stmt = $this->pdo->prepare($query);
            $moodString = implode(', ', $moodData['moods']);
            
            $success = $stmt->execute([
                ':srcode' => $srcode,
                ':mood_name' => $moodString,
                ':description' => $moodData['description'],
                ':log_date' => $moodData['date']
            ]);

            if ($success) {
                // Log the activity
                $this->logMoodActivity($srcode, $moodData['moods']);
                $this->pdo->commit();
                return true;
            } else {
                throw new Exception('Failed to save mood entry');
            }

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error in saveMood: " . $e->getMessage());
            throw $e;
        }
    }
}
?>