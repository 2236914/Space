<?php
session_start();
require_once '../configs/config.php';
require '../vendor/autoload.php';

header('Content-Type: application/json');

try {
    // Get form data
    $srcode = $_POST['srcode'];
    $phonenum = $_POST['phonenum'];
    $email = $_POST['email'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $password = hash('sha256', $_POST['password']);

    // Check if SR-Code exists
    $stmt = $pdo->prepare("SELECT srcode FROM students WHERE srcode = ?");
    $stmt->execute([$srcode]);
    if($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'exists', 'message' => 'This SR-Code is already registered.']);
        exit;
    }

    // Check if phone number exists
    $stmt = $pdo->prepare("SELECT phonenum FROM students WHERE phonenum = ?");
    $stmt->execute([$phonenum]);
    if($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'exists', 'message' => 'This phone number is already registered.']);
        exit;
    }

    // Check if email exists
    $stmt = $pdo->prepare("SELECT email FROM students WHERE email = ?");
    $stmt->execute([$email]);
    if($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'exists', 'message' => 'This G-Suite email is already registered.']);
        exit;
    }

    // Generate verification code and set expiration time (10 minutes from now)
    $verificationCode = sprintf("%06d", mt_rand(100000, 999999));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Delete any existing verification codes for this email
    $stmt = $pdo->prepare("DELETE FROM email_verifications WHERE email = ?");
    $stmt->execute([$email]);

    // Insert new verification code
    $stmt = $pdo->prepare("INSERT INTO email_verifications (email, verification_code, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$email, $verificationCode, $expiresAt]);
    
    // Store registration data in session
    $_SESSION['temp_registration'] = [
        'srcode' => $srcode,
        'phonenum' => $phonenum,
        'email' => $email,
        'firstname' => $firstname,
        'lastname' => $lastname,
        'password' => $password
    ];

    // Send verification email using PHPMailer
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'space.creotech@gmail.com'; // Space Gmail
        $mail->Password = 'qwiqelaivjigouqz'; // App Password
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('space.creotech@gmail.com', 'Space Admin');
        $mail->addAddress($email); // This will send to the G-Suite email provided in signup

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification - Space';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #333;'>Email Verification</h2>
                <p>Hello $firstname,</p>
                <p>Thank you for registering with Space. To complete your registration, please use the following verification code:</p>
                <div style='background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; letter-spacing: 5px; margin: 20px 0;'>
                    <strong>$verificationCode</strong>
                </div>
                <p>This code will expire in 10 minutes.</p>
                <p>If you didn't request this verification, please ignore this email.</p>
                <p>Best regards,<br>Space Team</p>
            </div>
        ";

        $mail->send();
        echo json_encode(['status' => 'unique', 'message' => 'Verification code has been sent to your email.']);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred during registration: ' . $e->getMessage()]);
}
?> 