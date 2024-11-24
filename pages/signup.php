<?php
session_start();
require __DIR__ . '/../configs/config.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/space-logo.png">
  <link rel="icon" type="image/png" href="../assets/img/space-logo.png">
  <title>Sign Up</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
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
                                    <form id="signupForm">
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
                                            <button type="button" class="btn btn-link position-absolute end-0 top-0 text-dark h-100" 
                                                    onclick="togglePassword('password')" style="z-index: 3;">
                                                <i class="material-symbols-rounded" id="password-toggle">visibility_off</i>
                                            </button>
                                        </div>
                                        <div class="form-check form-check-info text-start ps-0">
                                            <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" checked>
                                            <label class="form-check-label" for="flexCheckDefault">
                                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#exampleModal" class="text-dark font-weight-bolder">Terms and Conditions</a>
                                            </label>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-lg bg-gradient-primary btn-responsive btn-lg w-100 mt-2 mb-0" id="signupBtn">
                                                Sign Up
                                            </button>
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
    <div class="toast fade hide p-2 mt-2 bg-white" role="alert" aria-live="assertive" id="passwordToast" aria-atomic="true">
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
            <span id="lengthText" class="text-danger">At least 6 characters</span>
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

    <!-- Warning Toast (will appear above password toast) -->
    <div class="toast fade hide p-2 mt-2 bg-white" role="alert" aria-live="assertive" id="warningToast" aria-atomic="true">
      <div class="toast-header border-0">
        <i class="material-symbols-rounded text-warning me-2">warning</i>
        <span class="me-auto font-weight-bold">Invalid Input</span>
        <small class="text-body">just now</small>
        <i class="fas fa-times text-md ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close"></i>
      </div>
      <hr class="horizontal dark m-0">
      <div class="toast-body" id="warningToastMessage">
      </div>
    </div>
  </div>

  <!-- Core JS Files -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>

  <!-- Custom JS for Modal Functionality -->
  <script>
    // Custom JavaScript for your page

    function capitalizeFirstLetter(input) {
      const value = input.value;
      input.value = value.charAt(0).toUpperCase() + value.slice(1).toLowerCase();
    }

    function showWarningToast(message) {
      const toast = document.getElementById('warningToast');
      document.getElementById('warningToastMessage').textContent = message;
      
      // Dismiss any existing toast before showing new one
      const existingToast = bootstrap.Toast.getInstance(toast);
      if (existingToast) {
        existingToast.hide();
      }
      
      const bsToast = new bootstrap.Toast(toast, {
        delay: 3000 // Toast will show for 3 seconds
      });
      bsToast.show();
    }

    function validateEmail() {
      const emailInput = document.getElementById("email");
      const email = emailInput.value.trim();
      const regex = /^[a-zA-Z0-9._%+-]+@g\.batstate-u\.edu\.ph$/;
      
      if (email && !regex.test(email)) {
        showWarningToast("Please use your official @g.batstate-u.edu.ph email address");
        // Clear the invalid input after a short delay
        setTimeout(() => {
          emailInput.value = '';
          emailInput.focus();
        }, 1500);
        return false;
      }
      return true;
    }

    function validatePhone(input) {
      const phoneNumber = input.value.trim();
      const regex = /^09\d{9}$/;  // Matches 09 followed by exactly 9 digits
      
      // Check if number starts with 09
      if (phoneNumber.length >= 2 && !phoneNumber.startsWith('09')) {
        showWarningToast("Phone number must start with '09'");
        input.value = '';
        input.focus();
        return false;
      }
      
      // Check if number is incomplete
      if (phoneNumber.length !== 11) {
        showWarningToast("Please enter complete 11-digit phone number");
        input.focus();
        return false;
      }
      
      // Check if complete number matches format
      if (!regex.test(phoneNumber)) {
        showWarningToast("Please enter a valid Philippine phone number (e.g., 09123456789)");
        input.value = '';
        input.focus();
        return false;
      }

      return true;
    }

    // Add input and blur event listeners for real-time validation
    document.addEventListener('DOMContentLoaded', function() {
      const phoneInput = document.querySelector('input[name="phonenum"]');
      const form = document.getElementById('signupForm');
      
      phoneInput.addEventListener('input', function() {
        // Only allow numbers
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Check first two digits immediately
        if (this.value.length >= 2) {
          if (!this.value.startsWith('09')) {
            showWarningToast("Phone number must start with '09'");
            this.value = '';
            this.focus();
          }
        }
      });

      // Validate when user leaves the phone field
      phoneInput.addEventListener('blur', function() {
        if (this.value.trim().length > 0) {  // Only validate if there's input
          validatePhone(this);
        }
      });

      // Validate when user tries to move to next field using Tab
      phoneInput.addEventListener('keydown', function(e) {
        if (e.key === 'Tab' && this.value.trim().length > 0 && this.value.trim().length !== 11) {
          e.preventDefault();
          showWarningToast("Please enter complete 11-digit phone number");
          this.focus();
        }
      });

      // Add form submit validation
      form.addEventListener('submit', function(event) {
        const phoneInput = document.querySelector('input[name="phonenum"]');
        if (!validatePhone(phoneInput)) {
          event.preventDefault();
          return false;
        }
      });
    });

    // Initialize event listeners when document is ready
    document.addEventListener('DOMContentLoaded', function() {
      const emailInput = document.getElementById("email");
      
      // Only validate email when user finishes typing (leaves the field)
      emailInput.addEventListener('blur', function() {
        if (this.value.trim() !== '') {  // Only validate if field is not empty
          validateEmail();
        }
      });
    });

    // Update form validation
    function validateForm(event) {
      event.preventDefault(); // Prevent form from submitting
      
      const isEmailValid = validateEmail();
      const isPhoneValid = validatePhone(document.querySelector('input[type="text"][maxlength="11"]'));

      if (!isEmailValid || !isPhoneValid) {
        return false;
      }
      
      // If all validations pass, you can submit the form
      return true;
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

    document.addEventListener('DOMContentLoaded', function() {
      const modal = document.getElementById('exampleModal');
      const modalBody = document.getElementById('termsModalBody');
      const acceptBtn = document.getElementById('acceptTermsBtn');
      let hasScrolledToBottom = false;

      // Prevent modal from closing on backdrop click or escape key
      modal.addEventListener('hide.bs.modal', function(event) {
        if (!hasScrolledToBottom) {
          event.preventDefault();
          showTermsWarningToast();
        }
      });

      // Check scroll position
      modalBody.addEventListener('scroll', function() {
        const isAtBottom = modalBody.scrollHeight - modalBody.scrollTop <= modalBody.clientHeight + 50; // 50px threshold
        if (isAtBottom && !hasScrolledToBottom) {
          hasScrolledToBottom = true;
          acceptBtn.style.display = 'block';
        }
      });

      // Show warning toast
      function showTermsWarningToast() {
        const toast = document.getElementById('termsWarningToast');
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
      }

      // Reset scroll state when modal opens
      modal.addEventListener('show.bs.modal', function() {
        hasScrolledToBottom = false;
        acceptBtn.style.display = 'none';
        modalBody.scrollTop = 0;
      });
    });

    // Password validation function
    function validatePassword(password) {
      const lengthValid = password.length >= 6;
      const specialValid = /[!@#$%^&*()_+]/.test(password);
      const numberValid = /\d/.test(password);

      // Update icons and text colors
      updateRequirement('length', lengthValid);
      updateRequirement('special', specialValid);
      updateRequirement('number', numberValid);

      return lengthValid && specialValid && numberValid;
    }

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

    // Show password requirements toast
    function showPasswordToast() {
      const toast = document.getElementById('passwordToast');
      const bsToast = new bootstrap.Toast(toast, {
        autohide: false
      });
      bsToast.show();
    }

    // Initialize password field event listeners
    document.addEventListener('DOMContentLoaded', function() {
      const passwordInput = document.getElementById('password');
      
      // Show requirements when password field is focused
      passwordInput.addEventListener('focus', function() {
        showPasswordToast();
      });

      // Validate on each keystroke
      passwordInput.addEventListener('input', function() {
        validatePassword(this.value);
      });

      // Hide toast when password field loses focus
      passwordInput.addEventListener('blur', function() {
        if (validatePassword(this.value)) {
          const toast = bootstrap.Toast.getInstance(document.getElementById('passwordToast'));
          if (toast) {
            toast.hide();
          }
        }
      });
    });

    document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('signupForm');
    const termsCheckbox = document.getElementById('flexCheckDefault');
    const signupBtn = document.getElementById('signupBtn');

    // Function to handle terms checkbox
    function handleTermsChange() {
        if (!termsCheckbox.checked) {
            showWarningToast("Please accept the Terms and Conditions to continue");
            signupBtn.disabled = true;
            signupBtn.classList.add('opacity-50');
        } else {
            signupBtn.disabled = false;
            signupBtn.classList.remove('opacity-50');
        }
    }

    termsCheckbox.addEventListener('change', handleTermsChange);

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        // Get the phone input
        const phoneInput = document.querySelector('input[name="phonenum"]');

        // Validate all fields
        if (!validateEmail() || 
            !validatePhone(phoneInput) ||
            !validatePassword(document.getElementById('password').value)) {
            return false;
        }

        // Collect form data
        const formData = new FormData(this);

        try {
            const response = await fetch('../admin_operations/register.php', {
                method: 'POST',
                body: formData,
            });

            const result = await response.json();

            if (result.status === 'success') {
                const swalResult = await Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Your account has been successfully registered!',
                    showCancelButton: true,
                    confirmButtonText: 'Continue',
                    cancelButtonText: 'OK',
                    confirmButtonClass: 'btn bg-gradient-primary btn-sm mx-2',
                    cancelButtonClass: 'btn btn-outline-primary btn-sm mx-2',
                    buttonsStyling: false,
                    reverseButtons: true,
                    customClass: {
                        actions: 'justify-content-center',
                        confirmButton: 'btn bg-gradient-primary btn-sm mx-2',
                        cancelButton: 'btn btn-outline-primary btn-sm mx-2'
                    }
                });

                if (swalResult.isConfirmed) {
                    window.location.href = 'signin.php';
                } else {
                    // Reset form code...
                }
            } else {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Registration Failed',
                    text: result.message,
                    confirmButtonText: 'OK',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-sm bg-gradient-primary',
                        actions: 'justify-content-center'
                    }
                });
            }
        } catch (error) {
            await Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred during registration. Please try again.',
                confirmButtonText: 'OK',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-sm bg-gradient-primary',
                    actions: 'justify-content-center'
                }
            });
        }
    });
});

  </script>
  
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
async function submitForm(event) {
    event.preventDefault();

    // Validate all fields first
    if (!validateEmail() || !validatePhone(document.querySelector('input[name="phonenum"]')) || 
        !validatePassword(document.getElementById('password').value)) {
        return false;
    }

    const form = document.getElementById('signupForm');
    const formData = new FormData(form);

    try {
        const response = await fetch('../admin_operations/registration.php', {  // Updated path
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
            await Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: result.message,
                confirmButtonColor: '#3085d6'
            });
            // Redirect to login page or dashboard
            window.location.href = 'signin.php';
        } else {
            await Swal.fire({
                icon: 'warning',
                title: 'Registration Failed',
                text: result.message,
                confirmButtonColor: '#3085d6'
            });
        }
    } catch (error) {
        await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred during registration. Please try again.',
            confirmButtonColor: '#3085d6'
        });
    }

    return false;
}
</script>
  













</body>
</html>
