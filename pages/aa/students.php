<?php
session_start();

// Prevent page caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Student' && $_SESSION['role'] !== 'Therapist')) {
    header('Location: index.php'); // Redirect to login page if not logged in
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
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

        <!--=============== REMIX ICONS ===============-->
        <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">

        <!--=============== CSS ===============-->
        <link rel="stylesheet" href="assets/css/admin.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
        <title>Space</title>
    </head>
    <body>
        <!--==================== HEADER ====================-->
        <header class="header" id="header">
           <nav class="nav container">
    <h1 href="index.html" class="nav__logo">
        <i class="ri-mental-health-fill nav__logo-icon"></i>Space.
    </h1>

    <div class="nav__menu" id="nav-menu">
        <ul class="nav__list">
            <li class="nav__item">
                <a href="#home" class="nav__link active-link">Home</a>
            </li>
            <li class="nav__item">
                <a href="#therapy" class="nav__link">Students</a>
            </li>
            <li class="nav__item">
                <a href="#developer" class="nav__link">Admin</a>
            </li>
        </ul>

        <div class="nav__close" id="nav-close">
            <i class="ri-close-line"></i>
        </div>
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

        </header>

        <main class="main">
            <!--==================== HOME ====================-->
<section>

</section>

        
        <!--=============== MAIN JS ===============-->
        <script src="assets/js/main.js"></script>
        <script>
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
