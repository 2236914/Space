<?php
// Error logging configuration
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/php/logs/php_error_log');
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../configs/config.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$response = ['status' => 'error', 'message' => 'Unknown error occurred'];

try {
    // Validate required fields
    $requiredFields = ['firstName', 'lastName', 'email', 'phone', 'licenseNumber', 'specialization', 'experience'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate file uploads
    if (!isset($_FILES['licenseFile']) || $_FILES['licenseFile']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Professional license file is required');
    }
    if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Resume/CV is required');
    }

    // Validate file types
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!in_array($_FILES['licenseFile']['type'], $allowedTypes)) {
        throw new Exception('License file must be PDF or DOC/DOCX');
    }
    if (!in_array($_FILES['resume']['type'], $allowedTypes)) {
        throw new Exception('Resume must be PDF or DOC/DOCX');
    }

    // Get file contents
    $licenseFile = file_get_contents($_FILES['licenseFile']['tmp_name']);
    if ($licenseFile === false) {
        throw new Exception('Failed to read license file');
    }

    $resume = file_get_contents($_FILES['resume']['tmp_name']);
    if ($resume === false) {
        throw new Exception('Failed to read resume file');
    }

    // Optional profile picture
    $profilePicture = null;
    $profilePictureType = null;
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['profilePicture']['type'], $allowedImageTypes)) {
            throw new Exception('Profile picture must be JPG, PNG, or GIF');
        }
        $profilePicture = file_get_contents($_FILES['profilePicture']['tmp_name']);
        $profilePictureType = $_FILES['profilePicture']['type'];
    }

    // Database insertion
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO therapist_applications (
            first_name, last_name, email, phone, license_number, 
            specialization, experience, license_file, license_file_type,
            resume, resume_file_type, profile_picture, profile_picture_type,
            application_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $result = $stmt->execute([
        $_POST['firstName'],
        $_POST['lastName'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['licenseNumber'],
        $_POST['specialization'],
        $_POST['experience'],
        $licenseFile,
        $_FILES['licenseFile']['type'],
        $resume,
        $_FILES['resume']['type'],
        $profilePicture,
        $profilePictureType
    ]);

    if (!$result) {
        throw new Exception("Database error: " . implode(", ", $stmt->errorInfo()));
    }

    $pdo->commit();

    // Send email notification
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'space.creotech@gmail.com';
        $mail->Password = 'qwiqelaivjigouqz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('space.creotech@gmail.com', 'Space Website');
        $mail->addAddress('space.creotech@gmail.com', 'Space Admin');

        $mail->isHTML(true);
        $mail->Subject = 'New Therapist Application';
        $mail->Body = "
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
    }

    $response['status'] = 'success';
    $response['message'] = 'Application submitted successfully!';

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error in therapist application: " . $e->getMessage());
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>
