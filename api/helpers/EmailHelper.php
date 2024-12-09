<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailHelper {
    public static function sendStatusUpdateNotification($session_id) {
        try {
            global $pdo;
            
            // Get session details
            $query = "SELECT ts.session_date, ts.session_time, ts.status, ts.session_type,
                            CONCAT(s.firstname, ' ', s.lastname) as student_name,
                            s.email as student_email,
                            CONCAT(t.firstname, ' ', t.lastname) as therapist_name
                     FROM therapy_sessions ts
                     JOIN students s ON ts.srcode = s.srcode
                     JOIN therapists t ON ts.therapist_id = t.therapist_id
                     WHERE ts.session_id = ?";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([$session_id]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!in_array($session['status'], ['confirmed', 'cancelled'])) {
                return;
            }

            // Format date and time
            $date = date('M j, Y', strtotime($session['session_date']));
            $time = date('g:i A', strtotime($session['session_time']));

            // Initialize PHPMailer
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'space.creotech@gmail.com';
                $mail->Password = 'qwiqelaivjigouqz';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('space.creotech@gmail.com', 'SPACE Therapy');
                $mail->addAddress('space.creotech@gmail.com', 'SPACE Admin');

                $mail->isHTML(true);
                $mail->Subject = 'Therapy Session Status Update';
                
                // Create email body
                $mailBody = "
                    <h2>Therapy Session Update</h2>
                    <table style='border-collapse: collapse; width: 100%; max-width: 600px;'>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd;'><strong>Status:</strong></td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>" . strtoupper($session['status']) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd;'><strong>Date:</strong></td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>{$date}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd;'><strong>Time:</strong></td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>{$time}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd;'><strong>Student:</strong></td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>{$session['student_name']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd;'><strong>Therapist:</strong></td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>{$session['therapist_name']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; border: 1px solid #ddd;'><strong>Type:</strong></td>
                            <td style='padding: 8px; border: 1px solid #ddd;'>{$session['session_type']}</td>
                        </tr>
                    </table>";

                if ($session['status'] === 'confirmed' && $session['session_type'] === 'online') {
                    $mailBody .= "<p><strong>Join Link:</strong> <a href='https://meet.google.com/xyz-abcd-jkl'>Click here to join the session</a></p>";
                }

                $mail->Body = $mailBody;
                $mail->send();
                
                error_log("Email notification sent successfully");
                return ['success' => true];

            } catch (Exception $e) {
                error_log("Email Error: " . $mail->ErrorInfo);
                return ['success' => false, 'error' => $mail->ErrorInfo];
            }

        } catch (Exception $e) {
            error_log("Email Helper Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
} 