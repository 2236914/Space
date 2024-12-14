<?php
require_once '../configs/config.php';
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Log received data
    error_log("Received POST data: " . print_r($_POST, true));
    error_log("Received FILES data: " . print_r($_FILES, true));

    // Validate required fields
    $required_fields = ['firstname', 'lastname', 'email', 'srcode', 'phonenum', 'password', 'course'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        throw new Exception("Missing required fields: " . implode(", ", $missing_fields));
    }

    // Validate phone number
    if (!preg_match('/^09\d{9}$/', $_POST['phonenum'])) {
        throw new Exception("Invalid phone number format");
    }

    // Validate email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Hash password
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Database insertion with only required fields
    $query = "INSERT INTO students (
        srcode, firstname, lastname, email, phonenum,
        course, password
    ) VALUES (
        :srcode, :firstname, :lastname, :email, :phonenum,
        :course, :password
    )";
    
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        'srcode' => $_POST['srcode'],
        'firstname' => $_POST['firstname'],
        'lastname' => $_POST['lastname'],
        'email' => $_POST['email'],
        'phonenum' => $_POST['phonenum'],
        'course' => $_POST['course'],
        'password' => $hashed_password
    ]);

    if ($result) {
        // Handle profile picture upload if provided
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
            $upload_dir = '../uploads/profile_pictures/students/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $file_name = $_POST['srcode'] . '.' . $file_ext;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_dir . $file_name)) {
                // Profile picture uploaded successfully
            }
        }

        echo json_encode(['success' => true, 'message' => 'Student added successfully']);
    } else {
        throw new Exception("Failed to add student to database");
    }

} catch (PDOException $e) {
    error_log("Database Error in add_student.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred', 
        'debug' => $e->getMessage()  // Adding actual error message for debugging
    ]);
} catch (Exception $e) {
    error_log("Error in add_student.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
