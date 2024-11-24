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
                // Show SweetAlert2 confirmation
                $_SESSION['temp_srcode'] = $srcode; // Temporarily store srcode
                header("Location: ../login.php?prompt=reactivate");
                exit();
            }

            // If account is active, proceed with normal login
            $_SESSION['srcode'] = $user['srcode'];
            // ... rest of your login logic ...
            header("Location: ../pages/student/student.php");
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
        $query = "UPDATE students SET 
                  status = 'active',
                  deactivated_at = NULL 
                  WHERE srcode = ?";
                  
        $stmt = $pdo->prepare($query);
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