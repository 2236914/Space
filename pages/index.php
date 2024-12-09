<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/logo-space.png">
  <link rel="icon" type="image/png" href="../assets/img/logo-space.png">
  <title>
    Space
  </title>
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show  bg-white">
  <!------------------- NAV ----------------------->
  <div class="container position-sticky z-index-sticky top-0">
    <div class="row">
        <div class="col-12 px-0">
            <nav class="navbar navbar-expand-lg blur border-radius-xl top-0 z-index-fixed shadow position-absolute my-3 py-2 start-0 end-0 mx-auto">
                <div class="container-fluid">
                    <a class="navbar-brand font-weight-bolder ms-sm-3 d-flex align-items-center" href="index.php" rel="tooltip" data-placement="bottom">
                        <img src="../assets/img/logo-space.png" class="navbar-brand-img me-2" alt="Space Logo" style="height: 20px; width: auto;">
                        SPACE
                    </a>
                    <button class="navbar-toggler shadow-none ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon mt-2">
                            <span class="navbar-toggler-bar bar1"></span>
                            <span class="navbar-toggler-bar bar2"></span>
                            <span class="navbar-toggler-bar bar3"></span>
                        </span>
                    </button>
                    <div class="collapse navbar-collapse pt-3 pb-2 py-lg-0" id="navigation">
                        <ul class="navbar-nav navbar-nav-hover ms-lg-12 ps-lg-5 w-100">
                            <li class="mx-2">
                                <a href="#home" class="nav-link ps-2 d-flex cursor-pointer align-items-center">
                                    <i class="material-symbols-rounded opacity-6 me-2 text-md">home</i>
                                    Home
                                </a>
                            </li>
                            <li class="nav-item mx-2">
                                <a href="#about" class="nav-link ps-2 d-flex cursor-pointer align-items-center">
                                    <i class="material-symbols-rounded opacity-6 me-2 text-md">info_i</i>
                                    About
                                </a>
                            </li>
                            <li class="nav-item mx-2">
                                <a href="#team" class="nav-link ps-2 d-flex cursor-pointer align-items-center">
                                    <i class="material-symbols-rounded opacity-6 me-2 text-md">diversity_1</i>
                                    Team
                                </a>
                            </li>
                            <li class="nav-item mx-2">
                                <a href="#therapy" class="nav-link ps-2 d-flex cursor-pointer align-items-center">
                                    <i class="material-symbols-rounded opacity-6 me-2 text-md">psychiatry</i>
                                    Therapy
                                </a>
                            </li>
                            <li class="nav-item mx-2">
                                <a href="#contact" class="nav-link ps-2 d-flex cursor-pointer align-items-center">
                                    <i class="material-symbols-rounded opacity-6 me-2 text-md">call</i>
                                    Contact Us
                                </a>
                            </li>
                            <li class="nav-item ms-lg-auto my-auto ms-3 ms-lg-0">
                              <div class="dropdown">
                                <button class="btn btn-sm bg-gradient-primary mb-0 me-1 mt-2 mt-md-0" 
                                        type="button"
                                        id="dropdownMenuPages" 
                                        data-bs-toggle="dropdown" 
                                        aria-expanded="false">
                                  SIGN IN
                                </button>
                                <ul class="dropdown-menu dropdown-menu-animation ms-n3 dropdown-md p-3 border-radius-lg mt-0 mt-lg-3" 
                                    aria-labelledby="dropdownMenuPages">
                                  <li class="d-none d-lg-block">
                                    <a href="signin.php" class="dropdown-item border-radius-md">Sign In</a>
                                    <a href="signup.php" class="dropdown-item border-radius-md">Sign Up</a>
                                    <a href="forgotpassword.php" class="dropdown-item border-radius-md">Reset Password</a>
                                  </li>
                                  <li class="d-lg-none">
                                    <a href="signin.php" class="dropdown-item border-radius-md">Sign In</a>
                                    <a href="signup.php" class="dropdown-item border-radius-md">Sign Up</a>
                                    <a href="forgotpassword.php" class="dropdown-item border-radius-md">Reset Password</a>
                                  </li>
                                </ul>
                              </div> 
                          </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </div>
  </div>
  <main>
    <!------------------- HEADER/HOME ----------------------->
  <section class="py-3" id="home">
    <div class="container">
      <div class="row align-items-center min-vh-100 py-5 mx-0">
        <!-- Left column with text -->
        <div class="col-lg-6 col-md-12 d-flex justify-content-center flex-column mb-lg-0 mb-5 text-lg-start text-center">
          <h1 class="font-weight-black mb-4" style="font-size: clamp(2.5rem, 5vw, 4.5rem);">Space</h1>
          <h5 class="text-muted px-lg-0 px-3">
            Prioritize your well-being with professional support, self-care tools, and a community that understands.
          </h5>
          <div class="buttons d-flex gap-3 justify-content-lg-start justify-content-center">
              <a href="#about" class="btn bg-gradient-primary btn-responsive mt-4">Discover</a>
              <a href="signup.php" type="button" class="btn btn-outline-primary btn-responsive shadow-none mt-4">Join Space</a>
          </div>
        </div>
        <!-- Right column with image -->
        <div class="col-lg-6 col-md-12 text-center">
          <img src="../assets/img/happy.png" 
               class="img-fluid" 
               alt="Space Header Image" 
               loading="lazy"
               style="max-width: 80%; width: auto; height: auto;">
        </div>
      </div>
    </div>
  </section>
    <!------------------- ABOUT  ----------------------->
    <section class="py-4" id="about">
      <div class="container">
        <div class="row align-items-center py-5 mx-0">
          <div class="col-lg-6 col-md-12 d-flex justify-content-center flex-column mb-lg-0 mb-5 text-lg-start text-center">
            <h3 class="mb-4">Find your safe space</h3>
            <p class="mb-4">Space is a web-based system providing BSU TNEU students with accessible therapy, mood tracking, and self-care tools. Empowering you to prioritize your mental health, Space offers:</p>
            <ul class="mb-4 px-lg-0 px-3" style="margin-left: 20px;">
              <li class="mb-2">Licensed Therapy: Secure, on-demand sessions.</li>
              <li class="mb-2">Mood Tracking: Monitor and understand your emotions.</li>
              <li class="mb-2">Self-Care Tools: Mindfulness exercises, journaling, and more.</li>
            </ul>
            <h3 class="mb-4">We're here for you</h3>
            <p>Join a supportive community that puts your well-being first—because your mental health matters.</p>
            <p class="blockquote my-4 ms-2">
              <span class="text-bold"> "It's okay to not be okay. What matters is taking that first step to heal."</span>
              <br>
              <small class="blockquote-footer">
                 Space Team.
              </small>
            </p>
          </div>
          <div class="col-lg-6 col-md-12 d-flex justify-content-center align-items-center">
            <img src="../assets/img/test.png" 
                 class="img-fluid" 
                 alt="Space About Image" 
                 loading="lazy"
                 style="max-width: 80%; width: auto; height: auto; border-radius: 10px;">
          </div>
        </div>
      </div>
    </section>
    <!------------------- TEAM/DEVELOPERS  ----------------------->
    <section class="py-5" id="team">
        <div class="container">
          <div class="row align-items-center py-5 mx-0">
            <div class="col-12 mb-5">
              <div class="icon icon-shape icon-md bg-gradient-primary shadow text-center mb-3">
                <i class="material-symbols-rounded opacity-10">supervisor_account</i>
              </div>
              <h3 class="mb-auto">CreoTech</h3>
              <div class="col-12 mb-5">
              <p style="max-width: 100%;">
                Meet the brilliant minds behind CreoTech, the driving force behind Space. Our team is dedicated to blending technology and compassion to create an innovative platform that empowers student mental health.
              </p>
            </div>
          </div>
          <div class="row mx-3 pt-6">
            <div class="col-lg-4 col-md-6 pb-5">
              <div class="card card-profile card-plain">
                <div class="text-start mt-n5">
                  <div class="position-relative w-25">
                    <div class="blur-shadow-avatar">
                      <img class="avatar avatar-xxl border-radius-xl" src="../assets/img/faithanne.png">
                    </div>
                  </div>
                </div>
                <div class="card-body ps-0">
                  <h5 class="mb-0">Faith Anne Banares</h5>
                  <p class="text-muted">CreoTech</p>
                </div>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 pb-5">
              <div class="card card-profile card-plain">
                <div class="text-start mt-n5">
                  <div class="position-relative w-25">
                    <div class="blur-shadow-avatar">
                      <img class="avatar avatar-xxl border-radius-xl" src="../assets/img/alethea.jpg">
                    </div>
                  </div>
                </div>
                <div class="card-body ps-0">
                  <h5 class="mb-0">Alethea Malata</h5>
                  <p class="text-muted">CreoTech</p>
                </div>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 pb-5">
              <div class="card card-profile card-plain">
                <div class="text-start mt-n5">
                  <div class="position-relative w-25">
                    <div class="blur-shadow-avatar">
                      <img class="avatar avatar-xxl border-radius-xl" src="../assets/img/gwy.jpg">
                    </div>
                  </div>
                </div>
                <div class="card-body ps-0">
                  <h5 class="mb-0">Gertrude Gwyn Maralit</h5>
                  <p class="text-muted">CreoTech</p>
                </div>
              </div>
            </div>
          </div>
        </div>
    </section>
    <!------------------- THERAPY/APPLICATION  ----------------------->
    <section class="py-5" id="therapy">
      <div class="container">
          <div class="row align-items-center py-5 mx-0">
              <div class="col-lg-6 col-md-12">
                  <div class="row justify-content-start g-4">
                      <div class="col-md-6">
                          <div class="info">
                              <i class="material-symbols-rounded text-3xl text-gradient text-primary mb-3">verified</i>
                              <h5>Licensed Professionals</h5>
                              <p>Work with certified psychologists and licensed therapists dedicated to supporting student mental health and well-being.</p>
                          </div>
                      </div>
                      <div class="col-md-6">
                          <div class="info">
                              <i class="material-symbols-rounded text-3xl text-gradient text-primary mb-3">schedule</i>
                              <h5>Flexible Online Sessions</h5>
                              <p>Easily schedule therapy sessions tailored to your needs, ensuring accessibility and privacy.</p>
                          </div>
                      </div>
                      <div class="col-md-6">
                          <div class="info">
                              <i class="material-symbols-rounded text-3xl text-gradient text-primary mb-3">support</i>
                              <h5>Community Support</h5>
                              <p>Join a community that fosters understanding and growth through guided discussions and resources.</p>
                          </div>
                      </div>
                      <div class="col-md-6">
                          <div class="info">
                              <i class="material-symbols-rounded text-3xl text-gradient text-primary mb-3">insights</i>
                              <h5>Growth Opportunities</h5>
                              <p>Access tools and resources for managing stress and enhancing self-care practices.</p>
                          </div>
                      </div>
                  </div>
              </div>
              <div class="col-lg-4 ms-auto mt-lg-0 mt-4">
                  <div class="card">
                      <div class="card-header p-0 position-relative mt-2 mx-2 z-index-2">
                          <a class="d-block blur-shadow-image">
                              <img src="../assets/img/samplebg.png" 
                                   alt="img-colored-shadow" 
                                   class="img-fluid border-radius-lg">
                          </a>
                      </div>
                      <div class="card-body text-center p-4">
                          <h5 class="font-weight-normal">
                              <a href="javascript:;">Join Our Mental Health Team!</a>
                          </h5>
                          <p class="mb-0">
                              We're expanding our network of licensed therapists to better serve our students. Collaborate with us to make a meaningful impact in the field of mental health.
                          </p>
                          <button type="button" class="btn bg-gradient-primary btn-sm mb-0 mt-3" onclick="showTherapistApplicationForm()">
                            Apply Now
                          </button>
                      </div>
                  </div>
              </div>
          </div>
      </div>
    </section>
    <!------------------- CONTACT  ----------------------->
    <section class="py-3" id="contact">
      <div class="page-header min-vh-100">
          <div class="container">
              <div class="row">
                  <!-- Left Side: Image -->
                  <div class="col-lg-6 col-md-8 d-flex justify-content-center align-items-center mx-auto">
                    <img src="../assets/img/contact.png" 
                         alt="Contact Us" 
                         class="img-fluid" 
                         style="border-radius: 10px; max-width: 80%;">
                  </div>
                  <!-- Right Side: Contact Form -->
                  <div class="col-lg-6 col-md-8 ms-auto me-auto">
                    <div class="card d-flex blur justify-content-center my-sm-0 my-sm-6 mt-8 mb-5 border-0"> 
                        <div class="card-header p-0 position-relative mt-2 mx-2 z-index-2 bg-transparent text-center">
                            <h3>Contact Us</h3>
                            <p class="text-sm mb-0">For further questions contact us using the form below.</p>
                        </div>
                        <div class="card-body">
                            <form id="contact-form" method="post" autocomplete="off">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="input-group input-group-dynamic mb-4">
                                                <label class="form-label">First Name</label>
                                                <input class="form-control" type="text" name="firstName" id="firstName">
                                            </div>
                                        </div>
                                        <div class="col-md-6 ps-2">
                                            <div class="input-group input-group-dynamic">
                                                <label class="form-label">Last Name</label>
                                                <input type="text" class="form-control" name="lastName" id="lastName" aria-label="Last Name..." required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="input-group input-group-dynamic">
                                            <label class="form-label">Email Address</label>
                                            <input type="email" class="form-control" name="email" id="email" required>
                                        </div>
                                    </div>
                                    <div class="input-group mb-4 input-group-static">
                                        <label>Your message</label>
                                        <textarea name="message" class="form-control" id="message" rows="4" required></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-check form-switch mb-4 d-flex align-items-center">
                                                <input class="form-check-input" type="checkbox" id="terms">
                                                <label class="form-check-label ms-3 mb-0" for="terms">
                                                    I agree to the <button type="button" onclick="openTermsModal()" class="btn btn-link p-0 text-dark text-decoration-underline border-0" style="vertical-align: baseline;"><u>Terms and Conditions</u></button>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <button type="submit" class="btn bg-gradient-primary w-100">Send to Space</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
              </div>
          </div>
      </div>
    </section>
    <!------------------- FAQ  ----------------------->
    <section class="py-4">
      <div class="container">
        <div class="row my-5">
          <div class="col-md-6 mx-auto text-center">
            <h2>Frequently Asked Questions</h2>
            <p>Find answers to common questions about using the Space platform for mental health support.</p>
          </div>
        </div>
        <div class="row">
          <div class="col-md-10 mx-auto">
            <div class="accordion" id="accordionRental">
              <div class="accordion-item mb-3">
                <h5 class="accordion-header" id="headingOne">
                  <button class="accordion-button border-bottom font-weight-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    What features does the Space platform offer for mental health support?
                    <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0"></i>
                    <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0"></i>
                  </button>
                </h5>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionRental">
                  <div class="accordion-body text-sm opacity-8">
                    Space provides virtual therapy sessions, mood tracking, and self-care tools to help students manage their mental well-being. 
                  </div>
                </div>
              </div>
              <div class="accordion-item mb-3">
                <h5 class="accordion-header" id="headingTwo">
                  <button class="accordion-button border-bottom font-weight-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    How can I book a therapy session on the Space platform?
                    <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0"></i>
                    <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0"></i>
                  </button>
                </h5>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionRental">
                  <div class="accordion-body text-sm opacity-8">
                    Students can book therapy sessions directly through the dashboard by checking available dates and scheduling an appointment.
                  </div>
                </div>
              </div>
              <div class="accordion-item mb-3">
                <h5 class="accordion-header" id="headingThree">
                  <button class="accordion-button border-bottom font-weight-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    Is my information safe on the Space platform?
                    <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0"></i>
                    <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0"></i>
                  </button>
                </h5>
                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionRental">
                  <div class="accordion-body text-sm opacity-8">
                    Yes, Space prioritizes user privacy and data security to ensure all personal information and interactions remain confidential.
                  </div>
                </div>
              </div>
              <div class="accordion-item mb-3">
                <h5 class="accordion-header" id="headingFour">
                  <button class="accordion-button border-bottom font-weight-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                    How does mood tracking work on the platform?
                    <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0"></i>
                    <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0"></i>
                  </button>
                </h5>
                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionRental">
                  <div class="accordion-body text-sm opacity-8">
                    The mood tracker allows students to log and monitor their emotional states over time, helping them understand patterns in their mental health.
                  </div>
                </div>
              </div>
              <div class="accordion-item mb-3">
                <h5 class="accordion-header" id="headingFifth">
                  <button class="accordion-button border-bottom font-weight-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFifth" aria-expanded="false" aria-controls="collapseFifth">
                    Can I get reminders for upcoming therapy sessions?
                    <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0"></i>
                    <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0"></i>
                  </button>
                </h5>
                <div id="collapseFifth" class="accordion-collapse collapse" aria-labelledby="headingFifth" data-bs-parent="#accordionRental">
                  <div class="accordion-body text-sm opacity-8">
                    Yes, Space sends automated SMS or email reminders for upcoming sessions to ensure students don't miss their appointments.
                  </div>
                </div>
              </div>
              <div class="accordion-item mb-3">
                <h5 class="accordion-header" id="headingSix">
                  <button class="accordion-button border-bottom font-weight-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                    What should I do if I forget my password?
                    <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0"></i>
                    <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0"></i>
                  </button>
                </h5>
                <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#accordionRental">
                  <div class="accordion-body text-sm opacity-8">
                    You can reset your password by providing your username and phone number, then verifying your identity through an OTP sent to your device.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
