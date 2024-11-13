<?php
session_start();
require __DIR__ . '/../configs/config.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>
    Space
  </title>
  <!--     Fonts and icons     -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <!-- Nucleo Icons -->
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <!-- CSS Files -->
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show  bg-white">
  <!------------------- NAV ----------------------->
  <div class="container position-sticky z-index-sticky top-0">
    <div class="row">
        <div class="col-12">
            <nav class="navbar navbar-expand-lg blur border-radius-xl top-0 z-index-fixed shadow position-absolute my-3 py-2 start-0 end-0 mx-4">
                <div class="container-fluid">
                    <a class="navbar-brand font-weight-bolder ms-sm-3" href="https://demos.creative-tim.com/material-kit/presentation" rel="tooltip" title="Designed and Coded by Creative Tim" data-placement="bottom" target="_blank">
                        Space.
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
                            <li class="nav-item dropdown dropdown-hover mx-2">
                                <a class="nav-link ps-2 d-flex cursor-pointer align-items-center" id="dropdownMenuPages" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="material-symbols-rounded opacity-6 me-2 text-md">dashboard</i>
                                    Home
                                </a>
                                <div class="dropdown-menu dropdown-menu-animation ms-n3 dropdown-md p-3 border-radius-lg mt-0 mt-lg-3" aria-labelledby="dropdownMenuPages">
                                    <div class="d-none d-lg-block">
                                        <a href="javascript:;" class="dropdown-item border-radius-md">About Us</a>
                                        <a href="javascript:;" class="dropdown-item border-radius-md">Contact Us</a>
                                        <a href="javascript:;" class="dropdown-item border-radius-md">Author</a>
                                        <a href="javascript:;" class="dropdown-item border-radius-md">Sign In</a>
                                    </div>
                                    <div class="d-lg-none">
                                        <a href="javascript:;" class="dropdown-item border-radius-md">About Us</a>
                                        <a href="javascript:;" class="dropdown-item border-radius-md">Contact Us</a>
                                        <a href="javascript:;" class="dropdown-item border-radius-md">Author</a>
                                        <a href="javascript:;" class="dropdown-item border-radius-md">Sign In</a>
                                    </div>
                                </div>
                            </li>

                            <li class="nav-item dropdown dropdown-hover mx-2">
                                <a class="nav-link ps-2 d-flex cursor-pointer align-items-center" id="dropdownMenuBlocks" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="material-symbols-rounded opacity-6 me-2 text-md">view_day</i>
                                    Sections
                                    <img src="./assets/img/down-arrow-dark.svg" alt="down-arrow" class="arrow ms-auto">
                                </a>
                                <ul class="dropdown-menu dropdown-menu-animation dropdown-lg dropdown-lg-responsive p-3 border-radius-lg mt-0 mt-lg-3" aria-labelledby="dropdownMenuBlocks">
                                    <div class="d-none d-lg-block">
                                        <li class="nav-item dropdown dropdown-hover dropdown-subitem">
                                            <a class="dropdown-item py-2 ps-3 border-radius-md" href="./presentation.html">
                                                <div class="d-flex">
                                                    <div class="icon h-10 me-3 d-flex mt-1">
                                                        <i class="ni ni-single-copy-04 text-gradient text-primary"></i>
                                                    </div>
                                                    <div class="w-100 d-flex align-items-center justify-content-between">
                                                        <div>
                                                            <h6 class="dropdown-header text-dark font-weight-bolder d-flex justify-content-center align-items-center p-0">Page Sections</h6>
                                                            <span class="text-sm">See all 109 sections</span>
                                                        </div>
                                                        <img src="./assets/img/down-arrow.svg" alt="down-arrow" class="arrow">
                                                    </div>
                                                </div>
                                            </a>
                                            <div class="dropdown-menu mt-0 py-3 px-2 mt-3" aria-labelledby="pageSections">
                                                <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Page Headers</a>
                                                <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Features</a>
                                                <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Pricing</a>
                                                <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">FAQ</a>
                                                <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Blog Posts</a>
                                                <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Testimonials</a>
                                                <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Teams</a>
                                                <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Stats</a>
                                                <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Call to Actions</a>
                                                <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Logo Areas</a>
                                            </div>
                                        </li>
                                    </div>
                                    <div class="row d-lg-none">
                                        <div class="col-md-12">
                                            <div class="d-flex mb-2">
                                                <div class="icon h-10 me-3 d-flex mt-1">
                                                    <i class="ni ni-single-copy-04 text-gradient text-primary"></i>
                                                </div>
                                                <div class="w-100 d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <h6 class="dropdown-header text-dark font-weight-bolder d-flex justify-content-center align-items-center p-0">Page Sections</h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Page Headers</a>
                                            <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Features</a>
                                            <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Pricing</a>
                                            <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">FAQ</a>
                                            <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Blog Posts</a>
                                            <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Testimonials</a>
                                            <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Teams</a>
                                            <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Stats</a>
                                            <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Call to Actions</a>
                                            <a class="dropdown-item ps-3 border-radius-md mb-1" href="javascript:;">Applications</a>
                                        </div>
                                    </div>
                                </ul>
                            </li>

                            <li class="nav-item dropdown dropdown-hover mx-2">
                                <a class="nav-link ps-2 d-flex cursor-pointer align-items-center" id="dropdownMenuDocs" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="material-symbols-rounded opacity-6 me-2 text-md">article</i>
                                    Docs
                                    <img src="./assets/img/down-arrow-dark.svg" alt="down-arrow" class="arrow ms-auto">
                                </a>
                                <ul class="dropdown-menu dropdown-menu-animation dropdown-lg mt-0 mt-lg-3 p-3 border-radius-lg" aria-labelledby="dropdownMenuDocs">
                                    <div class="d-none d-lg-block">
                                        <li class="nav-item">
                                            <a class="dropdown-item py-2 ps-3 border-radius-md" href="javascript:;">
                                                <div class="d-flex">
                                                    <div class="icon h-10 me-3 d-flex mt-1">
                                                        <svg class="text-secondary" width="16px" height="16px" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                                                            <title>spaceship</title>
                                                            <!-- SVG Paths -->
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h6 class="dropdown-header text-dark font-weight-bolder d-flex justify-content-center align-items-center p-0">Getting Started</h6>
                                                        <span class="text-sm">All about overview, quick start, license and contents</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <!-- More List Items -->
                                    </div>
                                </ul>
                            </li>
                            <li class="nav-item ms-lg-auto my-auto ms-3 ms-lg-0">
                            <div class="dropdown">
                                <a class="btn btn-sm btn-primary mb-0 me-1 mt-2 mt-md-0" id="dropdownMenuPages" data-bs-toggle="dropdown" aria-expanded="false">Account</a>
                                <div class="dropdown-menu dropdown-menu-animation ms-n3 dropdown-md p-3 border-radius-lg mt-0 mt-lg-3" aria-labelledby="dropdownMenuPages">
                                    <div class="d-none d-lg-block">
                                        <a href="signin.php" class="dropdown-item border-radius-md">Sign In</a>
                                        <a href="signup.php" class="dropdown-item border-radius-md">Sign Up</a>
                                        <a href="reset_password.php" class="dropdown-item border-radius-md">Reset Password</a>
                                        <a href="more.php" class="dropdown-item border-radius-md">More</a>
                                    </div>
                                    <div class="d-lg-none">
                                        <a href="signin.php" class="dropdown-item border-radius-md">Sign In</a>
                                        <a href="signup.php" class="dropdown-item border-radius-md">Sign Up</a>
                                        <a href="reset_password.php" class="dropdown-item border-radius-md">Reset Password</a>
                                        <a href="more.php" class="dropdown-item border-radius-md">More</a>
                                    </div>
                                </div>
                            </div>
                          </li>
                          
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </div>
  </div>
  <!------------------- HEADER ----------------------->
  <header>
    <div class="page-header min-vh-100">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 my-auto">
                    <h1 class="mb-4">Space</h1>
                    <p class="lead">
                        The time is now for it to be okay to be great. For being a bright color. For standing out.
                    </p>
                    <div class="buttons">
                        <button type="button" class="btn bg-gradient-warning mt-4">Discover</button>
                        <button type="button" class="btn text-warning shadow-none mt-4">Read more</button>
                    </div>
                </div>
                <div class="col-lg-8 ps-5 pe-0">
                    <div class="row mt-3">
                        <div class="col-lg-3 col-6">
                            <img class="w-100 border-radius-lg shadow mt-0 mt-lg-7" src="https://images-wixmp-ed30a86b8c4ca887773594c2.wixmp.com/f/b7489cf6-f701-4e8e-a6e7-08c8924ef45b/dhlnlxi-3134ba59-2a36-4ca6-885d-71d88d3688d7.png/v1/fill/w_894,h_894/genshin_meme__loading_paimon_by_beat_lynx_dhlnlxi-pre.png?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ1cm46YXBwOjdlMGQxODg5ODIyNjQzNzNhNWYwZDQxNWVhMGQyNmUwIiwiaXNzIjoidXJuOmFwcDo3ZTBkMTg4OTgyMjY0MzczYTVmMGQ0MTVlYTBkMjZlMCIsIm9iaiI6W1t7ImhlaWdodCI6Ijw9MTAwMCIsInBhdGgiOiJcL2ZcL2I3NDg5Y2Y2LWY3MDEtNGU4ZS1hNmU3LTA4Yzg5MjRlZjQ1YlwvZGhsbmx4aS0zMTM0YmE1OS0yYTM2LTRjYTYtODg1ZC03MWQ4OGQzNjg4ZDcucG5nIiwid2lkdGgiOiI8PTEwMDAifV1dLCJhdWQiOlsidXJuOnNlcnZpY2U6aW1hZ2Uub3BlcmF0aW9ucyJdfQ.IOswoNl5bfaytmD4yTuZKA1hhX6f_8ENVWmbV5K0KCY" alt="flower-1" loading="lazy">
                        </div>
                        <div class="col-lg-3 col-6">
                            <img class="w-100 border-radius-lg shadow" src="https://images-wixmp-ed30a86b8c4ca887773594c2.wixmp.com/f/b7489cf6-f701-4e8e-a6e7-08c8924ef45b/dhlnlxi-3134ba59-2a36-4ca6-885d-71d88d3688d7.png/v1/fill/w_894,h_894/genshin_meme__loading_paimon_by_beat_lynx_dhlnlxi-pre.png?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ1cm46YXBwOjdlMGQxODg5ODIyNjQzNzNhNWYwZDQxNWVhMGQyNmUwIiwiaXNzIjoidXJuOmFwcDo3ZTBkMTg4OTgyMjY0MzczYTVmMGQ0MTVlYTBkMjZlMCIsIm9iaiI6W1t7ImhlaWdodCI6Ijw9MTAwMCIsInBhdGgiOiJcL2ZcL2I3NDg5Y2Y2LWY3MDEtNGU4ZS1hNmU3LTA4Yzg5MjRlZjQ1YlwvZGhsbmx4aS0zMTM0YmE1OS0yYTM2LTRjYTYtODg1ZC03MWQ4OGQzNjg4ZDcucG5nIiwid2lkdGgiOiI8PTEwMDAifV1dLCJhdWQiOlsidXJuOnNlcnZpY2U6aW1hZ2Uub3BlcmF0aW9ucyJdfQ.IOswoNl5bfaytmD4yTuZKA1hhX6f_8ENVWmbV5K0KCY" alt="flower-2" loading="lazy">
                            <img class="w-100 border-radius-lg shadow mt-4" src="https://images-wixmp-ed30a86b8c4ca887773594c2.wixmp.com/f/b7489cf6-f701-4e8e-a6e7-08c8924ef45b/dhlnlxi-3134ba59-2a36-4ca6-885d-71d88d3688d7.png/v1/fill/w_894,h_894/genshin_meme__loading_paimon_by_beat_lynx_dhlnlxi-pre.png?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ1cm46YXBwOjdlMGQxODg5ODIyNjQzNzNhNWYwZDQxNWVhMGQyNmUwIiwiaXNzIjoidXJuOmFwcDo3ZTBkMTg4OTgyMjY0MzczYTVmMGQ0MTVlYTBkMjZlMCIsIm9iaiI6W1t7ImhlaWdodCI6Ijw9MTAwMCIsInBhdGgiOiJcL2ZcL2I3NDg5Y2Y2LWY3MDEtNGU4ZS1hNmU3LTA4Yzg5MjRlZjQ1YlwvZGhsbmx4aS0zMTM0YmE1OS0yYTM2LTRjYTYtODg1ZC03MWQ4OGQzNjg4ZDcucG5nIiwid2lkdGgiOiI8PTEwMDAifV1dLCJhdWQiOlsidXJuOnNlcnZpY2U6aW1hZ2Uub3BlcmF0aW9ucyJdfQ.IOswoNl5bfaytmD4yTuZKA1hhX6f_8ENVWmbV5K0KCY" alt="flower-3" loading="lazy">
                        </div>
                        <div class="col-lg-3 col-6 mb-3">
                            <img class="w-100 border-radius-lg shadow mt-0 mt-lg-5" src="https://images-wixmp-ed30a86b8c4ca887773594c2.wixmp.com/f/b7489cf6-f701-4e8e-a6e7-08c8924ef45b/dhlnlxi-3134ba59-2a36-4ca6-885d-71d88d3688d7.png/v1/fill/w_894,h_894/genshin_meme__loading_paimon_by_beat_lynx_dhlnlxi-pre.png?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ1cm46YXBwOjdlMGQxODg5ODIyNjQzNzNhNWYwZDQxNWVhMGQyNmUwIiwiaXNzIjoidXJuOmFwcDo3ZTBkMTg4OTgyMjY0MzczYTVmMGQ0MTVlYTBkMjZlMCIsIm9iaiI6W1t7ImhlaWdodCI6Ijw9MTAwMCIsInBhdGgiOiJcL2ZcL2I3NDg5Y2Y2LWY3MDEtNGU4ZS1hNmU3LTA4Yzg5MjRlZjQ1YlwvZGhsbmx4aS0zMTM0YmE1OS0yYTM2LTRjYTYtODg1ZC03MWQ4OGQzNjg4ZDcucG5nIiwid2lkdGgiOiI8PTEwMDAifV1dLCJhdWQiOlsidXJuOnNlcnZpY2U6aW1hZ2Uub3BlcmF0aW9ucyJdfQ.IOswoNl5bfaytmD4yTuZKA1hhX6f_8ENVWmbV5K0KCY" alt="flower-4" loading="lazy">
                            <img class="w-100 border-radius-lg shadow mt-4" src="https://images-wixmp-ed30a86b8c4ca887773594c2.wixmp.com/f/b7489cf6-f701-4e8e-a6e7-08c8924ef45b/dhlnlxi-3134ba59-2a36-4ca6-885d-71d88d3688d7.png/v1/fill/w_894,h_894/genshin_meme__loading_paimon_by_beat_lynx_dhlnlxi-pre.png?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ1cm46YXBwOjdlMGQxODg5ODIyNjQzNzNhNWYwZDQxNWVhMGQyNmUwIiwiaXNzIjoidXJuOmFwcDo3ZTBkMTg4OTgyMjY0MzczYTVmMGQ0MTVlYTBkMjZlMCIsIm9iaiI6W1t7ImhlaWdodCI6Ijw9MTAwMCIsInBhdGgiOiJcL2ZcL2I3NDg5Y2Y2LWY3MDEtNGU4ZS1hNmU3LTA4Yzg5MjRlZjQ1YlwvZGhsbmx4aS0zMTM0YmE1OS0yYTM2LTRjYTYtODg1ZC03MWQ4OGQzNjg4ZDcucG5nIiwid2lkdGgiOiI8PTEwMDAifV1dLCJhdWQiOlsidXJuOnNlcnZpY2U6aW1hZ2Uub3BlcmF0aW9ucyJdfQ.IOswoNl5bfaytmD4yTuZKA1hhX6f_8ENVWmbV5K0KCY" alt="flower-5" loading="lazy">
                        </div>
                        <div class="col-lg-3 col-6">
                            <img class="w-100 border-radius-lg shadow mt-3" src="https://images-wixmp-ed30a86b8c4ca887773594c2.wixmp.com/f/b7489cf6-f701-4e8e-a6e7-08c8924ef45b/dhlnlxi-3134ba59-2a36-4ca6-885d-71d88d3688d7.png/v1/fill/w_894,h_894/genshin_meme__loading_paimon_by_beat_lynx_dhlnlxi-pre.png?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ1cm46YXBwOjdlMGQxODg5ODIyNjQzNzNhNWYwZDQxNWVhMGQyNmUwIiwiaXNzIjoidXJuOmFwcDo3ZTBkMTg4OTgyMjY0MzczYTVmMGQ0MTVlYTBkMjZlMCIsIm9iaiI6W1t7ImhlaWdodCI6Ijw9MTAwMCIsInBhdGgiOiJcL2ZcL2I3NDg5Y2Y2LWY3MDEtNGU4ZS1hNmU3LTA4Yzg5MjRlZjQ1YlwvZGhsbmx4aS0zMTM0YmE1OS0yYTM2LTRjYTYtODg1ZC03MWQ4OGQzNjg4ZDcucG5nIiwid2lkdGgiOiI8PTEwMDAifV1dLCJhdWQiOlsidXJuOnNlcnZpY2U6aW1hZ2Uub3BlcmF0aW9ucyJdfQ.IOswoNl5bfaytmD4yTuZKA1hhX6f_8ENVWmbV5K0KCY" alt="flower-6" loading="lazy">
                            <img class="w-100 border-radius-lg shadow mt-4" src="https://images-wixmp-ed30a86b8c4ca887773594c2.wixmp.com/f/b7489cf6-f701-4e8e-a6e7-08c8924ef45b/dhlnlxi-3134ba59-2a36-4ca6-885d-71d88d3688d7.png/v1/fill/w_894,h_894/genshin_meme__loading_paimon_by_beat_lynx_dhlnlxi-pre.png?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJ1cm46YXBwOjdlMGQxODg5ODIyNjQzNzNhNWYwZDQxNWVhMGQyNmUwIiwiaXNzIjoidXJuOmFwcDo3ZTBkMTg4OTgyMjY0MzczYTVmMGQ0MTVlYTBkMjZlMCIsIm9iaiI6W1t7ImhlaWdodCI6Ijw9MTAwMCIsInBhdGgiOiJcL2ZcL2I3NDg5Y2Y2LWY3MDEtNGU4ZS1hNmU3LTA4Yzg5MjRlZjQ1YlwvZGhsbmx4aS0zMTM0YmE1OS0yYTM2LTRjYTYtODg1ZC03MWQ4OGQzNjg4ZDcucG5nIiwid2lkdGgiOiI8PTEwMDAifV1dLCJhdWQiOlsidXJuOnNlcnZpY2U6aW1hZ2Uub3BlcmF0aW9ucyJdfQ.IOswoNl5bfaytmD4yTuZKA1hhX6f_8ENVWmbV5K0KCY" alt="flower-7" loading="lazy">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </header>
  <main>
    <!------------------- ABOUT  ----------------------->
    <section class="py-5">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-5 col-10 d-flex justify-content-center flex-column mx-auto text-lg-start text-center">
            <h2 class="mb-4">Find more great partners</h2>
            <p class="mb-2">It really matters and then like it really doesn&#39;t matter. What matters is the people who are sparked by it. And the people who are like offended by it, it doesn&#39;t matter. </p>
            <ul class="m-lg-2 m-auto">
              <li class="mb-2">People are so scared to lose their hope</li>
              <li class="mb-2">That&#39;s the main thing people </li>
              <li class="mb-2">Thoughts- their perception of themselves!</li>
            </ul>
            <h3 class="mt-4">We will be with you forever</h3>
            <p>It really matters and then like it really doesn&#39;t matter. What matters is the people who are sparked by it. And the people who are like offended by it, it doesn&#39;t matter.</p>
            <p class="blockquote my-3 ps-2">
              <span class="text-bold">“And thank you for turning my personal jean jacket into a couture piece.”</span>
              <br>
              <small class="blockquote-footer">
                Kanye West, Producer.
              </small>
            </p>
          </div>
          <div class="col-md-5 col-6 mx-lg-0 mx-auto px-lg-0 px-md-0 my-auto">
            <img class="max-width-400 border-radius-lg shadow-lg" src="https://cdn.donmai.us/original/18/c1/__paimon_genshin_impact_drawn_by_waxgroud__18c1ae5bd2a75471a3552686cf731a0c.png">
          </div>
        </div>
      </div>
    </section>
    <!------------------- DEVELOPERS  ----------------------->
    <section class="py-5">
        <div class="container">
          <div class="row">
            <div class="col-md-7 mb-5">
              <div class="icon icon-shape icon-md bg-gradient-dark shadow text-center mb-3">
                <i class="material-symbols-rounded opacity-10">supervisor_account</i>
              </div>
              <h3>Our Awesome Team</h3>
              <p>awesome</p>
            </div>
          </div>
          <div class="row mt-5">
            <div class="col-lg-4 col-md-6 mt-md-0 mt-5">
              <div class="card card-profile card-plain">
                <div class="text-start mt-n5 z-index-1">
                  <div class="position-relative w-25">
                    <div class="blur-shadow-avatar">
                      <img class="avatar avatar-xxl border-radius-xl" src="https://media.tenor.com/SprkCVhzAK8AAAAe/paimon-genshin-impact.png">
                    </div>
                  </div>
                </div>
                <div class="card-body ps-0">
                  <h5 class="mb-0">Faith Anne Banares</h5>
                  <p class="text-muted">CreoTech</p>
                  <p>
                    haha
                  </p>
                  <button type="button" class="btn-icon-only btn-simple btn btn-lg btn-twitter" data-toggle="tooltip" data-placement="bottom" title="Follow me!">
                    <i class="fab fa-twitter"></i>
                  </button>
                  <button type="button" class="btn-icon-only btn-simple btn btn-lg btn-dribbble" data-toggle="tooltip" data-placement="bottom" title="Follow me!">
                    <i class="fab fa-dribbble"></i>
                  </button>
                  <button type="button" class="btn-icon-only btn-simple btn btn-lg btn-linkedin" data-toggle="tooltip" data-placement="bottom" title="Follow me!">
                    <i class="fab fa-linkedin"></i>
                  </button>
                </div>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 mt-md-0 mt-5">
              <div class="card card-profile card-plain">
                <div class="mt-n5 z-index-1">
                  <div class="position-relative w-25">
                    <div class="blur-shadow-avatar">
                      <img class="avatar avatar-xxl border-radius-xl" src="https://media.tenor.com/SprkCVhzAK8AAAAe/paimon-genshin-impact.png">
                    </div>
                  </div>
                </div>
                <div class="card-body ps-0">
                  <h5 class="mb-0">Alethea Malata</h5>
                  <p class="text-muted">CreoTech</p>
                  <p>
                    hehe
                  </p>
                  <button type="button" class="btn-icon-only btn-simple btn btn-lg btn-twitter" data-toggle="tooltip" data-placement="bottom" title="Follow me!">
                    <i class="fab fa-twitter"></i>
                  </button>
                  <button type="button" class="btn-icon-only btn-simple btn btn-lg btn-dribbble" data-toggle="tooltip" data-placement="bottom" title="Follow me!">
                    <i class="fab fa-dribbble"></i>
                  </button>
                  <button type="button" class="btn-icon-only btn-simple btn btn-lg btn-linkedin" data-toggle="tooltip" data-placement="bottom" title="Follow me!">
                    <i class="fab fa-linkedin"></i>
                  </button>
                </div>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 mx-md-auto mt-md-0 mt-5">
              <div class="card card-profile card-plain">
                <div class="mt-n5 z-index-1">
                  <div class="position-relative w-25">
                    <div class="blur-shadow-avatar">
                      <img class="avatar avatar-xxl border-radius-xl" src="https://media.tenor.com/SprkCVhzAK8AAAAe/paimon-genshin-impact.png">
                    </div>
                  </div>
                </div>
                <div class="card-body ps-0">
                  <h5 class="mb-0">Gertrude Gwyn Maralit</h5>
                  <p class="text-muted">CreoTech</p>
                  <p>
                    huhu
                  </p>
                  <button type="button" class="btn-icon-only btn-simple btn btn-lg btn-twitter" data-toggle="tooltip" data-placement="bottom" title="Follow me!">
                    <i class="fab fa-twitter"></i>
                  </button>
                  <button type="button" class="btn-icon-only btn-simple btn btn-lg btn-dribbble" data-toggle="tooltip" data-placement="bottom" title="Follow me!">
                    <i class="fab fa-dribbble"></i>
                  </button>
                  <button type="button" class="btn-icon-only btn-simple btn btn-lg btn-linkedin" data-toggle="tooltip" data-placement="bottom" title="Follow me!">
                    <i class="fab fa-linkedin"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
    </section>
    <!------------------- APPLICATION  ----------------------->
    <section class="py-6">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-lg-6">
              <div class="row justify-content-start">
                <div class="col-md-6">
                  <div class="info">
                    <i class="material-symbols-rounded text-3xl text-gradient text-info mb-3">public</i>
                    <h5>Fully integrated</h5>
                    <p>We get insulted by others, lose trust for those We get back freezes</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info">
                    <i class="material-symbols-rounded text-3xl text-gradient text-info mb-3">payments</i>
                    <h5>Payments functionality</h5>
                    <p>We get insulted by others, lose trust for those We get back freezes</p>
                  </div>
                </div>
              </div>
              <div class="row justify-content-start mt-4">
                <div class="col-md-6">
                  <div class="info">
                    <i class="material-symbols-rounded text-3xl text-gradient text-info mb-3">apps</i>
                    <h5>Prebuilt components</h5>
                    <p>We get insulted by others, lose trust for those We get back freezes</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info">
                    <i class="material-symbols-rounded text-3xl text-gradient text-info mb-3">3p</i>
                    <h5>Improved platform</h5>
                    <p>We get insulted by others, lose trust for those We get back freezes</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-4 ms-auto mt-lg-0 mt-4">
              <div class="card">
                <div class="card-header p-0 position-relative mt-2 mx-2 z-index-2">
                  <a class="d-block blur-shadow-image">
                    <img src="https://w.wallhaven.cc/full/dp/wallhaven-dpyvv3.jpg" alt="img-colored-shadow" class="img-fluid border-radius-lg">
                  </a>
                </div>
                <div class="card-body text-center">
                  <h5 class="font-weight-normal">
                    <a href="javascript:;">Get insights on Search</a>
                  </h5>
                  <p class="mb-0">
                    Website visitors today demand a frictionless user expericence — especially when using search. Because of the hight standards.
                  </p>
                  <button type="button" class="btn bg-gradient-info btn-sm mb-0 mt-3">Find out more</button>
                </div>
              </div>
            </div>
          </div>
        </div>
    </section>
    <!------------------- CONTACT  ----------------------->
    <section>
      <div class="page-header min-vh-100">
          <div class="container">
              <div class="row">
                  <!-- Left Side: Image -->
                  <div class="col-lg-6 col-md-8 d-none d-md-flex justify-content-center align-items-center">
                    <img src="https://preview.redd.it/happy-birthday-paimon-v0-x5arhzw0yb3b1.jpg?width=640&crop=smart&auto=webp&s=bac82f414b88ca68e5d9852632011ede65a98146" alt="Contact Us" class="img-fluid" style="border-radius: 10px;">
                  </div>                
                  <!-- Right Side: Contact Form -->
                  <div class="col-lg-6 col-md-8 ms-auto me-auto">
                    <div class="card d-flex blur justify-content-center my-sm-0 my-sm-6 mt-8 mb-5 border-0"> <!-- Removed shadow-lg and added border-0 -->
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
                                                <input class="form-control" aria-label="First Name..." type="text">
                                            </div>
                                        </div>
                                        <div class="col-md-6 ps-2">
                                            <div class="input-group input-group-dynamic mb-4">
                                                <label class="form-label">Last Name</label>
                                                <input type="text" class="form-control" aria-label="Last Name...">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="input-group input-group-dynamic">
                                            <label class="form-label">Email Address</label>
                                            <input type="email" class="form-control">
                                        </div>
                                    </div>
                                    <div class="input-group mb-4 input-group-static">
                                        <label>Your message</label>
                                        <textarea name="message" class="form-control" id="message" rows="4"></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-check form-switch mb-4 d-flex align-items-center">
                                                <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault" checked="">
                                                <label class="form-check-label ms-3 mb-0" for="flexSwitchCheckDefault">I agree to the <a href="javascript:;" class="text-dark"><u>Terms and Conditions</u></a>.</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <button type="submit" class="btn bg-gradient-dark w-100">Send Message</button>
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
                    Yes, Space sends automated SMS or email reminders for upcoming sessions to ensure students don’t miss their appointments.
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
  <!------------------- FOOTER  ----------------------->
  <footer class="footer pt-5 mt-5">
          <div class="container">
            <div class=" row">
              <div class="col-md-3 mb-4 ms-auto">
                <div>
                  <<a href="#">
                    <img src="./assets/img/logo-ct-dark.png" class="mb-3 footer-logo" alt="main_logo">
                  </a>
                  <h6 class="font-weight-bolder mb-4">CreoTech</h6>
                </div>
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
                      <a class="nav-link pe-1" href="https://dribbble.com/" target="_blank">
                        <i class="fab fa-dribbble text-lg opacity-8"></i>
                      </a>
                    </li>
        
        
                    <li class="nav-item">
                      <a class="nav-link pe-1" href="https://github.com/" target="_blank">
                        <i class="fab fa-github text-lg opacity-8"></i>
                      </a>
                    </li>
        
                    <li class="nav-item">
                      <a class="nav-link pe-1" href="https://www.youtube.com/channel/UCVyTG4sCw-rOvB9oHkzZD1w" target="_blank">
                        <i class="fab fa-youtube text-lg opacity-8"></i>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
        
        
        
              <div class="col-md-2 col-sm-6 col-6 mb-4">
                <div>
                  <h6 class="text-sm">Company</h6>
                  <ul class="flex-column ms-n3 nav">
                    <li class="nav-item">
                      <a class="nav-link" href="https://www.creative-tim.com/presentation" target="_blank">
                        About Us
                      </a>
                    </li>
        
                    <li class="nav-item">
                      <a class="nav-link" href="https://www.creative-tim.com/templates/free" target="_blank">
                        Freebies
                      </a>
                    </li>
        
                    <li class="nav-item">
                      <a class="nav-link" href="https://www.creative-tim.com/templates/premium" target="_blank">
                        Premium Tools
                      </a>
                    </li>
        
                    <li class="nav-item">
                      <a class="nav-link" href="https://www.creative-tim.com/blog" target="_blank">
                        Blog
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
        
              <div class="col-md-2 col-sm-6 col-6 mb-4">
                <div>
                  <h6 class="text-sm">Resources</h6>
                  <ul class="flex-column ms-n3 nav">
                    <li class="nav-item">
                      <a class="nav-link" href="https://iradesign.io/" target="_blank">
                        Illustrations
                      </a>
                    </li>
        
                    <li class="nav-item">
                      <a class="nav-link" href="https://www.creative-tim.com/bits" target="_blank">
                        Bits & Snippets
                      </a>
                    </li>
        
                    <li class="nav-item">
                      <a class="nav-link" href="https://www.creative-tim.com/affiliates/new" target="_blank">
                        Affiliate Program
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
        
              <div class="col-md-2 col-sm-6 col-6 mb-4">
                <div>
                  <h6 class="text-sm">Help & Support</h6>
                  <ul class="flex-column ms-n3 nav">
                    <li class="nav-item">
                      <a class="nav-link" href="https://www.creative-tim.com/contact-us" target="_blank">
                        Contact Us
                      </a>
                    </li>
        
                    <li class="nav-item">
                      <a class="nav-link" href="https://www.creative-tim.com/knowledge-center" target="_blank">
                        Knowledge Center
                      </a>
                    </li>
        
                    <li class="nav-item">
                      <a class="nav-link" href="https://services.creative-tim.com/?ref=ct-mk2-footer" target="_blank">
                        Custom Development
                      </a>
                    </li>
        
                    <li class="nav-item">
                      <a class="nav-link" href="https://www.creative-tim.com/sponsorships" target="_blank">
                        Sponsorships
                      </a>
                    </li>
        
                  </ul>
                </div>
              </div>
        
              <div class="col-md-2 col-sm-6 col-6 mb-4 me-auto">
                <div>
                  <h6 class="text-sm">Legal</h6>
                  <ul class="flex-column ms-n3 nav">
                    <li class="nav-item">
                      <a class="nav-link" href="https://www.creative-tim.com/knowledge-center/terms-of-service" target="_blank">
                        Terms & Conditions
                      </a>
                    </li>
        
                    <li class="nav-item">
                      <a class="nav-link" href="https://www.creative-tim.com/knowledge-center/privacy-policy" target="_blank">
                        Privacy Policy
                      </a>
                    </li>
        
                    <li class="nav-item">
                      <a class="nav-link" href="https://www.creative-tim.com/license" target="_blank">
                        Licenses (EULA)
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
        
              <div class="col-12">
                <div class="text-center">
                  <p class="text-dark my-4 text-sm font-weight-normal">
                    All rights reserved. Copyright © 
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
  <script>


    var ctx = document.getElementById("chart-bars").getContext("2d");

    new Chart(ctx, {
      type: "bar",
      data: {
        labels: ["M", "T", "W", "T", "F", "S", "S"],
        datasets: [{
          label: "Views",
          tension: 0.4,
          borderWidth: 0,
          borderRadius: 4,
          borderSkipped: false,
          backgroundColor: "#43A047",
          data: [50, 45, 22, 28, 50, 60, 76],
          barThickness: 'flex'
        }, ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: '#e5e5e5'
            },
            ticks: {
              suggestedMin: 0,
              suggestedMax: 500,
              beginAtZero: true,
              padding: 10,
              font: {
                size: 14,
                lineHeight: 2
              },
              color: "#737373"
            },
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#737373',
              padding: 10,
              font: {
                size: 14,
                lineHeight: 2
              },
            }
          },
        },
      },
    });


    var ctx2 = document.getElementById("chart-line").getContext("2d");

    new Chart(ctx2, {
      type: "line",
      data: {
        labels: ["J", "F", "M", "A", "M", "J", "J", "A", "S", "O", "N", "D"],
        datasets: [{
          label: "Sales",
          tension: 0,
          borderWidth: 2,
          pointRadius: 3,
          pointBackgroundColor: "#43A047",
          pointBorderColor: "transparent",
          borderColor: "#43A047",
          backgroundColor: "transparent",
          fill: true,
          data: [120, 230, 130, 440, 250, 360, 270, 180, 90, 300, 310, 220],
          maxBarThickness: 6

        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            callbacks: {
              title: function(context) {
                const fullMonths = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                return fullMonths[context[0].dataIndex];
              }
            }
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [4, 4],
              color: '#e5e5e5'
            },
            ticks: {
              display: true,
              color: '#737373',
              padding: 10,
              font: {
                size: 12,
                lineHeight: 2
              },
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#737373',
              padding: 10,
              font: {
                size: 12,
                lineHeight: 2
              },
            }
          },
        },
      },
    });

    var ctx3 = document.getElementById("chart-line-tasks").getContext("2d");

    new Chart(ctx3, {
      type: "line",
      data: {
        labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
          label: "Tasks",
          tension: 0,
          borderWidth: 2,
          pointRadius: 3,
          pointBackgroundColor: "#43A047",
          pointBorderColor: "transparent",
          borderColor: "#43A047",
          backgroundColor: "transparent",
          fill: true,
          data: [50, 40, 300, 220, 500, 250, 400, 230, 500],
          maxBarThickness: 6

        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [4, 4],
              color: '#e5e5e5'
            },
            ticks: {
              display: true,
              padding: 10,
              color: '#737373',
              font: {
                size: 14,
                lineHeight: 2
              },
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [4, 4]
            },
            ticks: {
              display: true,
              color: '#737373',
              padding: 10,
              font: {
                size: 14,
                lineHeight: 2
              },
            }
          },
        },
      },
    });
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
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>