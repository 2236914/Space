<?php
header('Content-Type: application/json');
require_once '../configs/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate srcode (only required field)
        if (empty($_POST['srcode'])) {
            throw new Exception("Student ID is required");
        }

        // Get current student data
        $stmt = $pdo->prepare("SELECT * FROM students WHERE srcode = ?");
        $stmt->execute([$_POST['srcode']]);
        $currentData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentData) {
            throw new Exception("Student not found");
        }

        // Build update query dynamically based on provided fields
        $updateFields = [];
        $params = ['srcode' => $_POST['srcode']]; // Always include srcode

        $fields = [
            'firstname', 'lastname', 'phonenum', 'email',
            'department', 'year', 'section', 'course', 'status'
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field]) && !empty($_POST[$field])) {
                // Validate phone number if it's being updated
                if ($field === 'phonenum' && !preg_match('/^09\d{9}$/', $_POST[$field])) {
                    throw new Exception("Invalid phone number format");
                }

                // Validate email uniqueness if it's being updated
                if ($field === 'email' && $_POST[$field] !== $currentData['email']) {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE email = ? AND srcode != ?");
                    $stmt->execute([$_POST[$field], $_POST['srcode']]);
                    if ($stmt->fetchColumn() > 0) {
                        throw new Exception("Email already exists for another student");
                    }
                }

                // Validate phone number uniqueness if it's being updated
                if ($field === 'phonenum' && $_POST[$field] !== $currentData['phonenum']) {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE phonenum = ? AND srcode != ?");
                    $stmt->execute([$_POST[$field], $_POST['srcode']]);
                    if ($stmt->fetchColumn() > 0) {
                        throw new Exception("Phone number already exists for another student");
                    }
                }

                $updateFields[] = "$field = :$field";
                $params[$field] = $_POST[$field];
            }
        }

        // Only update if there are fields to update
        if (!empty($updateFields)) {
            $sql = "UPDATE students SET " . implode(", ", $updateFields) . " WHERE srcode = :srcode";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Student updated successfully'
                ]);
            } else {
                throw new Exception("Failed to update student");
            }
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'No changes to update'
            ]);
        }

    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred. Please try again.'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
} 