<!-- Terms & Conditions in Contact Form -->
<div class="modal fade" id="termsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title font-weight-normal" id="termsModalLabel">Terms and Conditions</h5>
      </div>
      <div class="modal-body p-5" id="termsModalBody" style="max-height: 400px; overflow-y: auto;">
        <p class="text-xs mb-0"><strong>Welcome to Space: A Web-Based Mental Health Therapy System for Students of BSU TNEU Lipa ("Space").</strong> By using our contact form, you agree to comply with and be bound by the following terms and conditions. Please read them carefully.</p>

        <p class="text-xs mb-0 mt-3"><strong>1. Acceptance of Terms</strong></p>
        <p class="text-xs mb-0">By submitting this contact form, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions.</p>

        <p class="text-xs mb-0 mt-3"><strong>2. Information Collection</strong></p>
        <p class="text-xs mb-0">When using our contact form, you agree to:</p>
        <ul>
          <li class="text-xs mb-0">Provide accurate and truthful information</li>
          <li class="text-xs mb-0">Submit information only for legitimate inquiries</li>
          <li class="text-xs mb-0">Not use false or misleading contact details</li>
        </ul>

        <p class="text-xs mb-0 mt-3"><strong>3. Privacy & Data Protection</strong></p>
        <p class="text-xs mb-0">By submitting the contact form, you acknowledge that:</p>
        <ul>
          <li class="text-xs mb-0">Your information will be stored securely in our database</li>
          <li class="text-xs mb-0">We will use your contact details only to respond to your inquiry</li>
          <li class="text-xs mb-0">We will not share your information with unauthorized third parties</li>
          <li class="text-xs mb-0">You have the right to request deletion of your submitted data</li>
        </ul>

        <p class="text-xs mb-0 mt-3"><strong>4. Communication</strong></p>
        <p class="text-xs mb-0">By submitting this form, you understand that:</p>
        <ul>
          <li class="text-xs mb-0">We will contact you via the email address provided</li>
          <li class="text-xs mb-0">Response time may take up to 24 hours</li>
          <li class="text-xs mb-0">This is not an emergency service - for urgent mental health support, please contact emergency services</li>
          <li class="text-xs mb-0">Communications will be professional and relevant to your inquiry</li>
        </ul>

        <p class="text-xs mb-0 mt-3"><strong>5. Prohibited Content</strong></p>
        <p class="text-xs mb-0">When using the contact form, you agree not to submit:</p>
        <ul>
          <li class="text-xs mb-0">Spam or automated messages</li>
          <li class="text-xs mb-0">Harmful, offensive, or discriminatory content</li>
          <li class="text-xs mb-0">False or misleading information</li>
          <li class="text-xs mb-0">Content that violates any applicable laws</li>
        </ul>

        <p class="text-xs mb-0 mt-3"><strong>6. Intellectual Property</strong></p>
        <p class="text-xs mb-0">All content on this website, including but not limited to text, graphics, logos, and images, is the property of Space and is protected by intellectual property laws.</p>

        <p class="text-xs mb-0 mt-3"><strong>7. Limitation of Liability</strong></p>
        <p class="text-xs mb-0">While we strive to provide timely and helpful responses, Space is not liable for any damages arising from the use of this contact form or any subsequent communications.</p>

        <p class="text-xs mb-0 mt-3"><strong>8. Contact</strong></p>
        <p class="text-xs mb-0">For questions about these terms, please contact us at <a href="mailto:space.creotech@gmail.com">space.creotech@gmail.com</a></p>

        <!-- Accept button -->
        <div class="text-end mt-4">
          <button type="button" class="btn bg-gradient-primary" data-bs-dismiss="modal">I Understand</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="tncModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="tncModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content"> 
      <div class="modal-header">
        <h5 class="modal-title font-weight-normal" id="tncModalLabel">Terms and Conditions</h5>
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
          <li class="text-xs mb-0"><strong>Virtual Therapy Sessions:</strong> Students can schedule therapy sessions with licensed professionals via the platform's calendar. Therapy sessions are conducted using secure video conferencing.</li>
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
          <li class="text-xs mb-0"><strong>Payment Methods:</strong> All payments must be made through the platform's secure payment gateway.</li>
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

        <div class="text-end mt-4">
          <button type="button" class="btn bg-gradient-primary" onclick="closeTncModal()">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Warning Toast -->
