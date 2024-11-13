<?php
session_start();
require __DIR__ . '/../configs/config.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sign In</title>
    
    <!-- External CSS Links -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
  </head>

<body class="bg-gray-200">
  <main class="main-content  mt-0">
    <div class="page-header align-items-start min-vh-100" style="background-image: url('../assets/img/samplebg.png');">
      <span class="mask bg-gradient-dark opacity-6"></span>
      <div class="container my-auto">
        <div class="row">
          <div class="col-lg-4 col-md-8 col-12 mx-auto">
            <div class="card z-index-0 fadeIn3 fadeInBottom">
            <div class="card-header">
                    <h4 class="mt-3 font-weight-bolder">Sign In</h4>
                    <p class="mb-0">Enter your email and password</p>
                  </div>

              <div class="card-body">
                <form role="form" onsubmit="return validateForm()">
                    <div class="input-group input-group-outline mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" id="email" class="form-control" required 
                        pattern="^[a-zA-Z0-9._%+-]+@g\.batstate-u\.edu\.ph$" 
                        title="Format: username@g.batstate-u.edu.ph (username can contain letters, numbers, and . _ % + -)"
                        oninput="validateEmail()">                      
                 </div>
                 <div class="input-group input-group-outline mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" id="password" class="form-control" required>
                  </div>
                  <a href="#" class="text-primary font-weight-bold mb-2 text-sm ms-10">Forgot Password?</a>
                  <div class="text-center">
                  <button type="button" class="btn btn-primary w-100 my-4 mb-2 btn-responsive">Sign In</button>
                  <button type="button" class="btn btn-outline-primary w-100 my-0 mb-2 btn-outline-responsive d-flex align-items-center justify-content-start" onclick="window.location.href='index.php';">
                    <i class="fa-brands fa-google ms-2"></i> 
                    <span class="ms-auto me-auto">Continue with GSuite</span>
                </button>

                  <!--- to edit 
                  <button type="button" class="btn btn-outline-primary w-100 my-0 mb-2 btn-outline-responsive d-flex align-items-center justify-content-start">
                    <i class="fa-brands fa-google ms-2"></i> <span class="ms-auto me-auto">Continue with GSuite</a></span>
                  </button>-->
                  </div>
                  <div class="card-footer text-center pt-2 px-lg-2 px-1">
                    <p class="mb-3 text-sm mx-auto">
                      Don't have an account?
                      <a href="signup.php" class="text-primary font-weight-bold">Create now</a>
                    </p>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
  </main>
  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }

    function validateForm(event) {
    event.preventDefault(); // Prevent form from submitting
    var email = document.getElementById("email").value.trim();
    var regex = /^[a-zA-Z0-9._%+-]+@g\.batstate-u\.edu\.ph$/;
    var passwordMessage = document.getElementById("passwordMessage");

    if (!regex.test(email)) {
        passwordMessage.textContent = "Please use your official @g.batstate-u.edu.ph email address (letters, numbers, . _ % + - are allowed).";
        return false;
    } else {
        passwordMessage.textContent = ""; // Clear any previous messages
        showModal(); // Show the modal upon successful validation
        return true;
    }
    }
  </script>
  <script src="../assets/js/scrollreveal.min.js"></script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>