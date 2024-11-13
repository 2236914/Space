<?php
// Include the database connection
include '../configs/config.php'; // Ensure the path is correct based on your file structure

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $phoneNumber = $_POST['phoneNumber'];
    $department = $_POST['department'];
    $course = $_POST['course'];
    $section = $_POST['section'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password for security

    // Prepare the SQL statement
    $insert_query = "INSERT INTO students (firstName, lastName, email, phoneNumber, department, course, section, password) VALUES (:firstName, :lastName, :email, :phoneNumber, :department, :course, :section, :password)";
    $stmt = $pdo->prepare($insert_query);

    // Bind parameters and execute the statement
    $stmt->bindParam(':firstName', $firstName);
    $stmt->bindParam(':lastName', $lastName);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phoneNumber', $phoneNumber);
    $stmt->bindParam(':department', $department);
    $stmt->bindParam(':course', $course);
    $stmt->bindParam(':section', $section);
    $stmt->bindParam(':password', $password);

    if ($stmt->execute()) {
        // Redirect back to student profile page with success message
        header("Location: ../studentprofile.php?update=success"); // Adjust the path if needed
        exit();
    } else {
        echo "Error adding student."; // Handle errors
    }
}
?>
