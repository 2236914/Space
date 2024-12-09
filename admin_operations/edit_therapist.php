<?php
session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if password is being updated
        if (!empty($_POST['password'])) {
            $stmt = $pdo->prepare("
                UPDATE therapists 
                SET 
                    firstname = ?,
                    lastname = ?,
                    email = ?,
                    specialization = ?,
                    license_number = ?,
                    contact_number = ?,
                    dob = ?,
                    status = ?,
                    password = ?
                WHERE therapist_id = ?
            ");
            
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            $result = $stmt->execute([
                $_POST['firstname'],
                $_POST['lastname'],
                $_POST['email'],
                $_POST['specialization'],
                $_POST['license_number'],
                $_POST['contact_number'],
                $_POST['dob'],
                $_POST['status'],
                $hashed_password,
                $_POST['therapist_id']
            ]);
        } else {
            // Update without changing password
            $stmt = $pdo->prepare("
                UPDATE therapists 
                SET 
                    firstname = ?,
                    lastname = ?,
                    email = ?,
                    specialization = ?,
                    license_number = ?,
                    contact_number = ?,
                    dob = ?,
                    status = ?
                WHERE therapist_id = ?
            ");
            
            $result = $stmt->execute([
                $_POST['firstname'],
                $_POST['lastname'],
                $_POST['email'],
                $_POST['specialization'],
                $_POST['license_number'],
                $_POST['contact_number'],
                $_POST['dob'],
                $_POST['status'],
                $_POST['therapist_id']
            ]);
        }
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Therapist updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update therapist'
            ]);
        }
        
    } catch (PDOException $e) {
        $error_message = '';
        if (strpos($e->getMessage(), 'email') !== false) {
            $error_message = 'Email already exists';
        } elseif (strpos($e->getMessage(), 'license_number') !== false) {
            $error_message = 'License number already exists';
        } elseif (strpos($e->getMessage(), 'contact_number') !== false) {
            $error_message = 'Contact number already exists';
        } else {
            $error_message = 'An error occurred while updating the therapist';
        }
        echo json_encode([
            'success' => false,
            'message' => $error_message
        ]);
    }
}