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
  <title>Sign Up</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/custom-swal.css" rel="stylesheet" />
</head>

<body>
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
                    <div class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 start-0 text-center justify-content-center flex-column">
                      <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center" style="background-image:url('../assets/img/samplebg.png'); background-size: cover;">
                      </div>
                    </div>
                        <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column ms-auto me-auto ms-lg-auto me-lg-5">
                            <div class="card card-plain">
                                <div class="card-header">
                                    <h4 class="font-weight-bolder">Sign Up</h4>
                                    <p class="mb-0">Enter your information to register</p>
                                </div>
                                <div class="card-body">
                                    <form id="signupForm" role="form" method="POST">
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">First Name</label>
                                            <input type="text" name="firstname" class="form-control" required oninput="capitalizeFirstLetter(this)">
                                        </div>
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" name="lastname" class="form-control" required oninput="capitalizeFirstLetter(this)">
                                        </div>
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">SR-Code</label>
                                            <input type="text" name="srcode" class="form-control" required oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                                        </div>
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">Phone Number</label>
                                            <input type="text" name="phonenum" class="form-control" required maxlength="11">
                                        </div>
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" id="email" class="form-control" required>
                                        </div>
                                        <div class="input-group input-group-outline mb-3">
                                            <label class="form-label">Password</label>
                                            <input type="password" name="password" id="password" class="form-control" required>
                                            <button type="button" 
                                                    class="btn btn-link position-absolute end-0 top-0 text-dark h-100" 
                                                    onclick="togglePassword('password')" 
                                                    style="z-index: 3;"
                                                    aria-label="Show password">
                                                <i class="material-symbols-rounded" id="password-toggle">visibility_off</i>
                                            </button>
                                        </div>
                                        <div class="form-check form-check-info text-start ps-0">
                                            <input class="form-check-input" type="checkbox" value="" id="termsCheckbox" onchange="handleTermsCheckbox(this)">
                                            <label class="form-check-label" for="termsCheckbox">
                                                I agree to the <span class="text-dark font-weight-bolder">Terms and Conditions</span>
                                            </label>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" id="signupBtn" class="btn bg-gradient-primary btn-responsive btn-lg w-100 my-4 mb-2">Sign up</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center pt-2 px-lg-2 px-1">
                                    <p class="mb-2 text-sm mx-auto">
                                        Already have an account?
                                        <a href="../pages/signin.php" class="text-primary font-weight-bold">Sign in</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
