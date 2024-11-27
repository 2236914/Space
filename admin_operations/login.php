<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../configs/config.php';
require_once 'SessionLogger.php';
require_once 'Logger.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = array('status' => 'error', 'message' => '', 'role' => '', 'redirect' => '');
    $sessionLogger = new SessionLogger($pdo);
    $logger = new Logger($pdo);

    try {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        
        // Check students table first
        $stmt = $pdo->prepare("SELECT srcode, password, firstname, lastname, status FROM students WHERE email = ?");
        $stmt->execute([$email]);
        $student = $stmt->fetch();
        
        if ($student && hash('sha256', $password) === $student['password']) {
            // Store student session variables
            $_SESSION['user_id'] = $student['srcode'];
            $_SESSION['srcode'] = $student['srcode'];
            $_SESSION['firstname'] = $student['firstname'];
            $_SESSION['lastname'] = $student['lastname'];
            $_SESSION['role'] = 'student';
            
            // Check if account needs reactivation
            if ($student['status'] === 'deactivated') {
                try {
                    // Begin transaction
                    $pdo->beginTransaction();
                    
                    // Update student status
                    $reactivateStmt = $pdo->prepare("UPDATE students SET status = 'active' WHERE srcode = ?");
                    $reactivateStmt->execute([$student['srcode']]);
                    
                    // Log reactivation with more detailed information
                    $logger->logActivity([
                        'srcode' => $student['srcode'],
                        'therapist_id' => null,
                        'admin_id' => null,
                        'action' => 'Account Reactivation',  // Make this more readable
                        'action_details' => 'Account was automatically reactivated upon login',
                        'ip_address' => $_SERVER['REMOTE_ADDR']
                    ]);
                    
                    // Add a notification for the reactivation
                    $notifStmt = $pdo->prepare("
                        INSERT INTO notifications 
                        (user_id, type, title, message, created_at) 
                        VALUES 
                        (?, 'account', 'Account Reactivated', 'Your account has been successfully reactivated.', NOW())
                    ");
                    $notifStmt->execute([$student['srcode']]);
                    
                    $pdo->commit();
                    
                    // Log success for debugging
                    error_log("Account reactivation successful for srcode: " . $student['srcode']);
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log("Reactivation error: " . $e->getMessage());
                    // Continue with login even if logging fails
                }
            }

            // Log session and activity
            $sessionId = $sessionLogger->logUserSession('student', $student['srcode'], 'login');
            $_SESSION['session_log_id'] = $sessionId;
            
            $logger->logActivity([
                'srcode' => $student['srcode'],
                'therapist_id' => null,
                'admin_id' => null,
                'action' => 'LOGIN',
                'action_details' => 'Student login successful',
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);

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

        // Check admins
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        
        if ($admin && hash('sha256', $password) === $admin['password']) {
            // Store admin session variables
            $_SESSION['user_id'] = $admin['admin_id'];
            $_SESSION['firstname'] = $admin['firstname'];
            $_SESSION['lastname'] = $admin['lastname'];
            $_SESSION['email'] = $admin['email'];
            $_SESSION['role'] = 'admin';

            // Log session and activity
            $sessionId = $sessionLogger->logUserSession('admin', $admin['admin_id'], 'login');
            $_SESSION['session_log_id'] = $sessionId;
            
            $logger->logActivity([
                'srcode' => null,
                'therapist_id' => null,
                'admin_id' => $admin['admin_id'],
                'action' => 'LOGIN',
                'action_details' => 'Admin login successful',
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);

            $response = [
                'status' => 'success',
                'title' => 'Hello ' . $admin['firstname'] . '!',
                'message' => 'Welcome back to Space!',
                'role' => 'admin',
                'redirect' => '/pages/admin/admin.php'
            ];
            
            echo json_encode($response);
            exit;
        }

        // Check therapists
        $stmt = $pdo->prepare("SELECT * FROM therapists WHERE email = ?");
        $stmt->execute([$email]);
        $therapist = $stmt->fetch();
        
        if ($therapist && hash('sha256', $password) === $therapist['password']) {
            // Store therapist session variables
            $_SESSION['user_id'] = $therapist['therapist_id'];
            $_SESSION['firstname'] = $therapist['firstname'];
            $_SESSION['lastname'] = $therapist['lastname'];
            $_SESSION['email'] = $therapist['email'];
            $_SESSION['role'] = 'therapist';

            // Log session and activity
            $sessionId = $sessionLogger->logUserSession('therapist', $therapist['therapist_id'], 'login');
            $_SESSION['session_log_id'] = $sessionId;
            
            $logger->logActivity([
                'srcode' => null,
                'therapist_id' => $therapist['therapist_id'],
                'admin_id' => null,
                'action' => 'LOGIN',
                'action_details' => 'Therapist login successful',
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);

            $response = [
                'status' => 'success',
                'title' => 'Hello ' . $therapist['firstname'] . '!',
                'message' => 'Welcome back to Space!',
                'role' => 'therapist',
                'redirect' => '/pages/therapist/therapist.php'
            ];
            
            echo json_encode($response);
            exit;
        }

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