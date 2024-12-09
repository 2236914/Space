<?php
session_start();
require_once '../configs/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = array('status' => 'error', 'message' => '');

    try {
        // Get and sanitize input data
        $srcode = filter_var($_POST['srcode'], FILTER_SANITIZE_NUMBER_INT);
        $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
        $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);
        $phonenum = filter_var($_POST['phonenum'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        
        // Generate username from email (everything before @)
        $username = strstr($email, '@', true);

        // Check for existing srcode
        $stmt = $pdo->prepare("SELECT srcode FROM students WHERE srcode = ?");
        $stmt->execute([$srcode]);
        if ($stmt->rowCount() > 0) {
            $response['message'] = 'SR-Code already exists!';
            echo json_encode($response);
            exit;
        }

        // Check for existing phone number
        $stmt = $pdo->prepare("SELECT phonenum FROM students WHERE phonenum = ?");
        $stmt->execute([$phonenum]);
        if ($stmt->rowCount() > 0) {
            $response['message'] = 'Phone number already registered!';
            echo json_encode($response);
            exit;
        }

        // Check for existing email
        $stmt = $pdo->prepare("SELECT email FROM students WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $response['message'] = 'Email already registered!';
            echo json_encode($response);
            exit;
        }

        // Hash password
        $hashedPassword = hash('sha256', $password);

        // Prepare INSERT statement
        $stmt = $pdo->prepare("
            INSERT INTO students (srcode, firstname, lastname, phonenum, email, password, username) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        // Execute with parameters
        $stmt->execute([
            $srcode,
            $firstname,
            $lastname,
            $phonenum,
            $email,
            $hashedPassword,
            $username
        ]);

        $response['status'] = 'success';
        $response['message'] = 'Registration successful!';
  
    } catch (PDOException $e) {
        $response['message'] = 'Registration failed: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit;
}
?>