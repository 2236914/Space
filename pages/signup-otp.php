<?php
session_start();
require __DIR__ . '/../configs/config.php';

// Redirect if no temporary registration data
if (!isset($_SESSION['temp_registration'])) {
    header('Location: signup.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/logo-space.png">
    <link rel="icon" type="image/png" href="../assets/img/logo-space.png">
    <title>Verify Email</title>
    
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
                        <a class="navbar-brand text-white font-weight-bolder"></a>
                        <div>
                            <a href="signup.php" class="btn btn-sm btn-primary mb-0 me-1 mt-2 mt-md-0">
                                <i class="material-symbols-rounded opacity-10 me-0 text-md">arrow_back</i>
                            </a>
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
                        <div class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 start-0 text-center justify-content-center flex-column">
                            <div class="position-relative bg-gradient-dark h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center" style="background-image: url('../assets/img/samplebg.png'); background-size: cover;"></div>
                        </div>
                        <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column ms-auto me-auto ms-lg-auto me-lg-5">
                            <div class="card card-plain">
                                <div class="card-header">
                                    <h4 class="font-weight-bolder">Email Verification</h4>
                                    <p class="mb-0">Enter the verification code sent to:<br>
                                        <strong><?php echo htmlspecialchars($_SESSION['temp_registration']['email']); ?></strong>
                                    </p>
                                </div>
                                <div class="card-body">
                                    <form role="form" id="verificationForm">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">Verification Code</label>
                                            <input type="text" id="verificationCode" name="verificationCode" class="form-control" maxlength="6">
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-lg btn-responsive bg-gradient-primary btn-lg w-100 mt-4 mb-0">
                                                Verify Email
                                            </button>
                                        </div>
                                    </form>
                                    <div class="text-center mt-3">
                                        <p class="mb-2">Didn't receive the code?</p>
                                        <button id="resendButton" class="btn btn-link text-primary text-gradient font-weight-bold">
                                            Resend Code
                                        </button>
                                    </div>
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
    <script src="../assets/js/scrollreveal.min.js"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const verificationForm = document.getElementById('verificationForm');
            const resendButton = document.getElementById('resendButton');
            let resendTimer = 0;

            verificationForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const code = document.getElementById('verificationCode').value;
                
                try {
                    const response = await fetch('../api/verify_code.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ code: code })
                    });

                    const result = await response.json();

                    if (result.status === 'success') {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Verification Successful',
                            html: `
                                <div class="text-center">
                                    <p>Your account has been created successfully!</p>
                                </div>
                            `,
                            confirmButtonText: 'Continue to Sign In',
                            allowOutsideClick: false,
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                        window.location.href = 'signin.php';
                    } else {
                        await Swal.fire({
                            icon: 'error',
                            title: 'Verification Failed',
                            text: result.message,
                            confirmButtonText: 'Try Again',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                    }
                } catch (error) {
                    console.error('Error:', error);
                    await Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred. Please try again.',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            });

            resendButton.addEventListener('click', async function() {
                if (resendTimer > 0) return;

                try {
                    const response = await fetch('../api/resend_code.php');
                    const result = await response.json();

                    if (result.status === 'success') {
                        // Start cooldown timer (60 seconds)
                        resendTimer = 60;
                        resendButton.disabled = true;
                        
                        const updateTimer = setInterval(() => {
                            resendButton.textContent = `Resend Code (${resendTimer}s)`;
                            resendTimer--;
                            
                            if (resendTimer < 0) {
                                clearInterval(updateTimer);
                                resendButton.disabled = false;
                                resendButton.textContent = 'Resend Code';
                            }
                        }, 1000);

                        await Swal.fire({
                            icon: 'success',
                            title: 'Code Resent',
                            text: 'A new verification code has been sent to your email.',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                    } else {
                        await Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message,
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        });
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        });
    </script>
</body>

</html>