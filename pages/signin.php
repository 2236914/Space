<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add a check before starting session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session data
echo "<!-- Session data: " . print_r($_SESSION, true) . " -->";

require __DIR__ . '/../configs/config.php';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/logo-space.png">
    <link rel="icon" type="image/png" href="../assets/img/logo-space.png">
    <title>Sign In</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
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
  <main class="main-content mt-0">
        <section>
            <div class="page-header min-vh-100">
                <div class="container">
                    <div class="row">
                        <!-- Left Illustration -->
                        <div class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 start-0 text-center justify-content-center flex-column">
                            <div class="position-relative h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center" 
                                 style="background-image: url('../assets/img/samplebg.png'); background-size: cover;">
                            </div>
                        </div>
                        
                        <!-- Sign In Form -->
                        <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column ms-auto me-auto ms-lg-auto me-lg-5">
                            <div class="card card-plain">
                                <div class="card-header text-center">
                                    <h4 class="font-weight-bolder">Sign In</h4>
                                    <p class="mb-0">Enter your email and password to sign in</p>
                                </div>
                                <div class="card-body mt-2">
                                    <form role="form" id="loginForm" method="post">
                                        <!-- Email Field -->
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control" required>
                                        </div>
                                        <!-- Password Field -->
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">Password</label>
                                            <input type="password" name="password" class="form-control" required>
                                            <button type="button" class="btn btn-link position-absolute end-0 top-0 text-dark h-100" 
                                                    onclick="togglePassword('password')" style="z-index: 3;">
                                                <i class="material-symbols-rounded" id="password-toggle">visibility_off</i>
                                            </button>
                                        </div>
                                        <!-- Buttons -->
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-lg bg-gradient-primary btn-lg btn-responsive w-100 mt-4 mb-0">
                                                Sign In
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                                    <p class="mb-4 text-sm mx-auto">
                                        Don't have an account? 
                                        <a href="signup.php" class="text-primary font-weight-bold">Sign up</a>
                                    </p>
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../assets/js/scrollreveal.min.js"></script>
  <script src="../assets/js/authAnimations.js"></script>
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
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
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('../admin_operations/login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                console.log('Server response:', result); // Debug log

                if (result.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: result.title,
                        text: result.message,
                        confirmButtonText: 'Continue',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn bg-gradient-primary btn-sm mx-2',
                            actions: 'justify-content-center'
                        }
                    }).then((sweetAlertResult) => {
                        if (sweetAlertResult.isConfirmed) {
                            window.location.href = '/Space' + result.redirect;
                        }
                    });
                } else {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Login Failed',
                        text: result.message,
                        confirmButtonText: 'Try Again',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn bg-gradient-primary btn-sm mx-2',
                            actions: 'justify-content-center'
                        }
                    });
                    
                    document.querySelector('input[name="password"]').value = '';
                }
            } catch (error) {
                console.error('Login error:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred during login. Please try again.',
                    confirmButtonText: 'OK',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn bg-gradient-primary btn-sm mx-2',
                        actions: 'justify-content-center'
                    }
                });
            }
        });
    }
});

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
document.addEventListener('DOMContentLoaded', function() {
    // Handle input focus and blur events for material design effect
    const inputs = document.querySelectorAll('.input-group-outline input');
    
    inputs.forEach(input => {
        // Add is-filled class if input has value on page load
        if (input.value !== '') {
            input.parentElement.classList.add('is-filled');
        }
        
        // Handle focus event
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('is-focused');
        });
        
        // Handle blur event
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('is-focused');
            if (this.value !== '') {
                this.parentElement.classList.add('is-filled');
            } else {
                this.parentElement.classList.remove('is-filled');
            }
        });
        
        // Handle input event
        input.addEventListener('input', function() {
            if (this.value !== '') {
                this.parentElement.classList.add('is-filled');
            } else {
                this.parentElement.classList.remove('is-filled');
            }
        });
    });
});
</script>

<script>
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'account_deactivated'): ?>
    Swal.fire({
        title: 'Account Deactivated',
        text: 'Your account has been successfully deactivated. You can reactivate it anytime by logging in.',
        icon: 'success',
        customClass: {
            confirmButton: 'btn bg-gradient-primary btn-sm mb-0'
        },
        buttonsStyling: false
    });
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'deactivation_failed'): ?>
    Swal.fire({
        title: 'Error',
        text: 'Failed to deactivate account. Please try again or contact support.',
        icon: 'error',
        customClass: {
            confirmButton: 'btn bg-gradient-primary btn-sm mb-0'
        },
        buttonsStyling: false
    });
<?php endif; ?>
</script>

</body>

</html>