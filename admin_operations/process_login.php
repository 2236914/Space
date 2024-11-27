<?php
session_start();
require_once '../configs/config.php';

if (isset($_POST['login'])) {
    $srcode = $_POST['srcode'];
    $password = $_POST['password'];

    try {
        // First check if credentials are valid
        $stmt = $pdo->prepare("SELECT * FROM students WHERE srcode = ?");
        $stmt->execute([$srcode]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // If account is deactivated, show reactivation prompt
            if ($user['status'] === 'deactivated') {
                $_SESSION['temp_srcode'] = $srcode;
                header("Location: ../login.php?prompt=reactivate");
                exit();
            }

            // Set session variables
            $_SESSION['user_id'] = $user['srcode'];
            $_SESSION['srcode'] = $user['srcode'];
            $_SESSION['role'] = 'student';

            // Add login activity log here
            $log_query = "INSERT INTO activity_logs 
                          (srcode, action, action_details, created_at) 
                          VALUES (?, 'LOGIN', 'Student logged in successfully', NOW())";
            $stmt_log = $pdo->prepare($log_query);
            $stmt_log->execute([$user['srcode']]);

            // Check if student has logged their mood today
            $mood_stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM moodlog 
                WHERE srcode = ? 
                AND DATE(log_date) = CURRENT_DATE()
            ");
            $mood_stmt->execute([$srcode]);
            $has_mood_today = $mood_stmt->fetchColumn() > 0;

            // Redirect based on mood log status
            if ($has_mood_today) {
                header("Location: ../pages/student/student.php");
            } else {
                header("Location: ../pages/student/moodtracker.php?prompt=daily_log");
            }
            exit();
        } else {
            header("Location: ../login.php?error=invalid_credentials");
            exit();
        }
    } catch (Exception $e) {
        error_log("Login Error: " . $e->getMessage());
        header("Location: ../login.php?error=system_error");
        exit();
    }
}

// Handle reactivation confirmation
if (isset($_POST['reactivate']) && isset($_SESSION['temp_srcode'])) {
    $srcode = $_SESSION['temp_srcode'];
    
    $pdo->beginTransaction();
    
    try {
        // Update user status to active
        $stmt = $pdo->prepare("
            UPDATE students 
            SET status = 'active', deactivated_at = NULL 
            WHERE srcode = ?
        ");
        $stmt->execute([$srcode]);
        
        // Get student details for the log
        $student_query = "SELECT CONCAT(firstname, ' ', lastname) as fullname 
                         FROM students WHERE srcode = ?";
        $stmt_student = $pdo->prepare($student_query);
        $stmt_student->execute([$srcode]);
        $student = $stmt_student->fetch(PDO::FETCH_ASSOC);
        
        // Log the reactivation
        $log_query = "INSERT INTO activity_logs 
                      (user_id, user_type, activity_type, description, ip_address, created_at) 
                      VALUES (?, 'student', 'ACCOUNT_REACTIVATION', ?, ?, NOW())";
        
        $description = "Student " . $student['fullname'] . " (SR-Code: " . $srcode . ") reactivated their account";
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        $stmt_log = $pdo->prepare($log_query);
        $stmt_log->execute([$srcode, $description, $ip_address]);
        
        $pdo->commit();
        
        // Start session for the user
        $_SESSION['srcode'] = $srcode;
        unset($_SESSION['temp_srcode']); // Clean up temporary session variable
        
        header("Location: ../pages/student/student.php?msg=reactivated");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Reactivation Error: " . $e->getMessage());
        header("Location: ../login.php?error=reactivation_failed");
        exit();
    }
} 