<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add logging
error_log("Session data: " . print_r($_SESSION, true));

// Required files
require_once '../../configs/config.php';
require_once '../../admin_operations/dashboard_analytics.php';

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    error_log("Auth failed - User ID: " . ($_SESSION['user_id'] ?? 'not set') . ", Role: " . ($_SESSION['role'] ?? 'not set'));
    header('Location: ../../signin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/logo-space.png">
  <link rel="icon" type="image/png" href="../../assets/img/logo-space.png">
  <title>
    Space - Admin
  </title>
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="../../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../../assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/ddc03e77c7.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link id="pagestyle" href="../../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../../assets/js/plugins/chart.min.js"></script>
  <script src="../../assets/js/plugins/quotes.js"></script>
  <script src="../../assets/js/activity-tracker.js"></script>
</head>

<body class="g-sidenav-show  bg-gray-100">
  <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2  bg-white my-2" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand px-4 py-3 m-0" href="index.html" target="_blank">
        <img src="Space/assets/img/logo-ct-dark.png" class="navbar-brand-img" width="26" height="26" alt="main_logo">
        <span class="ms-1 text-sm text-dark">Creative Tim</span>
      </a>
    </div>
    <hr class="horizontal dark mt-0 mb-2">
    <div class="collapse navbar-collapse  w-auto h-auto" id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item mb-2 mt-0">
          <a data-bs-toggle="collapse" href="#ProfileNav" class="nav-link text-dark" aria-controls="ProfileNav" role="button" aria-expanded="false">
            <img src="Space/assets/img/team-3.jpg" class="avatar">
            <span class="nav-link-text ms-2 ps-1"><?php echo $_SESSION['firstname'] . ' ' . $_SESSION['lastname']; ?></span>
          </a>
          <div class="collapse" id="ProfileNav" style="">
            <ul class="nav ">
              <li class="nav-item">
                <a class="nav-link text-dark" href="Space/pages/pages/profile/overview.html">
                  <span class="sidenav-mini-icon"> MP </span>
                  <span class="sidenav-normal  ms-3  ps-1"> My Profile </span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-dark " href="Space/pages/pages/account/settings.html">
                  <span class="sidenav-mini-icon"> S </span>
                  <span class="sidenav-normal  ms-3  ps-1"> Settings </span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-dark " href="Space/pages/authentication/signin/basic.html">
                  <span class="sidenav-mini-icon"> L </span>
                  <span class="sidenav-normal  ms-3  ps-1"> Logout </span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        <hr class="horizontal dark mt-0">
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#dashboardsExamples" class="nav-link text-dark active" aria-controls="dashboardsExamples" role="button" aria-expanded="false">
            <i class="material-symbols-rounded opacity-5">space_dashboard</i>
            <span class="nav-link-text ms-1 ps-1">Dashboards</span>
          </a>
          <div class="collapse  show " id="dashboardsExamples">
            <ul class="nav ">
              <li class="nav-item active">
                <a class="nav-link text-dark active" href="index.html">
                  <span class="sidenav-mini-icon"> A </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Analytics </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark" href="pages/dashboards/discover.html">
                  <span class="sidenav-mini-icon"> D </span>
                  <span class="sidenav-normal ms-1 ps-1"> Discover </span>
              </a>              
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/dashboards/sales.html">
                  <span class="sidenav-mini-icon"> S </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Sales </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/dashboards/automotive.html">
                  <span class="sidenav-mini-icon"> A </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Automotive </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/dashboards/smart-home.html">
                  <span class="sidenav-mini-icon"> S </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Smart Home </span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        <li class="nav-item mt-3">
          <h6 class="ps-3  ms-2 text-uppercase text-xs font-weight-bolder text-dark">PAGES</h6>
        </li>
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#pagesExamples" class="nav-link text-dark " aria-controls="pagesExamples" role="button" aria-expanded="false">
            <i class="material-symbols-rounded opacity-5 {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">contract</i>
            <span class="nav-link-text ms-1 ps-1">Pages</span>
          </a>
          <div class="collapse " id="pagesExamples">
            <ul class="nav ">
              <li class="nav-item ">
                <a class="nav-link text-dark " data-bs-toggle="collapse" aria-expanded="false" href="#vrExamples">
                  <span class="sidenav-mini-icon"> V </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Virtual Reality <b class="caret"></b></span>
                </a>
                <div class="collapse " id="vrExamples">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/pages/vr/vr-default.html">
                        <span class="sidenav-mini-icon"> V </span>
                        <span class="sidenav-normal  ms-1  ps-1"> VR Default </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/pages/vr/vr-info.html">
                        <span class="sidenav-mini-icon"> V </span>
                        <span class="sidenav-normal  ms-1  ps-1"> VR Info </span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/pages/pricing-page.html">
                  <span class="sidenav-mini-icon"> P </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Pricing Page </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/pages/rtl-page.html">
                  <span class="sidenav-mini-icon"> R </span>
                  <span class="sidenav-normal  ms-1  ps-1"> RTL </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/pages/widgets.html">
                  <span class="sidenav-mini-icon"> W </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Widgets </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/pages/charts.html">
                  <span class="sidenav-mini-icon"> C </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Charts </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/pages/sweet-alerts.html">
                  <span class="sidenav-mini-icon"> S </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Sweet Alerts </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/pages/notifications.html">
                  <span class="sidenav-mini-icon"> N </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Notifications </span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#accountExamples" class="nav-link text-dark " aria-controls="accountExamples" role="button" aria-expanded="false">
            <i class="material-symbols-rounded opacity-5 {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">account_circle</i>
            <span class="nav-link-text ms-1 ps-1">Account</span>
          </a>
          <div class="collapse " id="accountExamples">
            <ul class="nav ">
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/account/settings.html">
                  <span class="sidenav-mini-icon"> S </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Settings </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/account/billing.html">
                  <span class="sidenav-mini-icon"> B </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Billing </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/account/invoice.html">
                  <span class="sidenav-mini-icon"> I </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Invoice </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/account/security.html">
                  <span class="sidenav-mini-icon"> S </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Security </span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#applicationsExamples" class="nav-link text-dark " aria-controls="applicationsExamples" role="button" aria-expanded="false">
            <i class="material-symbols-rounded opacity-5 {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">apps</i>
            <span class="nav-link-text ms-1 ps-1">Applications</span>
          </a>
          <div class="collapse " id="applicationsExamples">
            <ul class="nav ">
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/applications/crm.html">
                  <span class="sidenav-mini-icon"> C </span>
                  <span class="sidenav-normal  ms-1  ps-1"> CRM </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/applications/kanban.html">
                  <span class="sidenav-mini-icon"> K </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Kanban </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/applications/wizard.html">
                  <span class="sidenav-mini-icon"> W </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Wizard </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/applications/datatables.html">
                  <span class="sidenav-mini-icon"> D </span>
                  <span class="sidenav-normal  ms-1  ps-1"> DataTables </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/applications/calendar.html">
                  <span class="sidenav-mini-icon"> C </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Calendar </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/applications/stats.html">
                  <span class="sidenav-mini-icon"> S </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Stats </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/applications/validation.html">
                  <span class="sidenav-mini-icon"> V </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Validation </span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#ecommerceExamples" class="nav-link text-dark " aria-controls="ecommerceExamples" role="button" aria-expanded="false">
            <i class="material-symbols-rounded opacity-5 {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">storefront</i>
            <span class="nav-link-text ms-1 ps-1">Ecommerce</span>
          </a>
          <div class="collapse " id="ecommerceExamples">
            <ul class="nav ">
              <li class="nav-item ">
                <a class="nav-link text-dark " data-bs-toggle="collapse" aria-expanded="false" href="#productsExample">
                  <span class="sidenav-mini-icon"> P </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Products <b class="caret"></b></span>
                </a>
                <div class="collapse " id="productsExample">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/ecommerce/products/new-product.html">
                        <span class="sidenav-mini-icon"> N </span>
                        <span class="sidenav-normal  ms-1  ps-1"> New Product </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/ecommerce/products/edit-product.html">
                        <span class="sidenav-mini-icon"> E </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Edit Product </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/ecommerce/products/product-page.html">
                        <span class="sidenav-mini-icon"> P </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Product Page </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/ecommerce/products/products-list.html">
                        <span class="sidenav-mini-icon"> P </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Products List </span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " data-bs-toggle="collapse" aria-expanded="false" href="#ordersExample">
                  <span class="sidenav-mini-icon"> O </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Orders <b class="caret"></b></span>
                </a>
                <div class="collapse " id="ordersExample">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/ecommerce/orders/list.html">
                        <span class="sidenav-mini-icon"> O </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Order List </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/ecommerce/orders/details.html">
                        <span class="sidenav-mini-icon"> O </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Order Details </span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/ecommerce/referral.html">
                  <span class="sidenav-mini-icon"> R </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Referral </span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#profileExamples" class="nav-link text-dark " aria-controls="profileExamples" role="button" aria-expanded="false">
            <i class="material-symbols-rounded opacity-5 {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">group</i>
            <span class="nav-link-text ms-1 ps-1">Team</span>
          </a>
          <div class="collapse " id="profileExamples">
            <ul class="nav ">
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/team/all-projects.html">
                  <span class="sidenav-mini-icon"> A </span>
                  <span class="sidenav-normal  ms-1  ps-1"> All Projects </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/team/messages.html">
                  <span class="sidenav-mini-icon"> M </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Messages </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/team/new-user.html">
                  <span class="sidenav-mini-icon"> N </span>
                  <span class="sidenav-normal  ms-1  ps-1"> New User </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/team/profile-overview.html">
                  <span class="sidenav-mini-icon"> P </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Profile Overview </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/team/reports.html">
                  <span class="sidenav-mini-icon"> R </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Reports </span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#projectsExamples" class="nav-link text-dark " aria-controls="projectsExamples" role="button" aria-expanded="false">
            <i class="material-symbols-rounded opacity-5 {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">widgets</i>
            <span class="nav-link-text ms-1 ps-1">Projects</span>
          </a>
          <div class="collapse " id="projectsExamples">
            <ul class="nav ">
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/projects/general.html">
                  <span class="sidenav-mini-icon"> G </span>
                  <span class="sidenav-normal  ms-1  ps-1"> General </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/projects/timeline.html">
                  <span class="sidenav-mini-icon"> T </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Timeline </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="Space/pages/projects/new-project.html">
                  <span class="sidenav-mini-icon"> N </span>
                  <span class="sidenav-normal  ms-1  ps-1"> New Project </span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#authExamples" class="nav-link text-dark " aria-controls="authExamples" role="button" aria-expanded="false">
            <i class="material-symbols-rounded opacity-5 {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">tv_signin</i>
            <span class="nav-link-text ms-1 ps-1">Authentication</span>
          </a>
          <div class="collapse " id="authExamples">
            <ul class="nav ">
              <li class="nav-item ">
                <a class="nav-link text-dark " data-bs-toggle="collapse" aria-expanded="false" href="#signinExample">
                  <span class="sidenav-mini-icon"> S </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Sign In <b class="caret"></b></span>
                </a>
                <div class="collapse " id="signinExample">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/authentication/signin/basic.html">
                        <span class="sidenav-mini-icon"> B </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Basic </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/authentication/signin/cover.html">
                        <span class="sidenav-mini-icon"> C </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Cover </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/authentication/signin/illustration.html">
                        <span class="sidenav-mini-icon"> I </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Illustration </span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " data-bs-toggle="collapse" aria-expanded="false" href="#signupExample">
                  <span class="sidenav-mini-icon"> S </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Sign Up <b class="caret"></b></span>
                </a>
                <div class="collapse " id="signupExample">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/authentication/signup/basic.html">
                        <span class="sidenav-mini-icon"> B </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Basic </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/authentication/signup/cover.html">
                        <span class="sidenav-mini-icon"> C </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Cover </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/authentication/signup/illustration.html">
                        <span class="sidenav-mini-icon"> I </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Illustration </span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " data-bs-toggle="collapse" aria-expanded="false" href="#resetExample">
                  <span class="sidenav-mini-icon"> R </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Reset Password <b class="caret"></b></span>
                </a>
                <div class="collapse " id="resetExample">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/authentication/reset/basic.html">
                        <span class="sidenav-mini-icon"> B </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Basic </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/authentication/reset/cover.html">
                        <span class="sidenav-mini-icon"> C </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Cover </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/authentication/reset/illustration.html">
                        <span class="sidenav-mini-icon"> I </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Illustration </span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " data-bs-toggle="collapse" aria-expanded="false" href="#lockExample">
                  <span class="sidenav-mini-icon"> L </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Lock <b class="caret"></b></span>
                </a>
                <div class="collapse " id="lockExample">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/authentication/lock/basic.html">
                        <span class="sidenav-mini-icon"> B </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Basic </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/authentication/lock/cover.html">
                        <span class="sidenav-mini-icon"> C </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Cover </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/authentication/lock/illustration.html">
                        <span class="sidenav-mini-icon"> I </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Illustration </span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " data-bs-toggle="collapse" aria-expanded="false" href="#StepExample">
                  <span class="sidenav-mini-icon"> 2 </span>
                  <span class="sidenav-normal  ms-1  ps-1"> 2-Step Verification <b class="caret"></b></span>
                </a>
                <div class="collapse " id="StepExample">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/authentication/verification/basic.html">
                        <span class="sidenav-mini-icon"> B </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Basic </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/authentication/verification/cover.html">
                        <span class="sidenav-mini-icon"> C </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Cover </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/authentication/verification/illustration.html">
                        <span class="sidenav-mini-icon"> I </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Illustration </span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " data-bs-toggle="collapse" aria-expanded="false" href="#errorExample">
                  <span class="sidenav-mini-icon"> E </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Error <b class="caret"></b></span>
                </a>
                <div class="collapse " id="errorExample">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/authentication/error/404.html">
                        <span class="sidenav-mini-icon"> E </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Error 404 </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="Space/pages/authentication/error/500.html">
                        <span class="sidenav-mini-icon"> E </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Error 500 </span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
            </ul>
          </div>
        </li>
        <li class="nav-item">
          <hr class="horizontal dark" />
          <h6 class="ps-3  ms-2 text-uppercase text-xs font-weight-bolder text-dark">DOCS</h6>
        </li>
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#basicExamples" class="nav-link text-dark " aria-controls="basicExamples" role="button" aria-expanded="false">
            <i class="material-symbols-rounded opacity-5 {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">upcoming</i>
            <span class="nav-link-text ms-1 ps-1">Basic</span>
          </a>
          <div class="collapse " id="basicExamples">
            <ul class="nav ">
              <li class="nav-item ">
                <a class="nav-link text-dark " data-bs-toggle="collapse" aria-expanded="false" href="#gettingStartedExample">
                  <span class="sidenav-mini-icon"> G </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Getting Started <b class="caret"></b></span>
                </a>
                <div class="collapse " id="gettingStartedExample">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/quick-start/material-dashboard" target="_blank">
                        <span class="sidenav-mini-icon"> Q </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Quick Start </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/license/material-dashboard" target="_blank">
                        <span class="sidenav-mini-icon"> L </span>
                        <span class="sidenav-normal  ms-1  ps-1"> License </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/overview/material-dashboard" target="_blank">
                        <span class="sidenav-mini-icon"> C </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Contents </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/build-tools/material-dashboard" target="_blank">
                        <span class="sidenav-mini-icon"> B </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Build Tools </span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " data-bs-toggle="collapse" aria-expanded="false" href="#foundationExample">
                  <span class="sidenav-mini-icon"> F </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Foundation <b class="caret"></b></span>
                </a>
                <div class="collapse " id="foundationExample">
                  <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/colors/material-dashboard" target="_blank">
                        <span class="sidenav-mini-icon"> C </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Colors </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/grid/material-dashboard" target="_blank">
                        <span class="sidenav-mini-icon"> G </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Grid </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/typography/material-dashboard" target="_blank">
                        <span class="sidenav-mini-icon"> T </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Typography </span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/icons/material-dashboard" target="_blank">
                        <span class="sidenav-mini-icon"> I </span>
                        <span class="sidenav-normal  ms-1  ps-1"> Icons </span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>
            </ul>
          </div>
        </li>
        <li class="nav-item">
          <a data-bs-toggle="collapse" href="#componentsExamples" class="nav-link text-dark " aria-controls="componentsExamples" role="button" aria-expanded="false">
            <i class="material-symbols-rounded opacity-5 {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">view_in_ar</i>
            <span class="nav-link-text ms-1 ps-1">Components</span>
          </a>
          <div class="collapse " id="componentsExamples">
            <ul class="nav ">
              <li class="nav-item ">
                <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/alerts/material-dashboard" target="_blank">
                  <span class="sidenav-mini-icon"> A </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Alerts </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/badge/material-dashboard" target="_blank">
                  <span class="sidenav-mini-icon"> B </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Badge </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/buttons/material-dashboard" target="_blank">
                  <span class="sidenav-mini-icon"> B </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Buttons </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/cards/material-dashboard" target="_blank">
                  <span class="sidenav-mini-icon"> C </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Card </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/carousel/material-dashboard" target="_blank">
                  <span class="sidenav-mini-icon"> C </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Carousel </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/collapse/material-dashboard" target="_blank">
                  <span class="sidenav-mini-icon"> C </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Collapse </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/dropdowns/material-dashboard" target="_blank">
                  <span class="sidenav-mini-icon"> D </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Dropdowns </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/forms/material-dashboard" target="_blank">
                  <span class="sidenav-mini-icon"> F </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Forms </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/modal/material-dashboard" target="_blank">
                  <span class="sidenav-mini-icon"> M </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Modal </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/navs/material-dashboard" target="_blank">
                  <span class="sidenav-mini-icon"> N </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Navs </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/navbar/material-dashboard" target="_blank">
                  <span class="sidenav-mini-icon"> N </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Navbar </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/pagination/material-dashboard" target="_blank">
                  <span class="sidenav-mini-icon"> P </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Pagination </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/popovers/material-dashboard" target="_blank">
                  <span class="sidenav-mini-icon"> P </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Popovers </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/progress/material-dashboard" target="_blank">
                  <span class="sidenav-mini-icon"> P </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Progress </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/spinners/material-dashboard" target="_blank">
                  <span class="sidenav-mini-icon"> S </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Spinners </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/tables/material-dashboard" target="_blank">
                  <span class="sidenav-mini-icon"> T </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Tables </span>
                </a>
              </li>
              <li class="nav-item ">
                <a class="nav-link text-dark " href="https://www.creative-tim.com/learning-lab/bootstrap/tooltips/material-dashboard" target="_blank">
                  <span class="sidenav-mini-icon"> T </span>
                  <span class="sidenav-normal  ms-1  ps-1"> Tooltips </span>
                </a>
              </li>
            </ul>
          </div>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark" href="https://github.com/creativetimofficial/ct-Space/blob/master/CHANGELOG.md" target="_blank">
            <i class="material-symbols-rounded opacity-5 {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">receipt_long</i>
            <span class="nav-link-text ms-1 ps-1">Changelog</span>
          </a>
        </li>
      </ul>
    </div>
    <div class="sidenav-footer position-absolute w-100 bottom-0">
        <div class="mx-3">
            <button type="button" class="btn bg-gradient-primary mt-4 w-100" onclick="handleSignOut()">
                <i class="material-symbols-rounded opacity-5 me-2">logout</i> Sign Out
            </button>
        </div>
    </div>
  </aside>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg position-sticky mt-2 top-1 px-0 py-1 mx-3 shadow-none border-radius-lg z-index-sticky" id="navbarBlur" data-scroll="true">
      <div class="container-fluid py-1 px-2">
        <div class="sidenav-toggler sidenav-toggler-inner d-xl-block d-none ">
          <a href="javascript:;" class="nav-link text-body p-0">
            <div class="sidenav-toggler-inner">
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
              <i class="sidenav-toggler-line"></i>
            </div>
          </a>
        </div>
        <nav aria-label="breadcrumb" class="ps-2">
          <ol class="breadcrumb bg-transparent mb-0 p-0">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
            <li class="breadcrumb-item text-sm text-dark active font-weight-bold" aria-current="page">Analytics</li>
          </ol>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <div class="ms-md-auto pe-md-3 d-flex align-items-center">
            <div class="input-group input-group-outline">
              <label class="form-label">Search here</label>
              <input type="text" class="form-control">
            </div>
          </div>
          <ul class="navbar-nav  justify-content-end">
            <li class="nav-item">
              <a href="Space/pages/authentication/signin/illustration.html" class="px-1 py-0 nav-link line-height-0" target="_blank">
                <i class="material-symbols-rounded">
              account_circle
            </i>
              </a>
            </li>
            <li class="nav-item">
              <a href="javascript:;" class="nav-link py-0 px-1 line-height-0">
                <i class="material-symbols-rounded fixed-plugin-button-nav">
              settings
            </i>
              </a>
            </li>
            <li class="nav-item dropdown py-0 pe-3">
              <a href="javascript:;" class="nav-link py-0 px-1 position-relative line-height-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="material-symbols-rounded">
              notifications
            </i>
                <span class="position-absolute top-5 start-100 translate-middle badge rounded-pill bg-danger border border-white small py-1 px-2">
                  <span class="small">11</span>
                  <span class="visually-hidden">unread notifications</span>
                </span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end p-2 me-sm-n4" aria-labelledby="dropdownMenuButton">
                <li class="mb-2">
                  <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex align-items-center py-1">
                      <span class="material-symbols-rounded">email</span>
                      <div class="ms-2">
                        <h6 class="text-sm font-weight-normal my-auto">
                          Check new messages
                        </h6>
                      </div>
                    </div>
                  </a>
                </li>
                <li class="mb-2">
                  <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex align-items-center py-1">
                      <span class="material-symbols-rounded">podcasts</span>
                      <div class="ms-2">
                        <h6 class="text-sm font-weight-normal my-auto">
                          Manage podcast session
                        </h6>
                      </div>
                    </div>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex align-items-center py-1">
                      <span class="material-symbols-rounded">shopping_cart</span>
                      <div class="ms-2">
                        <h6 class="text-sm font-weight-normal my-auto">
                          Payment successfully completed
                        </h6>
                      </div>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                </div>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <!-- End Navbar -->
    <div class="container-fluid py-2">
      <div class="row">
        <div class="ms-3">
          <h3 class="mb-0 h4 font-weight-bolder">Dashboard</h3>
          <p class="mb-4">
            Check the sales, value and bounce rate by country.
          </p>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="text-sm mb-0 text-capitalize">Today's Money</p>
                  <h4 class="mb-0">$53k</h4>
                </div>
                <div class="icon icon-md icon-shape bg-gradient-dark shadow-dark shadow text-center border-radius-lg">
                  <i class="material-symbols-rounded opacity-10">weekend</i>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">+55% </span>than last week</p>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="text-sm mb-0 text-capitalize">Today's Users</p>
                  <h4 class="mb-0">2300</h4>
                </div>
                <div class="icon icon-md icon-shape bg-gradient-dark shadow-dark shadow text-center border-radius-lg">
                  <i class="material-symbols-rounded opacity-10">person</i>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">+3% </span>than last month</p>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="text-sm mb-0 text-capitalize">Ads Views</p>
                  <h4 class="mb-0">3,462</h4>
                </div>
                <div class="icon icon-md icon-shape bg-gradient-dark shadow-dark shadow text-center border-radius-lg">
                  <i class="material-symbols-rounded opacity-10">leaderboard</i>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm"><span class="text-danger font-weight-bolder">-2% </span>than yesterday</p>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="text-sm mb-0 text-capitalize">Sales</p>
                  <h4 class="mb-0">$103,430</h4>
                </div>
                <div class="icon icon-md icon-shape bg-gradient-dark shadow-dark shadow text-center border-radius-lg">
                  <i class="material-symbols-rounded opacity-10">weekend</i>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">+5% </span>than yesterday</p>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-4 col-md-6 mt-4 mb-4">
          <div class="card">
            <div class="card-body">
              <h6 class="mb-0 ">Website Views</h6>
              <p class="text-sm ">Last Campaign Performance</p>
              <div class="pe-2">
                <div class="chart">
                  <canvas id="chart-bars" class="chart-canvas" height="170"></canvas>
                </div>
              </div>
              <hr class="dark horizontal">
              <div class="d-flex ">
                <i class="material-symbols-rounded text-sm my-auto me-1">schedule</i>
                <p class="mb-0 text-sm"> campaign sent 2 days ago </p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mt-4 mb-4">
          <div class="card ">
            <div class="card-body">
              <h6 class="mb-0 "> Daily Sales </h6>
              <p class="text-sm "> (<span class="font-weight-bolder">+15%</span>) increase in today sales. </p>
              <div class="pe-2">
                <div class="chart">
                  <canvas id="chart-line" class="chart-canvas" height="170"></canvas>
                </div>
              </div>
              <hr class="dark horizontal">
              <div class="d-flex ">
                <i class="material-symbols-rounded text-sm my-auto me-1">schedule</i>
                <p class="mb-0 text-sm"> updated 4 min ago </p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 mt-4 mb-3">
          <div class="card">
            <div class="card-body">
              <h6 class="mb-0 ">Completed Tasks</h6>
              <p class="text-sm ">Last Campaign Performance</p>
              <div class="pe-2">
                <div class="chart">
                  <canvas id="chart-line-tasks" class="chart-canvas" height="170"></canvas>
                </div>
              </div>
              <hr class="dark horizontal">
              <div class="d-flex ">
                <i class="material-symbols-rounded text-sm my-auto me-1">schedule</i>
                <p class="mb-0 text-sm">just updated</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row mb-4">
        <div class="col-lg-8 col-md-6 mb-md-0 mb-4">
          <div class="card">
            <div class="card-header pb-0">
              <div class="row">
                <div class="col-lg-6 col-7">
                  <h6>Projects</h6>
                  <p class="text-sm mb-0">
                    <i class="fa fa-check text-info" aria-hidden="true"></i>
                    <span class="font-weight-bold ms-1">30 done</span> this month
                  </p>
                </div>
                <div class="col-lg-6 col-5 my-auto text-end">
                  <div class="dropdown float-lg-end pe-4">
                    <a class="cursor-pointer" id="dropdownTable" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fa fa-ellipsis-v text-secondary"></i>
                    </a>
                    <ul class="dropdown-menu px-2 py-3 ms-sm-n4 ms-n5" aria-labelledby="dropdownTable">
                      <li><a class="dropdown-item border-radius-md" href="javascript:;">Action</a></li>
                      <li><a class="dropdown-item border-radius-md" href="javascript:;">Another action</a></li>
                      <li><a class="dropdown-item border-radius-md" href="javascript:;">Something else here</a></li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Companies</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Members</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Budget</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Completion</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div>
                            <img src="../../assets/img/small-logos/logo-xd.svg" class="avatar avatar-sm me-3" alt="xd">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Material XD Version</h6>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="avatar-group mt-2">
                          <a href="javascript:;" class="avatar avatar-xs rounded-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Ryan Tompson">
                            <img src="../../assets/img/team-1.jpg" alt="team1">
                          </a>
                          <a href="javascript:;" class="avatar avatar-xs rounded-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Romina Hadid">
                            <img src="../../assets/img/team-2.jpg" alt="team2">
                          </a>
                          <a href="javascript:;" class="avatar avatar-xs rounded-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Alexander Smith">
                            <img src="../../assets/img/team-3.jpg" alt="team3">
                          </a>
                          <a href="javascript:;" class="avatar avatar-xs rounded-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Jessica Doe">
                            <img src="../../assets/img/team-4.jpg" alt="team4">
                          </a>
                        </div>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="text-xs font-weight-bold"> $14,000 </span>
                      </td>
                      <td class="align-middle">
                        <div class="progress-wrapper w-75 mx-auto">
                          <div class="progress-info">
                            <div class="progress-percentage">
                              <span class="text-xs font-weight-bold">60%</span>
                            </div>
                          </div>
                          <div class="progress">
                            <div class="progress-bar bg-gradient-info w-60" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div>
                            <img src="../../assets/img/small-logos/logo-atlassian.svg" class="avatar avatar-sm me-3" alt="atlassian">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Add Progress Track</h6>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="avatar-group mt-2">
                          <a href="javascript:;" class="avatar avatar-xs rounded-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Romina Hadid">
                            <img src="../../assets/img/team-2.jpg" alt="team5">
                          </a>
                          <a href="javascript:;" class="avatar avatar-xs rounded-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Jessica Doe">
                            <img src="../../assets/img/team-4.jpg" alt="team6">
                          </a>
                        </div>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="text-xs font-weight-bold"> $3,000 </span>
                      </td>
                      <td class="align-middle">
                        <div class="progress-wrapper w-75 mx-auto">
                          <div class="progress-info">
                            <div class="progress-percentage">
                              <span class="text-xs font-weight-bold">10%</span>
                            </div>
                          </div>
                          <div class="progress">
                            <div class="progress-bar bg-gradient-info w-10" role="progressbar" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div>
                            <img src="../../assets/img/small-logos/logo-slack.svg" class="avatar avatar-sm me-3" alt="team7">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Fix Platform Errors</h6>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="avatar-group mt-2">
                          <a href="javascript:;" class="avatar avatar-xs rounded-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Romina Hadid">
                            <img src="../../assets/img/team-3.jpg" alt="team8">
                          </a>
                          <a href="javascript:;" class="avatar avatar-xs rounded-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Jessica Doe">
                            <img src="../../assets/img/team-1.jpg" alt="team9">
                          </a>
                        </div>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="text-xs font-weight-bold"> Not set </span>
                      </td>
                      <td class="align-middle">
                        <div class="progress-wrapper w-75 mx-auto">
                          <div class="progress-info">
                            <div class="progress-percentage">
                              <span class="text-xs font-weight-bold">100%</span>
                            </div>
                          </div>
                          <div class="progress">
                            <div class="progress-bar bg-gradient-success w-100" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div>
                            <img src="../../assets/img/small-logos/logo-spotify.svg" class="avatar avatar-sm me-3" alt="spotify">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Launch our Mobile App</h6>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="avatar-group mt-2">
                          <a href="javascript:;" class="avatar avatar-xs rounded-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Ryan Tompson">
                            <img src="../../assets/img/team-4.jpg" alt="user1">
                          </a>
                          <a href="javascript:;" class="avatar avatar-xs rounded-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Romina Hadid">
                            <img src="../../assets/img/team-3.jpg" alt="user2">
                          </a>
                          <a href="javascript:;" class="avatar avatar-xs rounded-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Alexander Smith">
                            <img src="../../assets/img/team-4.jpg" alt="user3">
                          </a>
                          <a href="javascript:;" class="avatar avatar-xs rounded-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Jessica Doe">
                            <img src="../../assets/img/team-1.jpg" alt="user4">
                          </a>
                        </div>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="text-xs font-weight-bold"> $20,500 </span>
                      </td>
                      <td class="align-middle">
                        <div class="progress-wrapper w-75 mx-auto">
                          <div class="progress-info">
                            <div class="progress-percentage">
                              <span class="text-xs font-weight-bold">100%</span>
                            </div>
                          </div>
                          <div class="progress">
                            <div class="progress-bar bg-gradient-success w-100" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div>
                            <img src="../../assets/img/small-logos/logo-jira.svg" class="avatar avatar-sm me-3" alt="jira">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Add the New Pricing Page</h6>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="avatar-group mt-2">
                          <a href="javascript:;" class="avatar avatar-xs rounded-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Ryan Tompson">
                            <img src="../../assets/img/team-4.jpg" alt="user5">
                          </a>
                        </div>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="text-xs font-weight-bold"> $500 </span>
                      </td>
                      <td class="align-middle">
                        <div class="progress-wrapper w-75 mx-auto">
                          <div class="progress-info">
                            <div class="progress-percentage">
                              <span class="text-xs font-weight-bold">25%</span>
                            </div>
                          </div>
                          <div class="progress">
                            <div class="progress-bar bg-gradient-info w-25" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="25"></div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div>
                            <img src="../../assets/img/small-logos/logo-invision.svg" class="avatar avatar-sm me-3" alt="invision">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Redesign New Online Shop</h6>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="avatar-group mt-2">
                          <a href="javascript:;" class="avatar avatar-xs rounded-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Ryan Tompson">
                            <img src="../../assets/img/team-1.jpg" alt="user6">
                          </a>
                          <a href="javascript:;" class="avatar avatar-xs rounded-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Jessica Doe">
                            <img src="../../assets/img/team-4.jpg" alt="user7">
                          </a>
                        </div>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="text-xs font-weight-bold"> $2,000 </span>
                      </td>
                      <td class="align-middle">
                        <div class="progress-wrapper w-75 mx-auto">
                          <div class="progress-info">
                            <div class="progress-percentage">
                              <span class="text-xs font-weight-bold">40%</span>
                            </div>
                          </div>
                          <div class="progress">
                            <div class="progress-bar bg-gradient-info w-40" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="40"></div>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="card h-100">
            <div class="card-header pb-0">
              <h6>Orders overview</h6>
              <p class="text-sm">
                <i class="fa fa-arrow-up text-success" aria-hidden="true"></i>
                <span class="font-weight-bold">24%</span> this month
              </p>
            </div>
            <div class="card-body p-3">
              <div class="timeline timeline-one-side">
                <div class="timeline-block mb-3">
                  <span class="timeline-step">
                    <i class="material-symbols-rounded text-success text-gradient">notifications</i>
                  </span>
                  <div class="timeline-content">
                    <h6 class="text-dark text-sm font-weight-bold mb-0">$2400, Design changes</h6>
                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">22 DEC 7:20 PM</p>
                  </div>
                </div>
                <div class="timeline-block mb-3">
                  <span class="timeline-step">
                    <i class="material-symbols-rounded text-danger text-gradient">code</i>
                  </span>
                  <div class="timeline-content">
                    <h6 class="text-dark text-sm font-weight-bold mb-0">New order #1832412</h6>
                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">21 DEC 11 PM</p>
                  </div>
                </div>
                <div class="timeline-block mb-3">
                  <span class="timeline-step">
                    <i class="material-symbols-rounded text-info text-gradient">shopping_cart</i>
                  </span>
                  <div class="timeline-content">
                    <h6 class="text-dark text-sm font-weight-bold mb-0">Server payments for April</h6>
                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">21 DEC 9:34 PM</p>
                  </div>
                </div>
                <div class="timeline-block mb-3">
                  <span class="timeline-step">
                    <i class="material-symbols-rounded text-warning text-gradient">credit_card</i>
                  </span>
                  <div class="timeline-content">
                    <h6 class="text-dark text-sm font-weight-bold mb-0">New card added for order #4395133</h6>
                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">20 DEC 2:20 AM</p>
                  </div>
                </div>
                <div class="timeline-block mb-3">
                  <span class="timeline-step">
                    <i class="material-symbols-rounded text-primary text-gradient">key</i>
                  </span>
                  <div class="timeline-content">
                    <h6 class="text-dark text-sm font-weight-bold mb-0">Unlock packages for development</h6>
                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">18 DEC 4:54 AM</p>
                  </div>
                </div>
                <div class="timeline-block">
                  <span class="timeline-step">
                    <i class="material-symbols-rounded text-dark text-gradient">payments</i>
                  </span>
                  <div class="timeline-content">
                    <h6 class="text-dark text-sm font-weight-bold mb-0">New order #9583120</h6>
                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">17 DEC</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row mt-3">
          <div class="col-lg-4 col-md-6">
            <div class="card" data-animation="true">
              <div class="card-header p-2 position-relative z-index-2 bg-transparent">
                <a class="d-block blur-shadow-image">
                  <img src="https://static1.thegamerimages.com/wordpress/wp-content/uploads/2022/11/Paimon.jpg" alt="img-blur-shadow" class="img-fluid shadow border-radius-lg">
                </a>
                <div class="colored-shadow" style="background-image: url(https://static1.thegamerimages.com/wordpress/wp-content/uploads/2022/11/Paimon.jpg)"></div>
              </div>
              <div class="card-body text-left">
                <div class="d-flex mt-n6 mx-auto">
                  <a class="btn btn-link text-primary ms-auto border-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Refresh">
                    <i class="material-symbols-rounded text-lg">refresh</i>
                  </a>
                  <button class="btn btn-link text-info me-auto border-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit">
                    <i class="material-symbols-rounded text-lg">edit</i>
                  </button>
                </div>
                <h5 class="font-weight-bold mt-3">
                  <a href="javascript:;">Cozy 5 Stars Apartment</a>
                </h5>
                <p class="mb-0 text-sm">
                  The place is close to Barceloneta Beach and bus stop just 2 min by walk and near to "Naviglio" where you can enjoy the main night life in Barcelona.
                </p>
              </div>
              <hr class="dark horizontal my-0">
              <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <i class="material-symbols-rounded text-lg me-1">place</i>
                  <p class="text-sm my-auto">Barcelona, Spain</p>
                </div>
                <p class="font-weight-bold text-dark my-auto">$899/night</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="card" data-animation="true">
              <div class="card-header p-2 position-relative z-index-2 bg-transparent">
                <a class="d-block blur-shadow-image">
                  <img src="https://static1.thegamerimages.com/wordpress/wp-content/uploads/2022/11/Paimon.jpg" alt="img-blur-shadow" class="img-fluid shadow border-radius-lg">
                </a>
                <div class="colored-shadow" style="background-image: url(https://static1.thegamerimages.com/wordpress/wp-content/uploads/2022/11/Paimon.jpg)"></div>
              </div>
              <div class="card-body text-left">
                <div class="d-flex mt-n6 mx-auto">
                  <a class="btn btn-link text-primary ms-auto border-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Refresh">
                    <i class="material-symbols-rounded text-lg">refresh</i>
                  </a>
                  <button class="btn btn-link text-info me-auto border-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit">
                    <i class="material-symbols-rounded text-lg">edit</i>
                  </button>
                </div>
                <h5 class="font-weight-bold mt-3">
                  <a href="javascript:;">Tibetan Buddhist Temple</a>
                </h5>
                <p class="mb-0 text-sm">
                  Join our unique experience to visit the Tibetan Buddhist Temple in the center of Bali. You will be guided by a local monk and learn about it.
                </p>
              </div>
              <hr class="dark horizontal my-0">
              <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <i class="material-symbols-rounded text-lg me-1">place</i>
                  <p class="text-sm my-auto">Ubud, Bali</p>
                </div>
                <p class="font-weight-bold text-dark my-auto">$1,119/night</p>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="card" data-animation="true">
              <div class="card-header p-2 position-relative z-index-2 bg-transparent">
                <a class="d-block blur-shadow-image">
                  <img src="https://static1.thegamerimages.com/wordpress/wp-content/uploads/2022/11/Paimon.jpg" alt="img-blur-shadow" class="img-fluid shadow border-radius-lg">
                </a>
                <div class="colored-shadow" style="background-image: url(https://static1.thegamerimages.com/wordpress/wp-content/uploads/2022/11/Paimon.jpg)"></div>
              </div>
              <div class="card-body text-left">
                <div class="d-flex mt-n6 mx-auto">
                  <a class="btn btn-link text-primary ms-auto border-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Refresh">
                    <i class="material-symbols-rounded text-lg">refresh</i>
                  </a>
                  <button class="btn btn-link text-info me-auto border-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit">
                    <i class="material-symbols-rounded text-lg">edit</i>
                  </button>
                </div>
                <h5 class="font-weight-bold mt-3">
                  <a href="javascript:;">Beautiful Castle</a>
                </h5>
                <p class="mb-0 text-sm">
                  The place is close to Metro Station and bus stop just 2 min by walk and near to "Naviglio" where you can enjoy the main night life in Milan.
                </p>
              </div>
              <hr class="dark horizontal my-0">
              <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                  <i class="material-symbols-rounded text-lg me-1">place</i>
                  <p class="text-sm my-auto">Milan, Italy</p>
                </div>
                <p class="font-weight-bold text-dark my-auto">$499/night</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <footer class="footer py-4  ">
        <div class="container-fluid">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6 mb-lg-0 mb-4">
              <div class="copyright text-center text-sm text-muted text-lg-start">
                © <script>
                  document.write(new Date().getFullYear())
                </script>,
                made with <i class="fa fa-heart"></i> by
                <a href="https://www.creative-tim.com" class="font-weight-bold" target="_blank">Creative Tim</a>
                for a better web.
              </div>
            </div>
            <div class="col-lg-6">
              <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                <li class="nav-item">
                  <a href="https://www.creative-tim.com" class="nav-link text-muted" target="_blank">Creative Tim</a>
                </li>
                <li class="nav-item">
                  <a href="https://www.creative-tim.com/presentation" class="nav-link text-muted" target="_blank">About Us</a>
                </li>
                <li class="nav-item">
                  <a href="https://www.creative-tim.com/blog" class="nav-link text-muted" target="_blank">Blog</a>
                </li>
                <li class="nav-item">
                  <a href="https://www.creative-tim.com/license" class="nav-link pe-0 text-muted" target="_blank">License</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </main>
  <div class="fixed-plugin">
    <a class="fixed-plugin-button text-dark position-fixed px-3 py-2">
      <i class="material-symbols-rounded py-2">settings</i>
    </a>
    <div class="card shadow-lg">
      <div class="card-header pb-0 pt-3">
        <div class="float-start">
          <h5 class="mt-3 mb-0">Material UI Configurator</h5>
          <p>See our dashboard options.</p>
        </div>
        <div class="float-end mt-4">
          <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
            <i class="material-symbols-rounded">clear</i>
          </button>
        </div>
        <!-- End Toggle Button -->
      </div>
      <hr class="horizontal dark my-1">
      <div class="card-body pt-sm-3 pt-0">
        <!-- Sidebar Backgrounds -->
        <div>
          <h6 class="mb-0">Sidebar Colors</h6>
        </div>
        <a href="javascript:void(0)" class="switch-trigger background-color">
          <div class="badge-colors my-2 text-start">
            <span class="badge filter bg-gradient-primary" data-color="primary" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-dark active" data-color="dark" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-info" data-color="info" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-success" data-color="success" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-warning" data-color="warning" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-danger" data-color="danger" onclick="sidebarColor(this)"></span>
          </div>
        </a>
        <!-- Sidenav Type -->
        <div class="mt-3">
          <h6 class="mb-0">Sidenav Type</h6>
          <p class="text-sm">Choose between different sidenav types.</p>
        </div>
        <div class="d-flex">
          <button class="btn bg-gradient-dark px-3 mb-2" data-class="bg-gradient-dark" onclick="sidebarType(this)">Dark</button>
          <button class="btn bg-gradient-dark px-3 mb-2 ms-2" data-class="bg-transparent" onclick="sidebarType(this)">Transparent</button>
          <button class="btn bg-gradient-dark px-3 mb-2  active ms-2" data-class="bg-white" onclick="sidebarType(this)">White</button>
        </div>
        <p class="text-sm d-xl-none d-block mt-2">You can change the sidenav type just on desktop view.</p>
        <!-- Navbar Fixed -->
        <div class="mt-3 d-flex">
          <h6 class="mb-0">Navbar Fixed</h6>
          <div class="form-check form-switch ps-0 ms-auto my-auto">
            <input class="form-check-input mt-1 ms-auto" type="checkbox" id="navbarFixed" onclick="navbarFixed(this)">
          </div>
        </div>
        <hr class="horizontal dark my-3">
        <div class="mt-2 d-flex">
          <h6 class="mb-0">Light / Dark</h6>
          <div class="form-check form-switch ps-0 ms-auto my-auto">
            <input class="form-check-input mt-1 ms-auto" type="checkbox" id="dark-version" onclick="darkMode(this)">
          </div>
        </div>
        <hr class="horizontal dark my-sm-4">
        <a class="btn bg-gradient-info w-100" href="https://www.creative-tim.com/product/Space">Free Download</a>
        <a class="btn btn-outline-dark w-100" href="https://www.creative-tim.com/learning-lab/bootstrap/overview/material-dashboard">View documentation</a>
        <div class="w-100 text-center">
          <a class="github-button" href="https://github.com/creativetimofficial/material-dashboard" data-icon="octicon-star" data-size="large" data-show-count="true" aria-label="Star creativetimofficial/material-dashboard on GitHub">Star</a>
          <h6 class="mt-3">Thank you for sharing!</h6>
          <a href="https://twitter.com/intent/tweet?text=Check%20Material%20UI%20Dashboard%20made%20by%20%40CreativeTim%20%23webdesign%20%23dashboard%20%23bootstrap5&amp;url=https%3A%2F%2Fwww.creative-tim.com%2Fproduct%2Fsoft-ui-dashboard" class="btn btn-dark mb-0 me-2" target="_blank">
            <i class="fab fa-twitter me-1" aria-hidden="true"></i> Tweet
          </a>
          <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.creative-tim.com/product/material-dashboard" class="btn btn-dark mb-0 me-2" target="_blank">
            <i class="fab fa-facebook-square me-1" aria-hidden="true"></i> Share
          </a>
        </div>
      </div>
    </div>
  </div>
  <!--   Core JS Files   -->
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap.min.js"></script>
  <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/chartjs.min.js"></script>
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
  <script src="../../assets/js/material-dashboard.min.js?v=3.2.0"></script>
  <!-- Handle sign out with SweetAlert2 confirmation -->
  <script>
  function handleSignOut() {
      Swal.fire({
          title: 'Sign Out',
          text: 'Are you sure you want to sign out?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Yes, sign out',
          cancelButtonText: 'No, cancel',
          reverseButtons: true,
          customClass: {
              confirmButton: 'btn bg-gradient-primary btn-sm mx-2',
              cancelButton: 'btn btn-outline-primary btn-sm mx-2',
              actions: 'justify-content-center'
          },
          buttonsStyling: false,
          allowOutsideClick: false
      }).then((result) => {
          if (result.isConfirmed) {
              // Show loading state
              Swal.fire({
                  title: 'Signing out...',
                  text: 'Please wait',
                  icon: 'info',
                  allowOutsideClick: false,
                  showConfirmButton: false,
                  willOpen: () => {
                      Swal.showLoading();
                  }
              });

              // Redirect to logout script after a brief delay
              setTimeout(() => {
                  window.location.href = '../../admin_operations/logout.php';
              }, 1000);
          }
      });
  }
  </script>
  <script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded');
    // Check if required elements exist
    console.log('Sidenav exists:', !!document.getElementById('sidenav-main'));
    console.log('Main content exists:', !!document.querySelector('.main-content'));
    
    // Log any JavaScript errors
    window.onerror = function(msg, url, lineNo, columnNo, error) {
        console.error('Error: ' + msg + '\nURL: ' + url + '\nLine: ' + lineNo + '\nColumn: ' + columnNo + '\nError object: ' + JSON.stringify(error));
        return false;
    };
});
</script>
</body>

</html>