<div class="position-fixed bottom-1 end-1 z-index-2">
    <div class="toast fade hide p-2 bg-white" role="alert" id="warningToast" aria-atomic="true">
        <div class="toast-header border-0">
            <i class="material-symbols-rounded text-warning me-2">warning</i>
            <span class="me-auto font-weight-bold">Terms & Conditions Required</span>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <hr class="horizontal dark m-0">
        <div class="toast-body">
            Please accept the Terms and Conditions to continue.
        </div>
    </div>
</div>
  
  <!------------------- FOOTER  ----------------------->
  <footer class="footer pt-5 mt-5">
    <div class="container">
      <div class="row">
        <!-- Logo, Team Info, and Description -->
        <div class="col-md-4 mb-4 ms-auto">
          <div>
            <a href="#">
              <img src="../assets/img/logo-space.png" class="mb-3 footer-logo" alt="main_logo">
            </a>
            <h6 class="font-weight-bolder mb-4">CreoTech</h6>
          </div>
          <p class="small">
            Created by third-year BSIT students majoring in Service Management as part of an academic group project at BSU TNEU.
          </p>
          <div>
            <ul class="d-flex flex-row ms-n3 nav">
              <li class="nav-item">
                <a class="nav-link pe-1" href="https://www.facebook.com/" target="_blank">
                  <i class="fab fa-facebook text-lg opacity-8"></i>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link pe-1" href="https://twitter.com/" target="_blank">
                  <i class="fab fa-twitter text-lg opacity-8"></i>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link pe-1" href="https://github.com/" target="_blank">
                  <i class="fab fa-github text-lg opacity-8"></i>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link pe-1" href="https://www.youtube.com/" target="_blank">
                  <i class="fab fa-youtube text-lg opacity-8"></i>
                </a>
              </li>
            </ul>
          </div>
        </div>
  
        <!-- About Section -->
        <div class="col-md-2 col-sm-6 col-6 mb-4">
          <div>
            <h6 class="text-sm">About</h6>
            <ul class="flex-column ms-n3 nav">
              <li class="nav-item">
                <a class="nav-link" href="#team">
                  Meet the Team
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#project">
                  Our Project
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#mission">
                  Mission & Goals
                </a>
              </li>
            </ul>
          </div>
        </div>
  
        <!-- Help & Support Section -->
        <div class="col-md-2 col-sm-6 col-6 mb-4">
          <div>
            <h6 class="text-sm">Help & Support</h6>
            <ul class="flex-column ms-n3 nav">
              <li class="nav-item">
                <a class="nav-link" href="#contact">
                  Contact Us
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#faq">
                  FAQs
                </a>
              </li>
            </ul>
          </div>
        </div>
  
        <!-- Legal Section -->
        <div class="col-md-2 col-sm-6 col-6 mb-4 me-auto">
          <div>
            <h6 class="text-sm">Legal</h6>
            <ul class="flex-column ms-n3 nav">
              <li class="nav-item">
                <a href="javascript:;" onclick="openTncModal()" class="nav-link">
                  Terms & Conditions
                </a>
              </li>
            </ul>
          </div>
        </div>
  
        <!-- Footer Bottom Text -->
        <div class="col-12">
          <div class="text-center">
            <p class="text-dark my-4 text-sm font-weight-normal">
              &copy; 2024 CreoTech. All rights reserved.  
              This project is for academic purposes only as part of the BSU TNEU curriculum.
            </p>
          </div>
        </div>
      </div>
    </div>
  </footer>
  
  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/chartjs.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../assets/js/scrollreveal.min.js"></script>
  <script src="../assets/js/applytherapy.js"></script>
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
  <script src="../assets/js/animations.js"></script>
  <script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modal with new ID
    const tncModal = new bootstrap.Modal(document.getElementById('tncModal'));
    
    // Function to open modal
    window.openTncModal = function() {
        tncModal.show();
    }

    // Function to close modal
    window.closeTncModal = function() {
        tncModal.hide();
    }

    // Add keyboard event listener to close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            tncModal.hide();
        }
    });
});
</script>
  <script>
    const initializeForm = () => {
        const form = document.getElementById('contact-form');
        if (!form || form.hasAttribute('data-initialized')) return;
        
        // Mark the form as initialized
        form.setAttribute('data-initialized', 'true');
        
        // Function to show warning toast
        const showWarningToast = () => {
            const toast = document.getElementById('warningToast');
            if (!toast) {
                console.error('Warning toast element not found');
                return;
            }
            const bsToast = new bootstrap.Toast(toast, {
                delay: 3000 // Toast will show for 3 seconds
            });
            bsToast.show();
        };

        // Handle terms checkbox change
        const termsCheckbox = document.getElementById('terms');
        const submitBtn = form.querySelector('button[type="submit"]');

        // Initial state check
        submitBtn.disabled = !termsCheckbox.checked;
        submitBtn.classList.toggle('opacity-50', !termsCheckbox.checked);

        const handleTermsChange = () => {
            submitBtn.disabled = !termsCheckbox.checked;
            submitBtn.classList.toggle('opacity-50', !termsCheckbox.checked);
            if (!termsCheckbox.checked) {
                showWarningToast();
            }
        };

        // Add event listener to checkbox
        termsCheckbox.addEventListener('change', handleTermsChange);
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault(); // Prevent default form submission

            // Validate all required fields
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                return;
            }

            // Check terms
            if (!termsCheckbox.checked) {
                showWarningToast();
                return;
            }

            if (form.hasAttribute('data-submitting')) return;
            form.setAttribute('data-submitting', 'true');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';

            try {
                const formData = new FormData(this);
                
                const response = await fetch('../admin_operations/handle_contact.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const result = await response.json();

                if (result.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Welcome to Space!',
                        text: 'Thank you for sending your inquiry. We\'ll contact you within 24 hours.',
                        confirmButtonText: 'Okay',
                        customClass: {
                            confirmButton: 'btn bg-gradient-primary'
                        }
                    }).then(() => {
                        form.reset();
                        termsCheckbox.checked = false;
                        handleTermsChange();
                    });
                } else {
                    throw new Error(result.message || 'Failed to send message');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: error.message || 'Something went wrong!',
                    confirmButtonText: 'Try Again',
                    customClass: {
                        confirmButton: 'btn bg-gradient-primary'
                    }
                });
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Send Message';
                form.removeAttribute('data-submitting');
            }
        });
    };

    // Initialize form when DOM is loaded
    document.addEventListener('DOMContentLoaded', initializeForm);
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
    <script>
  document.addEventListener('DOMContentLoaded', function() {
      // Initialize all modals
      var modals = document.querySelectorAll('.modal');
      modals.forEach(function(modal) {
          new bootstrap.Modal(modal);
      });

      // Optional: Auto-check terms when user clicks "I Understand"
      const termsModal = document.getElementById('termsModal');
      if (termsModal) {
          termsModal.addEventListener('hidden.bs.modal', function () {
              document.getElementById('terms').checked = true;
              // Trigger change event to update button state
              document.getElementById('terms').dispatchEvent(new Event('change'));
          });
      }
  });
  </script>
  <script>
