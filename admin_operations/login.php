<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../configs/config.php';
require_once 'SessionLogger.php';
require_once 'Logger.php';

// Login attempts check functions
function checkLoginAttempts($email, $ip) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts 
                          WHERE (email = ? OR ip_address = ?) 
                          AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->execute([$email, $ip]);
    $attempts = $stmt->fetchColumn();
    
    if ($attempts >= 5) {
        return false;
    }
    return true;
}

function recordFailedAttempt($email) {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $pdo->prepare("INSERT INTO login_attempts (email, attempt_time, ip_address) 
                          VALUES (?, NOW(), ?)");
    $stmt->execute([$email, $ip]);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    
    $response = array('status' => 'error', 'message' => '', 'role' => '', 'redirect' => '');
    
    // Check for too many login attempts
    if (!checkLoginAttempts($_POST['email'], $_SERVER['REMOTE_ADDR'])) {
        $response = [
            'status' => 'error',
            'title' => 'Too Many Attempts',
            'message' => 'Please try again after 15 minutes',
            'icon' => 'error'
        ];
        echo json_encode($response);
        exit;
    }

    // Check for empty POST data
    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        $response['message'] = 'Email and password are required';
        echo json_encode($response);
        exit;
    }

    $sessionLogger = new SessionLogger($pdo);
    $logger = new Logger($pdo);

    try {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        
        // Add validation for empty values
        if (empty($email) || empty($password)) {
            $response['message'] = 'Email and password cannot be empty';
            echo json_encode($response);
            exit;
        }
        
        // Check students table first
        $stmt = $pdo->prepare("SELECT srcode, password, firstname, lastname, status FROM students WHERE email = ?");
        $stmt->execute([$email]);
        $student = $stmt->fetch();
        
        if ($student && hash('sha256', $password) === $student['password']) {
            // Check if account is suspended
            if ($student['status'] === 'suspended') {
                recordFailedAttempt($email); // Record failed attempt for suspended account
                $response = [
                    'status' => 'error',
                    'title' => 'Account Suspended',
                    'message' => 'Your account has been suspended. Please contact the administrator for assistance.',
                    'icon' => 'warning'
                ];
                echo json_encode($response);
                exit;
            }

            // If not suspended, proceed with normal login
            $_SESSION['user_id'] = $student['srcode'];
            $_SESSION['srcode'] = $student['srcode'];
            $_SESSION['firstname'] = $student['firstname'];
            $_SESSION['lastname'] = $student['lastname'];
            $_SESSION['role'] = 'student';
            $_SESSION['first_login_today'] = true;
            
            // Check if account needs reactivation
            if ($student['status'] === 'deactivated') {
                try {
                    $pdo->beginTransaction();
                    
                    $reactivateStmt = $pdo->prepare("UPDATE students SET status = 'active' WHERE srcode = ?");
                    $reactivateStmt->execute([$student['srcode']]);
                    
                    $logger->logActivity([
                        'srcode' => $student['srcode'],
                        'therapist_id' => null,
                        'admin_id' => null,
                        'action' => 'Account Reactivation',
                        'action_details' => 'Account was automatically reactivated upon login',
                        'ip_address' => $_SERVER['REMOTE_ADDR']
                    ]);
                    
                    $notifStmt = $pdo->prepare("
                        INSERT INTO notifications 
                        (user_id, type, title, message, created_at) 
                        VALUES 
                        (?, 'account', 'Account Reactivated', 'Your account has been successfully reactivated.', NOW())
                    ");
                    $notifStmt->execute([$student['srcode']]);
                    
                    $pdo->commit();
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log("Reactivation error: " . $e->getMessage());
                }
            }

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
                'redirect' => '../pages/student/moodlog.php'
            ];
            
            echo json_encode($response);
            exit;
        }

        // Check admins
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        
        if ($admin && hash('sha256', $password) === $admin['password']) {
            $_SESSION['user_id'] = $admin['admin_id'];
            $_SESSION['firstname'] = $admin['firstname'];
            $_SESSION['lastname'] = $admin['lastname'];
            $_SESSION['email'] = $admin['email'];
            $_SESSION['role'] = 'admin';

            try {
                $stmt = $pdo->prepare("INSERT INTO admin_session_logs 
                    (admin_id, login_time, ip_address, session_status) 
                    VALUES (?, NOW(), ?, 'active')");
                $stmt->execute([$admin['admin_id'], $_SERVER['REMOTE_ADDR']]);
            } catch (PDOException $e) {
                error_log("Admin session log error: " . $e->getMessage());
            }

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
                'redirect' => '../pages/admin/admin.php'
            ];
            
            echo json_encode($response);
            exit;
        }

        // Check therapists
        $stmt = $pdo->prepare("SELECT * FROM therapists WHERE email = ?");
        $stmt->execute([$email]);
        $therapist = $stmt->fetch();
        
        if ($therapist && hash('sha256', $password) === $therapist['password']) {
            if ($therapist['status'] === 'inactive') {
                recordFailedAttempt($email); // Record failed attempt for inactive account
                $response = [
                    'status' => 'error',
                    'title' => 'Account Inactive',
                    'message' => 'Your account is inactive. Please contact the administrator.',
                    'icon' => 'warning'
                ];
                echo json_encode($response);
                exit;
            }
        
            $_SESSION['user_id'] = $therapist['therapist_id'];
            $_SESSION['firstname'] = $therapist['firstname'];
            $_SESSION['lastname'] = $therapist['lastname'];
            $_SESSION['email'] = $therapist['email'];
            $_SESSION['role'] = 'therapist';
        
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
                'redirect' => '../pages/therapist/therapist.php'
            ];
            
            echo json_encode($response);
            exit;
        }

        // If no match found
        recordFailedAttempt($email); // Record failed attempt for invalid credentials
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