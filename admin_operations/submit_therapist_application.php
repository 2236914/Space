<?php
// Error logging configuration
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/php/logs/php_error_log');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and include required files
session_start();
require_once '../configs/config.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$response = ['status' => 'error', 'message' => 'Unknown error occurred'];

try {
    // Debug database connection
    $pdo->query('SELECT 1');
    error_log("Database connection successful");

    // Log incoming data
    error_log("POST data received: " . print_r($_POST, true));
    error_log("FILES data received: " . print_r($_FILES, true));

    // Validate required fields
    $requiredFields = ['firstName', 'lastName', 'email', 'phone', 'licenseNumber', 'specialization', 'experience'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Database insertion
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO therapist_applications (
            first_name,
            last_name,
            email,
            phone,
            license_number,
            specialization,
            experience,
            license_file,
            license_file_type,
            resume,
            resume_file_type,
            profile_picture,
            profile_picture_type,
            application_date
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
        )
    ");

    // Handle file data
    $profilePicture = isset($_FILES['profilePicture']) ? file_get_contents($_FILES['profilePicture']['tmp_name']) : null;
    $licenseFile = isset($_FILES['licenseFile']) ? file_get_contents($_FILES['licenseFile']['tmp_name']) : null;
    $resume = isset($_FILES['resume']) ? file_get_contents($_FILES['resume']['tmp_name']) : null;

    $result = $stmt->execute([
        $_POST['firstName'],
        $_POST['lastName'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['licenseNumber'],
        $_POST['specialization'],
        $_POST['experience'],
        $licenseFile,
        $_FILES['licenseFile']['type'] ?? null,
        $resume,
        $_FILES['resume']['type'] ?? null,
        $profilePicture,
        $_FILES['profilePicture']['type'] ?? null
    ]);

    if (!$result) {
        throw new Exception("Database error: " . implode(", ", $stmt->errorInfo()));
    }

    $pdo->commit();

    // Email notification (existing code)
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'space.creotech@gmail.com';
        $mail->Password = 'ocqlnjhicvemknon'; // Use your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('space.creotech@gmail.com', 'Space Website');
        $mail->addAddress('space.creotech@gmail.com', 'Space Admin');

        $mail->isHTML(true);
        $mail->Subject = 'New Therapist Application';
        $mail->Body    = "
            <h2>New Therapist Application</h2>
            <p><strong>Name:</strong> {$_POST['firstName']} {$_POST['lastName']}</p>
            <p><strong>Email:</strong> {$_POST['email']}</p>
            <p><strong>Phone:</strong> {$_POST['phone']}</p>
            <p><strong>License Number:</strong> {$_POST['licenseNumber']}</p>
            <p><strong>Specialization:</strong> {$_POST['specialization']}</p>
            <p><strong>Experience:</strong></p>
            <p>{$_POST['experience']}</p>
            <p>Please log in to the admin panel to review this application.</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        // Don't throw an exception here, as the application was still submitted successfully
    }

    $response['status'] = 'success';
    $response['message'] = 'Application submitted successfully!';

} catch (Exception $e) {
    error_log("Error in therapist application: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

// Clear any output buffers and send response
while (ob_get_level()) {
    ob_end_clean();
}

echo json_encode($response);
exit;
?>
