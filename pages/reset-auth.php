<?php
session_start();
require __DIR__ . '/../configs/config.php';

// Check if user has verified OTP and has valid reset token
if (!isset($_SESSION['reset_token']) || 
    !isset($_SESSION['reset_email']) || 
    !isset($_SESSION['reset_expires']) || 
    $_SESSION['reset_expires'] < time() || 
    !isset($_GET['token']) || 
    $_GET['token'] !== $_SESSION['reset_token']) {
    
    error_log("Reset Auth Check - Session token: " . ($_SESSION['reset_token'] ?? 'not set')); // Debug log
    error_log("Reset Auth Check - URL token: " . ($_GET['token'] ?? 'not set')); // Debug log
    error_log("Reset Auth Check - Session email: " . ($_SESSION['reset_email'] ?? 'not set')); // Debug log
    error_log("Reset Auth Check - Session expires: " . ($_SESSION['reset_expires'] ?? 'not set')); // Debug log
    
    // Redirect to forgot password page if not verified
    header('Location: forgotpassword.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/logo-space.png">
    <link rel="icon" type="image/png" href="../assets/img/logo-space.png">
    <title>Sign In</title>
    
    <!-- External CSS Links -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/custom-swal.css">
  </head>

<body class="bg-white">
  <!------------------- NAV ----------------------->
  <div class="container position-sticky z-index-sticky top-0">
    <div class="row">
        <div class="col-12">
        <nav class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3 navbar-transparent">
          <div class="container-fluid">
                    <a class="navbar-brand  text-white font-weight-bolder"></a>
                  <div>
                    <a href="index.php" class="btn btn-sm btn-primary mb-0 me-1 mt-2 mt-md-0"><i class="material-symbols-rounded opacity-10 me-0 text-md">home</i></a>
                  </div>
          </div>
        </nav>
        </div>
    </div>
  </div>
  <main class="main-content  mt-0">
    <section>
      <div class="page-header min-vh-100">
        <div class="container">
          <div class="row">
            <div class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 start-0 text-center justify-content-center flex-column">
              <div class="position-relative bg-gradient-dark h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center" style="background-image: url('../assets/img/samplebg.png'); background-size: cover;"></div>
            </div>
            <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column ms-auto me-auto ms-lg-auto me-lg-5">
              <div class="card card-plain">
                <div class="card-header">
                  <h4 class="font-weight-bolder">Reset Password</h4>
                  <p class="mb-0">Enter a new password</p>
                </div>
                <div class="card-body">
                  <form role="form" id="resetPasswordForm">
                    <input type="hidden" name="token" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>">
                    <div class="input-group input-group-outline mb-3">
                      <label class="form-label">New Password</label>
                      <input type="password" id="new-password" name="password" class="form-control">
                      <button type="button" class="btn btn-link position-absolute end-0 top-0 text-dark h-100" 
                              onclick="togglePassword('new-password')" style="z-index: 3;">
                        <i class="material-symbols-rounded" id="new-password-toggle">visibility_off</i>
                      </button>
                    </div>
                    <div class="input-group input-group-outline mb-3">
                      <label class="form-label">Confirm Password</label>
                      <input type="password" id="confirm-password" name="confirm_password" class="form-control">
                      <button type="button" class="btn btn-link position-absolute end-0 top-0 text-dark h-100" 
                              onclick="togglePassword('confirm-password')" style="z-index: 3;">
                        <i class="material-symbols-rounded" id="confirm-password-toggle">visibility_off</i>
                      </button>
                    </div>
                    <div class="buttons">
                      <button type="submit" class="btn btn-lg bg-gradient-primary btn-responsive btn-lg w-100 mt-2 mb-0">
                        Reset Password
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/jquery-3.7.1.min.js"></script>
  <script src="../assets/js/plugins/jquery.validate.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    function togglePassword(inputId) {
      const input = document.getElementById(inputId);
      const icon = document.getElementById(inputId + '-toggle');
      
      if (input.type === 'password') {
        input.type = 'text';
        icon.textContent = 'visibility';
      } else {
        input.type = 'password';
        icon.textContent = 'visibility_off';
      }
    }
  </script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <script src="../assets/js/scrollreveal.min.js"></script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const resetForm = document.getElementById('resetPasswordForm');
        
        resetForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const password = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const token = this.querySelector('[name="token"]').value;

            // Password validation
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            
            if (!passwordRegex.test(password)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Password',
                    text: 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character',
                    confirmButtonText: 'OK',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        content: 'custom-swal-content',
                        confirmButton: 'btn bg-gradient-danger btn-sm'
                    }
                });
                return;
            }

            if (password !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'Passwords do not match',
                    confirmButtonText: 'OK',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        content: 'custom-swal-content',
                        confirmButton: 'btn bg-gradient-danger btn-sm'
                    }
                });
                return;
            }

            try {
                const response = await fetch('../api/reset_password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `token=${encodeURIComponent(token)}&password=${encodeURIComponent(password)}`
                });

                const result = await response.json();
                console.log('Reset password response:', result); // Debug log

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Reset Successful',
                        text: 'You can now login with your new password',
                        confirmButtonText: 'Login',
                        buttonsStyling: false,
                        customClass: {
                            popup: 'custom-swal-popup',
                            title: 'custom-swal-title',
                            content: 'custom-swal-content',
                            confirmButton: 'btn bg-gradient-primary btn-sm'
                        }
                    }).then(() => {
                        window.location.href = 'signin.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Failed to reset password',
                        confirmButtonText: 'OK',
                        buttonsStyling: false,
                        customClass: {
                            popup: 'custom-swal-popup',
                            title: 'custom-swal-title',
                            content: 'custom-swal-content',
                            confirmButton: 'btn bg-gradient-danger btn-sm'
                        }
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while resetting password',
                    confirmButtonText: 'OK',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        content: 'custom-swal-content',
                        confirmButton: 'btn bg-gradient-danger btn-sm'
                    }
                });
            }
        });
    });
  </script>
</body>

</html>