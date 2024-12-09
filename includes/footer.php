<!--   Core JS Files   -->
<script src="../../assets/js/core/popper.min.js"></script>
<script src="../../assets/js/core/bootstrap.min.js"></script>
<script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="../../assets/js/plugins/chartjs.min.js"></script>

<!-- Control Center for Material Dashboard -->
<script src="../../assets/js/material-dashboard.min.js?v=3.0.6"></script>

<!-- Github buttons -->
<script async defer src="https://buttons.github.io/buttons.js"></script>

<!-- Custom Scripts -->
<script src="../../assets/js/signout.js"></script>
<script src="../../assets/js/notification-handler.js"></script>
<script src="../../assets/js/support.js"></script>
<script src="../../assets/js/support-messages.js"></script>

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
<script>
    function openGithub() {
        window.open('https://github.com/yourusername/yourrepository', '_blank');
    }
</script> 