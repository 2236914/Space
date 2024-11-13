<?php
session_start();
require __DIR__ . '/../configs/config.php';

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Sign Up</title>

  <!-- External CSS Links -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body>
  <main class="main-content mt-0">
    <section>
      <div class="page-header min-vh-100">
        <div class="container">
          <div class="row">
            <div class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 start-0 text-center justify-content-center flex-column">
              <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center" style="background-image:url('https://img.freepik.com/free-vector/house-plants-illustration_23-2150199898.jpg?t=st=1730460898~exp=1730464498~hmac=8084ca410209afef07616134267bd295cd9f7d36c3d713d18ab268f23ad12e31&w=740'); background-size: cover;">
              </div>
            </div>
            <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column ms-auto me-auto ms-lg-auto me-lg-5">
              <div class="card card-plain">
                <div class="card-header">
                  <h4 class="font-weight-bolder">Sign Up</h4>
                  <p class="mb-0">Enter your information to register</p>
                </div>
                <div class="card-body">
                  <form role="form" onsubmit="return validateForm()">
                    <div class="input-group input-group-outline mb-3">
                      <label class="form-label">First Name</label>
                      <input type="text" class="form-control" required oninput="capitalizeFirstLetter(this)">
                    </div>
                    <div class="input-group input-group-outline mb-3">
                      <label class="form-label">Last Name</label>
                      <input type="text" class="form-control" required oninput="capitalizeFirstLetter(this)">
                    </div>
                    <div class="input-group input-group-outline mb-3">
                      <label class="form-label">SR-Code</label>
                      <input type="text" class="form-control" required oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                    </div>
                    <div class="input-group input-group-outline mb-3">
                      <label class="form-label">Phone Number</label>
                      <input type="text" class="form-control" required oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                    </div>
                    <div class="input-group input-group-outline mb-3">
                      <label class="form-label">Email</label>
                      <input type="email" id="email" class="form-control" required pattern="^[a-zA-Z0-9._%+-]+@g\.batstate-u\.edu\.ph$" title="Format: username@g.batstate-u.edu.ph (username can contain letters, numbers, and . _ % + -)" oninput="validateEmail()">
                    </div>
                    <div class="input-group input-group-outline mb-3">
                      <label class="form-label">Password</label>
                      <input type="password" id="password" class="form-control" required>
                    </div>
                    <div id="passwordMessage" class="text-danger"></div>
                    <div class="form-check form-check-info text-start ps-0">
                      <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" checked>
                      <label class="form-check-label" for="flexCheckDefault">
                        I agree to the <a href="javascript:;" class="text-dark font-weight-bolder" data-bs-toggle="modal" data-bs-target="#exampleModal">Terms and Conditions</a>
                      </label>
                    </div>
                    <div class="text-center">
                      <button type="button" class="btn btn-primary w-100 mt-4 mb-0 btn-responsive" data-bs-toggle="modal" data-bs-target="#exampleModal">Sign Up</button>
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
  <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content"> 
  <div class="modal-header">
    <h5 class="modal-title font-weight-normal" id="exampleModalLabel">Terms and Conditions</h5>
    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
  <div class="modal-body p-5" style="max-height: 400px; overflow-y: auto;">
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
  </div>
  <div class="modal-footer">
    <button type="button" class="btn text-bg-primary" data-bs-dismiss="modal">Accept</button>
  </div>
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
        return true;
      }
    }
  </script>

  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>

  <!-- Control Center for Material Dashboard -->
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>
