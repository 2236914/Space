<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../configs/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = array('status' => 'error', 'message' => '', 'role' => '', 'redirect' => '');
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $current_time = date('Y-m-d H:i:s');

    try {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        
        // Debug log
        error_log("Login attempt for email: " . $email);
        
        // Check students table first
        $stmt = $pdo->prepare("SELECT srcode, password, firstname, lastname, status FROM students WHERE email = ?");
        $stmt->execute([$email]);
        $student = $stmt->fetch();
        
        if ($student) {
            $hashedPassword = hash('sha256', $password);
            
            if ($hashedPassword === $student['password']) {
                // Check if account is deactivated
                if ($student['status'] === 'deactivated') {
                    // Reactivate the account
                    $reactivateStmt = $pdo->prepare("UPDATE students SET status = 'active' WHERE srcode = ?");
                    $reactivateStmt->execute([$student['srcode']]);
                    
                    // Log the reactivation
                    $logStmt = $pdo->prepare("
                        INSERT INTO activity_logs 
                        (srcode, action, action_details, ip_address, created_at) 
                        VALUES (?, 'ACCOUNT_REACTIVATION', ?, ?, ?)
                    ");
                    $logStmt->execute([
                        $student['srcode'],
                        'Student account reactivated through login',
                        $ip_address,
                        $current_time
                    ]);
                }

                // Log the login attempt
                $logStmt = $pdo->prepare("
                    INSERT INTO activity_logs 
                    (srcode, action, action_details, ip_address, created_at) 
                    VALUES (?, 'LOGIN', ?, ?, ?)
                ");
                $logStmt->execute([
                    $student['srcode'],
                    'Student login successful',
                    $ip_address,
                    $current_time
                ]);

                // Create session log
                $sessionStmt = $pdo->prepare("
                    INSERT INTO session_logs 
                    (srcode, login_time, ip_address, session_status) 
                    VALUES (?, ?, ?, 'active')
                ");
                $sessionStmt->execute([
                    $student['srcode'],
                    $current_time,
                    $ip_address
                ]);

                // Store session_id for logout tracking
                $_SESSION['session_log_id'] = $pdo->lastInsertId();
                
                // Rest of student login code...
                $_SESSION['user_id'] = $student['srcode'];
                $_SESSION['firstname'] = $student['firstname'];
                $_SESSION['role'] = 'student';
                
                // Check mood log
                $today = date('Y-m-d');
                $moodCheckStmt = $pdo->prepare("SELECT COUNT(*) as mood_count FROM moodlog WHERE srcode = ? AND DATE(log_date) = ?");
                $moodCheckStmt->execute([$student['srcode'], $today]);
                $moodCheck = $moodCheckStmt->fetch();
                
                $response = [
                    'status' => 'success',
                    'title' => 'Hello ' . $student['firstname'] . '!',
                    'message' => 'Welcome back to Space',
                    'role' => 'student',
                    'redirect' => '/pages/student/moodlog.php'
                ];
                
                echo json_encode($response);
                exit;
            }
        }

        // Check admins
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            $hashedPassword = hash('sha256', $password);
            if ($hashedPassword === $admin['password']) {
                // Log admin login
                $logStmt = $pdo->prepare("
                    INSERT INTO activity_logs 
                    (admin_id, action, action_details, ip_address, created_at) 
                    VALUES (?, 'LOGIN', ?, ?, ?)
                ");
                $logStmt->execute([
                    $admin['admin_id'],
                    'Admin login successful',
                    $ip_address,
                    $current_time
                ]);

                // Create session log
                $sessionStmt = $pdo->prepare("
                    INSERT INTO session_logs 
                    (admin_id, login_time, ip_address, session_status) 
                    VALUES (?, ?, ?, 'active')
                ");
                $sessionStmt->execute([
                    $admin['admin_id'],
                    $current_time,
                    $ip_address
                ]);

                $_SESSION['session_log_id'] = $pdo->lastInsertId();
                $_SESSION['user_id'] = $admin['admin_id'];
                $_SESSION['role'] = $admin['role'];
                
                $response = [
                    'status' => 'success',
                    'title' => 'Hello Administrator!',
                    'message' => 'Welcome back to Space!',
                    'role' => $admin['role'],
                    'redirect' => '/pages/admin/admin.php'
                ];
                
                echo json_encode($response);
                exit;
            }
        }

        // Check therapists
        $stmt = $pdo->prepare("SELECT * FROM therapists WHERE email = ?");
        $stmt->execute([$email]);
        $therapist = $stmt->fetch();
        
        if ($therapist) {
            $hashedPassword = hash('sha256', $password);
            if ($hashedPassword === $therapist['password']) {
                // Log therapist login
                $logStmt = $pdo->prepare("
                    INSERT INTO activity_logs 
                    (therapist_id, action, action_details, ip_address, created_at) 
                    VALUES (?, 'LOGIN', ?, ?, ?)
                ");
                $logStmt->execute([
                    $therapist['therapist_id'],
                    'Therapist login successful',
                    $ip_address,
                    $current_time
                ]);

                // Create session log
                $sessionStmt = $pdo->prepare("
                    INSERT INTO session_logs 
                    (therapist_id, login_time, ip_address, session_status) 
                    VALUES (?, ?, ?, 'active')
                ");
                $sessionStmt->execute([
                    $therapist['therapist_id'],
                    $current_time,
                    $ip_address
                ]);

                $_SESSION['session_log_id'] = $pdo->lastInsertId();
                $_SESSION['user_id'] = $therapist['therapist_id'];
                $_SESSION['role'] = 'therapist';
                
                $response = [
                    'status' => 'success',
                    'title' => 'Hello Therapist!',
                    'message' => 'Welcome back to Space!',
                    'role' => 'therapist',
                    'redirect' => '/pages/therapist/therapist.php'
                ];
                
                echo json_encode($response);
                exit;
            }
        }

        // Log failed login attempt
        $logStmt = $pdo->prepare("
            INSERT INTO activity_logs 
            (action, action_details, ip_address, created_at) 
            VALUES ('LOGIN_FAILED', ?, ?, ?)
        ");
        $logStmt->execute([
            'Failed login attempt for email: ' . $email,
            $ip_address,
            $current_time
        ]);

        // If no match found
        $response['status'] = 'error';
        $response['message'] = 'Invalid email or password';
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $response['status'] = 'error';
        $response['message'] = 'A database error occurred';
    }
    
    echo json_encode($response);
    exit;
}
?>