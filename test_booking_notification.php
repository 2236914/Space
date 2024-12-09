<?php
require_once __DIR__ . '/api/helpers/SMSHelper.php';

// Test data
$test_booking = [
    'date' => date('F j, Y'),
    'time' => '2:30 PM',
    'student_id' => '2020-00123',
    'student_phone' => '09267075697', // Your test number
    'therapist_name' => 'Dr. Smith',
    'cancel_reason' => 'Schedule conflict'
];

// Test all status notifications
$statuses = ['pending', 'confirmed', 'cancelled', 'completed'];

foreach ($statuses as $status) {
    echo "Testing $status notification:<br>";
    $result = SMSHelper::sendBookingNotification($test_booking, $status);
    echo "<pre>";
    print_r($result);
    echo "</pre><br>";
    sleep(2); // Wait 2 seconds between messages
} 