<?php
session_start();
require_once '../configs/config.php';
require_once 'log_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array('status' => 'error', 'message' => '');

    try {
        // Validate input
        if (!isset($_POST['srcode'], $_POST['selected_emoji'], $_POST['mood_name'], $_POST['description'])) {
            throw new Exception('Missing required fields');
        }

        $srcode = filter_var($_POST['srcode'], FILTER_VALIDATE_INT);
        $selected_emojis = explode(',', $_POST['selected_emoji']);
        $mood_names = explode(',', $_POST['mood_name']);
        $description = $_POST['description'];

        // Validate emoji count
        if (count($selected_emojis) !== 5) {
            throw new Exception('Please select exactly 5 emojis');
        }

        // Insert into database
        $stmt = $pdo->prepare("
            INSERT INTO moodlog (srcode, selected_emoji, mood_name, description)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $srcode,
            $_POST['selected_emoji'], // Store as comma-separated string
            $_POST['mood_name'],      // Store as comma-separated string
            $description
        ]);

        // After successful mood save, log the activity
        logActivity($pdo, [
            'srcode' => $_SESSION['user_id'],
            'action' => 'Mood Log',
            'action_details' => "Student logged mood: {$_POST['mood_name']}"
        ]);

        $response['status'] = 'success';
        $response['message'] = 'Mood logged successfully';
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>