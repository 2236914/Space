<?php
// Start the session at the very top of the file
session_start();

// Include the database connection
include '../configs/config.php'; // Adjust the path as necessary

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input data
    $studentId = intval($_POST['studentId']); // Ensure it's an integer
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $phoneNumber = trim($_POST['phoneNumber']);
    $department = trim($_POST['department']);
    $course = trim($_POST['course']);
    $section = trim($_POST['section']);
    
    // Prepare the SQL update query
    $update_query = "UPDATE students SET firstName = :firstName, lastName = :lastName, email = :email, 
                     phoneNumber = :phoneNumber, department = :department, course = :course, section = :section 
                     WHERE student_id = :studentId";
    
    // Prepare the statement
    $stmt = $pdo->prepare($update_query);
    
    // Bind parameters
    $stmt->bindParam(':firstName', $firstName);
    $stmt->bindParam(':lastName', $lastName);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phoneNumber', $phoneNumber);
    $stmt->bindParam(':department', $department);
    $stmt->bindParam(':course', $course);
    $stmt->bindParam(':section', $section);
    $stmt->bindParam(':studentId', $studentId);

    // Execute the query
    if ($stmt->execute()) {
        // Set a session variable for success
        $_SESSION['update_message'] = 'Student information has been successfully updated!';
        header("Location: ../studentprofile.php"); // Redirect back
        exit();
    } else {
        // Handle error
        echo "Error updating record: " . $stmt->errorInfo()[2];
    }
} else {
    // If not a POST request, redirect or handle as needed
    header("Location: ../studentprofile.php");
    exit();
}
?>
