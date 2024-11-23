<?php
session_start();
require_once '../configs/config.php';
require_once 'log_helper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = array('status' => 'error', 'message' => '', 'role' => '', 'redirect' => '');

    try {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $hashedPassword = hash('sha256', $password);
        
        // Check students table first
        $stmt = $pdo->prepare("SELECT srcode, firstname, lastname, email, status FROM students WHERE email = ? AND password = ? AND status = 'active'");
        $stmt->execute([$email, $hashedPassword]);
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            $_SESSION['user_id'] = $user['srcode'];
            $_SESSION['role'] = 'student';
            $_SESSION['firstname'] = $user['firstname'];
            $_SESSION['lastname'] = $user['lastname'];
            
            // Log the login activity
            logActivity($pdo, [
                'srcode' => $user['srcode'],
                'action' => 'Login',
                'action_details' => 'Student logged in successfully'
            ]);
            
            // Log the session
            logSession($pdo, [
                'srcode' => $user['srcode']
            ]);

            // Check if student has logged mood today
            $today = date('Y-m-d');
            $moodCheckStmt = $pdo->prepare("
                SELECT COUNT(*) as mood_count 
                FROM moodlog 
                WHERE srcode = ? 
                AND DATE(log_date) = ?
            ");
            $moodCheckStmt->execute([$user['srcode'], $today]);
            $moodCheck = $moodCheckStmt->fetch();
            
            $response['status'] = 'success';
            $response['message'] = 'Welcome back, Student!';
            $response['role'] = 'student';
            
            if ($moodCheck['mood_count'] == 0) {
                // No mood logged today, redirect to moodlog.php
                $response['redirect'] = '../pages/student/moodlog.php';
            } else {
                // Mood already logged, redirect to dashboard
                $response['redirect'] = '../pages/student/student.php';
            }
            
            echo json_encode($response);
            exit;
        }

        // First check admin/superadmin table
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? AND password = ?");
        $stmt->execute([$email, $hashedPassword]);
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role']; // 'admin' or 'superadmin'
            $_SESSION['redirect_path'] = '../pages/admin/admin.php';
            $response['status'] = 'success';
            $response['message'] = 'Welcome back, Administrator!';
            $response['role'] = $user['role'];
            echo json_encode($response);
            exit;
        }

        // Check therapist table
        $stmt = $pdo->prepare("SELECT * FROM therapists WHERE email = ? AND password = ?");
        $stmt->execute([$email, $hashedPassword]);
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'therapist';
            $_SESSION['redirect_path'] = '../pages/therapist/therapist.php';
            $response['status'] = 'success';
            $response['message'] = 'Welcome back, Therapist!';
            $response['role'] = 'therapist';
            echo json_encode($response);
            exit;
        }

        // If no match found
        $response['message'] = 'Invalid email or password';
        echo json_encode($response);
        exit;

    } catch (PDOException $e) {
        $response['message'] = 'Login failed: ' . $e->getMessage();
        echo json_encode($response);
        exit;
    }
}
?>