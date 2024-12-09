<?php
session_start();
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
    
    <!-- External CSS Links -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.0/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/css/custom-swal.css">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
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
                  <h4 class="font-weight-bolder">Verify Email</h4>
                  <p class="mb-0">Enter your email</p>
                </div>
                <div class="card-body">
                  <form role="form" id="verifyEmailForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <div class="input-group input-group-outline mb-3">
                      <label class="form-label">Email</label>
                      <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="buttons">
                      <button type="submit" class="btn btn-lg bg-gradient-primary btn-responsive btn-lg w-100 mt-2 mb-0" id="verifyBtn">
                        Verify
                      </button>
                      <button type="button" class="btn btn-lg btn-outline-primary btn-responsive btn-lg w-100 mt-3 mb-0" 
                              id="sendOtpBtn" disabled>
                        Send OTP
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
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Material Dashboard Scripts -->
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>

  <!-- Custom Scripts -->
  <script>
    // Add this for material input fields
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.input-group-outline input');
        
        inputs.forEach(input => {
            if (input.value !== '') {
                input.parentElement.classList.add('is-filled');
            }
            
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('is-focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('is-focused');
                if (this.value !== '') {
                    this.parentElement.classList.add('is-filled');
                } else {
                    this.parentElement.classList.remove('is-filled');
                }
            });
        });
    });

    // Declare verifiedEmail in global scope
    let verifiedEmail = '';

    document.addEventListener('DOMContentLoaded', function() {
        const verifyEmailForm = document.getElementById('verifyEmailForm');
        const sendOtpBtn = document.getElementById('sendOtpBtn');

        verifyEmailForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const email = this.email.value;
            console.log('Verifying email:', email); // Debug log

            try {
                const response = await fetch('../admin_operations/verify_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `email=${encodeURIComponent(email)}`
                });

                const result = await response.json();
                console.log('Server response:', result); // Debug log

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Email Verified',
                        text: 'Email found in our records. You can now send OTP.',
                        confirmButtonText: 'OK',
                        buttonsStyling: false,
                        customClass: {
                            popup: 'custom-swal-popup',
                            title: 'custom-swal-title',
                            content: 'custom-swal-content',
                            confirmButton: 'btn bg-gradient-primary btn-sm'
                        }
                    });
                    sendOtpBtn.disabled = false; // Enable the button
                    verifiedEmail = email; // Store the verified email
                    console.log('Send OTP button enabled'); // Debug log
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Verification Failed',
                        text: 'Email not found in our records.',
                        confirmButtonText: 'OK',
                        buttonsStyling: false,
                        customClass: {
                            popup: 'custom-swal-popup',
                            title: 'custom-swal-title',
                            content: 'custom-swal-content',
                            confirmButton: 'btn bg-gradient-danger btn-sm'
                        }
                    });
                    sendOtpBtn.disabled = true; // Keep button disabled
                    verifiedEmail = ''; // Clear the verified email
                    console.log('Send OTP button remains disabled'); // Debug log
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred during verification.',
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

    // OTP Button Handler
    const sendOtpBtn = document.getElementById('sendOtpBtn');

    sendOtpBtn.addEventListener('click', async function() {
        if (!verifiedEmail) return;

        try {
            const response = await fetch('../api/send_otp.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `email=${encodeURIComponent(verifiedEmail)}`
            });

            const result = await response.json();
            console.log('OTP response:', result); // Debug log

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'OTP Sent',
                    text: 'Please check your email for the OTP code.',
                    confirmButtonText: 'OK',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        content: 'custom-swal-content',
                        confirmButton: 'btn bg-gradient-primary btn-sm'
                    }
                }).then(() => {
                    window.location.href = 'otp-auth.php?email=' + encodeURIComponent(verifiedEmail);
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Failed to send OTP. Please try again.',
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
            console.error('OTP Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while sending OTP.',
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

    // Add debug logs
    console.log('Form element:', verifyEmailForm);
    console.log('Send OTP button:', sendOtpBtn);

    // Initialize tooltips if you're using them
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Initialize perfect scrollbar
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
        var options = {
            damping: '0.5'
        }
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
</body>

</html>