 <?php
session_start();
require '../configs/config.php'; // Ensure this is the path to your config file

// Check if the user is logged in as Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../index.php');
    exit();
}

$admin_id = $_SESSION['user_id'];

// Check if form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Collect updated data from the form
        $name = htmlspecialchars($_POST['name']);
        $phone_number = htmlspecialchars($_POST['phone_number']);
        $email = htmlspecialchars($_POST['email']);
        $password = htmlspecialchars($_POST['password']) ?: null; // Optional password change
        $admin_role = htmlspecialchars($_POST['admin_role']);
        $department = htmlspecialchars($_POST['department']);

        // DEBUG: Print $_FILES array to check uploaded file info
        if (isset($_FILES['profile_picture'])) {
            echo '<pre>';
            print_r($_FILES['profile_picture']);  // This will output the details of the uploaded file
            echo '</pre>';
        }

        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $target_dir = "../uploads/admin_pictures/";
            $image_name = time() . "_" . basename($_FILES['profile_picture']['name']); // Add a unique timestamp to avoid file name conflicts
            $target_file = $target_dir . $image_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if it's a valid image
            $check = getimagesize($_FILES['profile_picture']['tmp_name']);
            if ($check === false) {
                throw new Exception("File is not an image.");
            }

            // Move the uploaded image to the uploads folder
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                $profile_picture = $image_name; // Save the new file name in the database
            } else {
                throw new Exception("Error uploading image.");
            }
        } else {
            // If no new image uploaded, keep the old one
            $stmt = $pdo->prepare("SELECT profile_picture FROM admin WHERE admin_id = ?");
            $stmt->execute([$admin_id]);
            $admin = $stmt->fetch();
            $profile_picture = $admin['profile_picture']; // Use the old image
        }

        // Prepare SQL update statement
        if ($password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
            $stmt = $pdo->prepare("
                UPDATE admin 
                SET name = ?, phone_number = ?, email = ?, password = ?, admin_role = ?, department = ?, profile_picture = ?
                WHERE admin_id = ?
            ");
            $stmt->execute([$name, $phone_number, $email, $hashed_password, $admin_role, $department, $profile_picture, $admin_id]);
        }
        
        
        else {
            $stmt = $pdo->prepare("
                UPDATE admin 
                SET name = ?, phone_number = ?, email = ?, admin_role = ?, department = ?, profile_picture = ?
                WHERE admin_id = ?
            ");
            $stmt->execute([$name, $phone_number, $email, $admin_role, $department, $profile_picture, $admin_id]);
        }

        // Redirect back to the admin profile page with success
        header('Location: ../admin.php?update=success');
        exit();

    } catch (Exception $e) {
        echo "Error updating profile: " . $e->getMessage();
        exit();
    }
} else {
    header('Location: ../admin.php');
    exit();
}
?>