<div class="position-fixed bottom-1 end-1 z-index-2">
  <div class="toast fade p-2 mt-2 bg-white" role="alert" aria-live="assertive" id="passwordToast" aria-atomic="true">
    <div class="toast-header border-0">
      <i class="material-symbols-rounded text-info me-2">password</i>
      <span class="me-auto font-weight-bold">Password Requirements</span>
      <small class="text-body">just now</small>
      <i class="fas fa-times text-md ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close"></i>
    </div>
    <hr class="horizontal dark m-0">
    <div class="toast-body">
      Password must include:
      <div class="d-flex align-items-center mb-2">
        <i class="material-symbols-rounded me-2" id="lengthCheck">close</i>
        <span id="lengthText" class="text-danger">At least 8 characters</span>
      </div>
      <div class="d-flex align-items-center mb-2">
        <i class="material-symbols-rounded me-2" id="specialCheck">close</i>
        <span id="specialText" class="text-danger">At least one special character (!@#$%^&*)</span>
      </div>
      <div class="d-flex align-items-center">
        <i class="material-symbols-rounded me-2" id="numberCheck">close</i>
        <span id="numberText" class="text-danger">At least one number</span>
      </div>
    </div>
  </div>
</div>
    </main>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content"> 
      <div class="modal-header">
        <h5 class="modal-title font-weight-normal" id="exampleModalLabel">Terms and Conditions</h5>
      </div>
      <div class="modal-body p-5" id="termsModalBody" style="max-height: 400px; overflow-y: auto;">
        <p class="text-xs mb-0"><strong>Welcome to Space: A Web-Based Mental Health Therapy System for Students of BSU TNEU Lipa ("Space").</strong> By accessing and using this platform, you agree to comply with and be bound by the following terms and conditions. Please read them carefully.</p>

        <p class="text-xs mb-0 mt-3"><strong>1. Acceptance of Terms</strong></p>
        <p class="text-xs mb-0">By using Space, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions, as well as any additional terms, policies, and rules that are referenced herein.</p>

        <p class="text-xs mb-0 mt-3"><strong>2. Eligibility</strong></p>
        <p class="text-xs mb-0">Only students enrolled at BSU TNEU Lipa are eligible to create an account and access the services provided on this platform. Therapists must be licensed professionals whose accounts are created and managed by the system administrator.</p>

        <p class="text-xs mb-0 mt-3"><strong>3. Account Registration and Security</strong></p>
        <ul>
          <li class="text-xs mb-0"><strong>User Registration:</strong> Students are required to create an account using their BSU student email through the registration page. A one-time password (OTP) will be sent for account verification.</li>
          <li class="text-xs mb-0"><strong>Therapist Accounts:</strong> Therapists are granted access by the system administrator. Self-registration is not available for therapists.</li>
          <li class="text-xs mb-0"><strong>Account Security:</strong> You agree to notify us immediately if you suspect any unauthorized use of your account.</li>
        </ul>

        <p class="text-xs mb-0 mt-3"><strong>4. Use of Services</strong></p>
        <ul>
          <li class="text-xs mb-0"><strong>Mood Tracking:</strong> The mood tracking feature is available for registered students. Therapists can only view mood tracking data for students who have scheduled appointments with them.</li>
          <li class="text-xs mb-0"><strong>Virtual Therapy Sessions:</strong> Students can schedule therapy sessions with licensed professionals via the platform’s calendar. Therapy sessions are conducted using secure video conferencing.</li>
          <li class="text-xs mb-0"><strong>Post Sharing:</strong> Users (students and therapists) can share posts, which may be done anonymously if preferred. Posts must adhere to community guidelines and must not contain harmful, offensive, or discriminatory content.</li>
          <li class="text-xs mb-0"><strong>Self-Care Tools:</strong> Space provides access to guided meditations, breathing exercises, and journaling prompts for registered users.</li>
          <li class="text-xs mb-0"><strong>Guest Access:</strong> Guests can access limited mental health resources without creating an account.</li>
        </ul>

        <p class="text-xs mb-0 mt-3"><strong>5. User Conduct</strong></p>
        <p class="text-xs mb-0">By using Space, you agree not to:</p>
        <ul>
          <li class="text-xs mb-0">Violate any applicable laws or regulations.</li>
          <li class="text-xs mb-0">Post any abusive, offensive, harmful, or discriminatory content.</li>
          <li class="text-xs mb-0">Use the platform to harass or harm other users.</li>
          <li class="text-xs mb-0">Impersonate another individual or provide false information during registration.</li>
        </ul>
        <p class="text-xs mb-0">Failure to adhere to these guidelines may result in the suspension or termination of your account.</p>

        <p class="text-xs mb-0 mt-3"><strong>6. Privacy and Data Security</strong></p>
        <ul>
          <li class="text-xs mb-0"><strong>Data Collection:</strong> Space collects personal data, including mood tracking information and session details, to provide services. By using the platform, you consent to the collection, use, and processing of this data in accordance with our [Privacy Policy].</li>
          <li class="text-xs mb-0"><strong>Data Security:</strong> We employ encryption and other security measures to protect your personal information. However, we cannot guarantee absolute security. You agree that you use the system at your own risk.</li>
        </ul>

        <p class="text-xs mb-0 mt-3"><strong>7. Payments</strong></p>
        <ul>
          <li class="text-xs mb-0"><strong>Membership Fees:</strong> Space operates on a yearly membership fee system for students. Payment is required to access therapy sessions and other premium features.</li>
          <li class="text-xs mb-0"><strong>Payment Methods:</strong> All payments must be made through the platform’s secure payment gateway.</li>
          <li class="text-xs mb-0"><strong>Refunds:</strong> Membership fees are non-refundable unless otherwise stated or required by law.</li>
        </ul>

        <p class="text-xs mb-0 mt-3"><strong>8. Intellectual Property</strong></p>
        <ul>
          <li class="text-xs mb-0"><strong>Ownership:</strong> All content, trademarks, logos, and materials provided on Space are the intellectual property of the platform and its licensors. You may not use or reproduce any part of the content without prior written permission.</li>
          <li class="text-xs mb-0"><strong>User-Generated Content:</strong> By posting on the platform, you grant Space a non-exclusive, royalty-free, worldwide license to use, display, and modify your content for the purpose of providing services.</li>
        </ul>

        <p class="text-xs mb-0 mt-3"><strong>9. Limitation of Liability</strong></p>
        <p class="text-xs mb-0">Space and its creators, including the CreoTech team, will not be liable for any indirect, incidental, special, or consequential damages arising out of or in connection with the use of this platform, including but not limited to emotional distress, data loss, or unauthorized access to personal information.</p>

        <p class="text-xs mb-0 mt-3"><strong>10. Modification of Terms</strong></p>
        <p class="text-xs mb-0">We reserve the right to modify these Terms and Conditions at any time. Users will be notified of any significant changes, and continued use of the platform following any such changes constitutes your acceptance of the revised terms.</p>

        <p class="text-xs mb-0 mt-3"><strong>11. Termination of Service</strong></p>
        <p class="text-xs mb-0">We may suspend or terminate your account and access to the platform at any time for violation of these Terms and Conditions or any other reason deemed necessary for the protection of the platform and its users.</p>

        <p class="text-xs mb-0 mt-3"><strong>12. Governing Law</strong></p>
        <p class="text-xs mb-0">These Terms and Conditions shall be governed and construed in accordance with the laws of the Philippines, without regard to its conflict of law principles.</p>

        <p class="text-xs mb-0 mt-3"><strong>13. Contact Information</strong></p>
        <p class="text-xs mb-0">For any questions or concerns regarding these Terms and Conditions, please contact the Space team at <a href="mailto:spacesupport@gmail.com">spacesupport@gmail.com</a>.</p>

        <!-- Accept button -->
        <div class="text-end mt-4">
          <button type="button" class="btn bg-gradient-primary" id="acceptTermsBtn" style="display: none;" data-bs-dismiss="modal">Accept</button>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Container for all toasts - add this before closing </body> tag -->
<div class="position-fixed bottom-1 end-1 z-index-2">
    <!-- Password Toast (will appear at bottom) -->
    <div class="toast fade hide p-2 bg-white" role="alert" aria-live="assertive" id="passwordToast" aria-atomic="true">
      <div class="toast-header border-0">
        <i class="material-symbols-rounded text-info me-2">password</i>
        <span class="me-auto font-weight-bold">Password Requirements</span>
        <small class="text-body">just now</small>
        <i class="fas fa-times text-md ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close"></i>
      </div>
      <hr class="horizontal dark m-0">
      <div class="toast-body">
        Password must include:
        <ul class="list-unstyled mb-0">
          <li class="d-flex align-items-center">
            <i class="material-symbols-rounded me-2" id="lengthCheck">close</i>
            <span id="lengthText" class="text-danger">At least 8 characters</span>
          </li>
          <li class="d-flex align-items-center">
            <i class="material-symbols-rounded me-2" id="specialCheck">close</i>
            <span id="specialText" class="text-danger">At least one special character (! @ # $ % ^ & * ( ) _ +)</span>
          </li>
          <li class="d-flex align-items-center">
            <i class="material-symbols-rounded me-2" id="numberCheck">close</i>
            <span id="numberText" class="text-danger">At least one number</span>
          </li>
        </ul>
      </div>
    </div>
</div>
<!-- Toast container -->
<div class="position-fixed bottom-1 end-1 z-index-2">
    <div class="toast fade hide p-2 bg-white" role="alert" aria-live="assertive" id="warningToast" aria-atomic="true">
        <div class="toast-header border-0">
            <i class="material-symbols-rounded text-warning me-2">warning</i>
            <span class="me-auto font-weight-bold">Warning</span>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="warningToastMessage"></div>
    </div>
</div>




  <!-- Core JS Files -->
  <script src="../assets/js/scrollreveal.min.js"></script>
  <script src="../assets/js/authAnimations.js"></script>
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.0.0"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>  
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const form = document.getElementById('signupForm');
    const firstNameInput = document.querySelector('input[name="firstname"]');
    const lastNameInput = document.querySelector('input[name="lastname"]');
    const srCodeInput = document.querySelector('input[name="srcode"]');
    const phoneInput = document.querySelector('input[name="phonenum"]');
    const emailInput = document.querySelector('input[name="email"]');
    const passwordInput = document.getElementById('password');
    const termsCheckbox = document.getElementById('termsCheckbox');
    const signupBtn = document.querySelector('button[type="submit"]');
    const modal = new bootstrap.Modal(document.getElementById('exampleModal'));
    const modalBody = document.getElementById('termsModalBody');
    const acceptBtn = document.getElementById('acceptTermsBtn');
    let hasScrolledToBottom = false;
    let passwordToast = null;

    // Toast notification function
    function showToast(message) {
        const toast = document.getElementById('warningToast');
        document.getElementById('warningToastMessage').textContent = message;
        const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
        bsToast.show();
    }

    // Password Toggle Function
    window.togglePassword = function(inputId) {
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

    // Input Validations

    // Phone Number Validation
    phoneInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        
        if (this.value.length >= 2 && !this.value.startsWith('09')) {
            showToast("Phone number must start with '09'");
            this.value = '';
        }
        
        if (this.value.length > 11) {
            this.value = this.value.slice(0, 11);
        }
    });

    // SR-Code Validation
    srCodeInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 7) {
            this.value = this.value.slice(0, 7);
        }
    });

    // Name Capitalization
    function capitalizeInput(input) {
        input.value = input.value.charAt(0).toUpperCase() + input.value.slice(1).toLowerCase();
    }

    firstNameInput.addEventListener('blur', () => capitalizeInput(firstNameInput));
    lastNameInput.addEventListener('blur', () => capitalizeInput(lastNameInput));

    // Email Validation
    emailInput.addEventListener('blur', function() {
        const email = this.value.trim();
        if (!email.endsWith('@g.batstate-u.edu.ph')) {
            showToast("Please use your @g.batstate-u.edu.ph email");
            this.value = '';
            return;
        }

        // Validate SR-Code and email matching
        const srcode = srCodeInput.value;
        if (srcode.length === 7) {
            const formattedSRCode = srcode.replace(/^(\d{2})(\d{5})$/, '$1-$2');
            const expectedEmail = `${formattedSRCode}@g.batstate-u.edu.ph`;
            
            if (email.toLowerCase() !== expectedEmail.toLowerCase()) {
                showToast("SR-Code and G-Suite email do not match");
                this.value = '';
            }
        }
    });

    // Password Toast Initialization
    function initializePasswordToast() {
        const toastElement = document.getElementById('passwordToast');
        if (!passwordToast) {
            passwordToast = new bootstrap.Toast(toastElement, {
                autohide: false,
                animation: false  // Disable animation to prevent flickering
            });
        }
        return passwordToast;
    }

    // Password Field Event Listeners
    passwordInput.addEventListener('focus', function() {
        const toast = initializePasswordToast();
        toast.show();
    });

    passwordInput.addEventListener('input', function() {
        validatePassword(this.value); // Remove toast.show() call here to prevent flickering
    });

    passwordInput.addEventListener('blur', function() {
        if (this.value.length === 0 || validatePassword(this.value)) {
            const toast = bootstrap.Toast.getInstance(document.getElementById('passwordToast'));
            if (toast) {
                toast.hide();
            }
        }
    });

    // Password Validation Function
    function validatePassword(password) {
        const lengthValid = password.length >= 8;
        const specialValid = /[!@#$%^&*()_+]/.test(password);
        const numberValid = /\d/.test(password);

        updateRequirement('length', lengthValid);
        updateRequirement('special', specialValid);
        updateRequirement('number', numberValid);

        return lengthValid && specialValid && numberValid;
    }

    // Update Requirement Indicators
    function updateRequirement(type, isValid) {
        const icon = document.getElementById(`${type}Check`);
        const text = document.getElementById(`${type}Text`);
        
        if (isValid) {
            icon.textContent = 'check_circle';
            icon.classList.remove('text-danger');
            icon.classList.add('text-success');
            text.classList.remove('text-danger');
            text.classList.add('text-success');
        } else {
            icon.textContent = 'close';
            icon.classList.remove('text-success');
            icon.classList.add('text-danger');
            text.classList.remove('text-success');
            text.classList.add('text-danger');
        }
    }

    // Initially disable signup button
    signupBtn.disabled = true;
    signupBtn.style.opacity = '0.6';
    signupBtn.style.cursor = 'not-allowed';

    // Terms checkbox handler
    termsCheckbox.addEventListener('change', function() {
        if (this.checked) {
            modal.show();
        } else {
            signupBtn.disabled = true;
            signupBtn.style.opacity = '0.6';
            signupBtn.style.cursor = 'not-allowed';
        }
    });

    // When Accept button is clicked
    acceptBtn.addEventListener('click', function() {
        termsCheckbox.checked = true; // Ensure checkbox is checked
        signupBtn.disabled = false;
        signupBtn.style.opacity = '1';
        signupBtn.style.cursor = 'pointer';
        modal.hide();
    });

    // Prevent modal from closing on backdrop click or escape key
    document.getElementById('exampleModal').addEventListener('hide.bs.modal', function(event) {
        if (!hasScrolledToBottom) {
            event.preventDefault();
            showToast("Please read the entire Terms and Conditions before accepting");
        }
    });

    // Check scroll position
    modalBody.addEventListener('scroll', function() {
        const isAtBottom = modalBody.scrollHeight - modalBody.scrollTop <= modalBody.clientHeight + 50;
        if (isAtBottom && !hasScrolledToBottom) {
            hasScrolledToBottom = true;
            acceptBtn.style.display = 'block';
        }
    });

    // Reset scroll state when modal opens
    document.getElementById('exampleModal').addEventListener('show.bs.modal', function() {
        hasScrolledToBottom = false;
        acceptBtn.style.display = 'none';
        modalBody.scrollTop = 0;
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault(); // Prevent normal form submission
        
        try {
            const formData = new FormData(form);
            const response = await fetch('../api/check_uniqueness.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            console.log('Server response:', result); // Debug log

            if (result.status === 'unique') {
                await Swal.fire({
                    icon: 'success',
                    title: 'Verification Code Sent',
                    text: 'Please check your email for the verification code.',
                    confirmButtonText: 'Continue',
                    allowOutsideClick: false,
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
                window.location.href = 'signup-otp.php';
            } else {
                await Swal.fire({
                    icon: 'error',
                    title: 'Registration Failed',
                    text: result.message,
                    confirmButtonText: 'OK',
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
});
</script>
</body>
</html>
