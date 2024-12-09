<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class TherapyApplicationHelper {
    public static function sendApplicationStatusEmail($application_id) {
        try {
            global $pdo;
            
            // Get application details
            $query = "SELECT * FROM therapist_applications 
                     WHERE id = ?";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([$application_id]);
            $application = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$application) {
                throw new Exception("Application not found");
            }

            // Only send email for approved or rejected status
            if (!in_array($application['status'], ['approved', 'rejected'])) {
                return;
            }

            $mail = new PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'space.creotech@gmail.com';
            $mail->Password = 'qwiqelaivjigouqz';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('space.creotech@gmail.com', 'SPACE Website');
            $mail->addAddress($application['email']);
            $mail->addCC('space.creotech@gmail.com'); // Admin copy

            // Email content
            $mail->isHTML(true);
            $mail->Subject = "SPACE: Therapist Application " . strtoupper($application['status']);

            // Create email body based on status
            if ($application['status'] === 'approved') {
                $email_body = "
                    <h2>Congratulations!</h2>
                    <p>Dear {$application['first_name']} {$application['last_name']},</p>
                    <p>We are pleased to inform you that your application to join SPACE as a therapist has been approved.</p>
                    <p><strong>Next Steps:</strong></p>
                    <ul>
                        <li>Complete your profile setup</li>
                        <li>Review our guidelines and policies</li>
                        <li>Schedule your orientation</li>
                    </ul>
                    <p>Our team will contact you shortly with further instructions.</p>
                ";
            } else {
                $email_body = "
                    <h2>Application Status Update</h2>
                    <p>Dear {$application['first_name']} {$application['last_name']},</p>
                    <p>Thank you for your interest in joining SPACE as a therapist.</p>
                    <p>After careful review of your application, we regret to inform you that we are unable to proceed with your application at this time.</p>
                    <p>Review Notes: {$application['review_notes']}</p>
                    <p>We appreciate your interest and wish you the best in your future endeavors.</p>
                ";
            }

            $mail->Body = $email_body;

            $mail->send();
            error_log("Application status email sent successfully to: " . $application['email']);

            return [
                'success' => true,
                'message' => 'Status update email sent successfully'
            ];

        } catch (Exception $e) {
            error_log("Application Email Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
} 