<?php
class SMSHelper {
    public static function sendStatusUpdateNotification($session_id) {
        try {
            global $pdo;
            
            // Get session details
            $query = "SELECT ts.session_date, ts.session_time, ts.status, ts.session_type,
                            CONCAT(s.firstname, ' ', s.lastname) as student_name
                     FROM therapy_sessions ts
                     JOIN students s ON ts.srcode = s.srcode
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

            // Format message with proper line breaks and shorter content
            $message = "SPACE: Therapy Session\n\n";
            $message .= "Status: " . strtoupper($session['status']) . "\n";
            $message .= "Date: {$date}\n";
            $message .= "Time: {$time}\n";
            $message .= "Student: {$session['student_name']}\n";
            $message .= "Type: {$session['session_type']}\n\n";
            
            // Only add meeting link for confirmed online sessions
            if ($session['status'] === 'confirmed' && $session['session_type'] === 'online') {
                $message .= "Join: https://meet.google.com/xyz-abcd-jkl";
            }

            // Format phone number
            $phone_number = '639939637684';
            if (substr($phone_number, 0, 2) !== '63') {
                $phone_number = '63' . ltrim($phone_number, '0');
            }

            $body = array(
                'messages' => array(
                    array(
                        'destinations' => array(
                            array('to' => $phone_number)
                        ),
                        'from' => '447491163443',
                        'text' => $message,
                        // Add these parameters
                        'validityPeriod' => 720, // 12 hours
                        'flash' => false,
                        'transliteration' => 'UNICODE'
                    )
                )
            );

            // Use cURL instead of HTTP_Request2
            $curl = curl_init();
            
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.infobip.com/sms/2/text/advanced',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: App aac8d9570753ad1481fdd92e68f74db7-12573a69-b8b6-4954-ae29-6245f68cd555',
                    'Content-Type: application/json',
                    'Accept: application/json'
                ),
            ));

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            if (curl_errno($curl)) {
                error_log('Curl error: ' . curl_error($curl));
                error_log('Curl error number: ' . curl_errno($curl));
            }
            
            error_log('SMS Request body: ' . json_encode($body));
            error_log('SMS Response: ' . $response);
            error_log('SMS HTTP Code: ' . $httpCode);

            curl_close($curl);

            if ($httpCode == 200) {
                error_log("SMS sent successfully: " . $response);
                return ['success' => true];
            } else {
                error_log("SMS failed with status: " . $httpCode . ", Response: " . $response);
                return ['success' => false, 'error' => 'HTTP Error: ' . $httpCode];
            }

        } catch (Exception $e) {
            error_log("SMS Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function sendPendingBookingNotification($phone_number, $booking_details) {
        try {
            // Create message
            $message = "SPACE: New Therapy Session Booking\n";
            $message .= "Date: {$booking_details['date']}\n";
            $message .= "Time: {$booking_details['time']}\n";
            $message .= "Student: {$booking_details['student_id']}\n";
            $message .= "Therapist: {$booking_details['therapist_name']}\n";
            $message .= "Status: PENDING\n";
            $message .= "Please wait for therapist confirmation.";

            // Use cURL to send message
            $curl = curl_init();
            
            $body = array(
                'messages' => array(
                    array(
                        'destinations' => array(
                            array('to' => $phone_number)
                        ),
                        'from' => '447491163443',
                        'text' => $message
                    )
                )
            );

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.infobip.com/sms/2/text/advanced',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: App aac8d9570753ad1481fdd92e68f74db7-12573a69-b8b6-4954-ae29-6245f68cd555',
                    'Content-Type: application/json',
                    'Accept: application/json'
                ),
            ));

            // Add detailed error logging
            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            if (curl_errno($curl)) {
                error_log('Curl error: ' . curl_error($curl));
                error_log('Curl error number: ' . curl_errno($curl));
            }
            
            error_log('SMS Request body: ' . json_encode($body));
            error_log('SMS Response: ' . $response);
            error_log('SMS HTTP Code: ' . $httpCode);

            curl_close($curl);

            if ($httpCode == 200) {
                error_log("SMS sent successfully: " . $response);
                return ['success' => true];
            } else {
                error_log("SMS failed with status: " . $httpCode . ", Response: " . $response);
                return ['success' => false, 'error' => 'HTTP Error: ' . $httpCode];
            }

        } catch (Exception $e) {
            error_log("SMS Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

// Add trigger to automatically send SMS when status is updated
try {
    if (isset($data['session_id']) && isset($data['status'])) {
        if (in_array(strtolower($data['status']), ['confirmed', 'cancelled'])) {
            SMSHelper::sendStatusUpdateNotification($data['session_id']);
        }
    }
} catch (Exception $e) {
    error_log("Auto SMS Trigger Error: " . $e->getMessage());
}