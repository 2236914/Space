<?php
session_start();
require 'configs/config.php'; // Ensure this is the path to your config

// Prevent page caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if the user is logged in as Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: index.php');
    exit();
}

$admin_id = $_SESSION['user_id'];

// Fetch the admin data
try {
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE admin_id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch();
} catch (Exception $e) {
    echo "Error fetching admin data: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--=============== FAVICON ===============-->
    <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon">

    <!--=============== GOOGLE FONT ===============-->
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <!--=============== REMIX ICONS ===============-->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">

    <!--=============== CSS ===============-->
    <link rel="stylesheet" href="assets/css/admin.css">
    <title>Space</title>
</head>
<body>

<!-- Show success modal if update was successful -->
<?php if (isset($_GET['update']) && $_GET['update'] == 'success'): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById('success-modal').style.display = 'flex';
            
            // Remove the query parameter after showing the modal
            const url = new URL(window.location);
            url.searchParams.delete('update');
            window.history.replaceState({}, document.title, url);
        });
    </script>
<?php endif; ?>

<!--==================== HEADER ====================-->
<header class="header" id="header">
    <nav class="nav container">
        <h1 class="nav__logo">
            <i class="ri-mental-health-fill nav__logo-icon"></i>Space.
        </h1>

        <div class="nav__menu" id="nav-menu">
            <ul class="nav__list">
                <li class="nav__item">
                    <a href="admin.php" class="nav__link active-link">Home</a>
                </li>
                <li class="nav__item">
                    <a href="studentprofile.php" class="nav__link">Students</a>
                </li>
                <li class="nav__item">
                    <a href="adminprofiles.php" class="nav__link">Admin</a>
                </li>
            </ul>
        </div>
<!-- Navigation buttons -->
<div class="nav__btns">
  <!-- Log Out button -->
  <a href="logout.php" class="nav__logout-button" id="open-logout-modal">Log Out</a>

  <!-- Theme change button -->
  <i class="ri-moon-line change-theme" id="theme-button"></i>

  <!-- Menu toggle for mobile view -->
  <div class="nav__toggle" id="nav-toggle">
    <i class="ri-menu-line"></i>
  </div>
</div>

<!-- Modal -->
<div class="modal" id="logout-modal">
  <div class="modal__content">
    <!-- Close button -->
    <button class="modal__close" id="close-logout-modal">&times;</button>

    <h2>Log Out</h2>
    <hr class="underline">

    <p>Are you sure you want to log out?</p>
    <div class="modal__logout-buttons">
      <button class="modal__logout-button--outline" id="cancel-logout" type="button">Cancel</button>
      <!-- Log Out button -->
      <form action="logout.php" method="POST">
        <button class="modal__logout-button--black" type="submit">Log Out</button>
      </form>
    </div>
  </div>
</div>

    </nav>
</header>

<main class="main">
    <section class="admin-profile">
        <div class="profile-container">
            <div class="profile-image-section">
                <h2 class="profile-title">Admin Profile</h2>
                <div class="profile-image">
                <img src="uploads/admin_pictures/<?php echo $admin['profile_picture'] ?: 'default.png'; ?>" alt="Admin Picture" id="admin-picture" width="150" height="150">
                    <div class="profile-role">
                        <p><?php echo htmlspecialchars($admin['admin_role']); ?></p>
                        <p><?php echo htmlspecialchars($admin['admin_id']); ?></p>
                    </div>
                </div>
                <input type="file" name="profile_picture" id="profile_picture" style="display:none;" onchange="previewImage(event)">
                <button type="button" id="upload-btn" style="display:none;" onclick="document.getElementById('profile_picture').click();">Upload Picture</button>
            </div>

            <!-- Profile Form Fields -->
            <div class="profile-form-section">
                <form id="admin-profile-form" method="POST" enctype="multipart/form-data" action="admin_operations/update_admin.php">
                    <div class="row">
                        <div class="column">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" disabled>
                            <label for="phone_number">Phone Number</label>
                            <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($admin['phone_number']); ?>" disabled>
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" disabled>
                        </div>

                        <div class="column">
                            <label for="admin_role">Admin Role</label>
                            <input type="text" id="admin_role" name="admin_role" value="<?php echo htmlspecialchars($admin['admin_role']); ?>" disabled>
                            <label for="department">Office/Department</label>
                            <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($admin['department']); ?>" disabled>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="buttons-container">
                        <button type="button" id="edit-btn">Edit</button>
                        <button type="submit" id="update-btn" style="display:none;">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<!-- Success Modal -->
<div id="success-modal" class="modal">
    <div class="modal__content">
        <span class="modal__close" onclick="closeModal()">&times;</span>
        <h2>Success!</h2>
        <p>Your profile has been updated successfully.</p>
    </div>
</div>

<script>
// Close modal
function closeModal() {
    document.getElementById('success-modal').style.display = 'none';
}

// Preview uploaded image
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function(){
        var output = document.getElementById('admin-picture');
        console.log(reader.result);  // Debug: check if the correct image data is being loaded
        output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}



// Enable fields and show update button when Edit is clicked
document.getElementById('edit-btn').addEventListener('click', function () {
    var inputs = document.querySelectorAll('#admin-profile-form input');
    inputs.forEach(input => {
        input.disabled = false; // Enable all fields for editing
    });
    document.getElementById('update-btn').style.display = 'inline-block';
    document.getElementById('upload-btn').style.display = 'inline-block';
    document.getElementById('edit-btn').style.display = 'none';
});

// Get modal and buttons
const logoutModal = document.getElementById('logout-modal');
const openLogoutModalBtn = document.getElementById('open-logout-modal');
const closeLogoutModalBtn = document.getElementById('close-logout-modal');
const cancelLogoutBtn = document.getElementById('cancel-logout');

// Open the modal when the Log Out button is clicked
openLogoutModalBtn.addEventListener('click', () => {
  logoutModal.classList.add('show');
});

// Close the modal when the Close (X) button or Cancel button is clicked
closeLogoutModalBtn.addEventListener('click', () => {
  logoutModal.classList.remove('show');
});

cancelLogoutBtn.addEventListener('click', () => {
  logoutModal.classList.remove('show');
});

// Close modal if clicked outside of the modal content
window.addEventListener('click', (e) => {
  if (e.target === logoutModal) {
    logoutModal.classList.remove('show');
  }
});

</script>

</body>
</html>