function openTermsModal() {
    const termsModal = new bootstrap.Modal(document.getElementById('termsModal'));
    termsModal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize modal
    const termsModal = document.getElementById('termsModal');
    if (termsModal) {
        const modal = new bootstrap.Modal(termsModal);
        
        // Handle "I Understand" button click
        termsModal.addEventListener('hidden.bs.modal', function () {
            document.getElementById('terms').checked = true;
            document.getElementById('terms').dispatchEvent(new Event('change'));
        });
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dynamicInputs = document.querySelectorAll('.input-group-dynamic input');
    
    dynamicInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('is-focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('is-focused');
            if (this.value) {
                this.parentElement.classList.add('is-filled');
            } else {
                this.parentElement.classList.remove('is-filled');
            }
        });
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get references to elements
    const termsLink = document.querySelector('a[onclick="openTermsModal()"]');
    const termsModal = document.getElementById('termsModal');
    const termsCheckbox = document.getElementById('terms');
    
    // Initialize the modal
    const modal = new bootstrap.Modal(termsModal);
    
    // Function to open modal
    window.openTermsModal = function() {
        modal.show();
    }
    
    // Handle modal events
    termsModal.addEventListener('hidden.bs.modal', function () {
        termsCheckbox.checked = true;
        // Trigger change event to update button state
        termsCheckbox.dispatchEvent(new Event('change'));
    });
    
    // Handle checkbox click
    termsCheckbox.addEventListener('change', function() {
        if (!this.checked) {
            modal.show();
        }
    });
});
</script>
</body>

</html>