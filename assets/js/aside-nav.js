document.addEventListener('DOMContentLoaded', function() {
    const iconNavbarSidenav = document.getElementById('iconNavbarSidenav');
    const iconSidenav = document.getElementById('iconSidenav');
    const body = document.getElementsByTagName('body')[0];

    function toggleSidenav() {
        if (window.innerWidth >= 1200) {
            body.classList.toggle('g-sidenav-hidden');
        } else {
            body.classList.toggle('g-sidenav-pinned');
        }
    }

    if (iconNavbarSidenav) {
        iconNavbarSidenav.addEventListener('click', toggleSidenav);
    }

    if (iconSidenav) {
        iconSidenav.addEventListener('click', toggleSidenav);
    }

    // Initial state
    if (window.innerWidth >= 1200) {
        body.classList.remove('g-sidenav-hidden');
    } else {
        body.classList.remove('g-sidenav-pinned');
    }

    // Handle window resize
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            if (window.innerWidth >= 1200) {
                body.classList.remove('g-sidenav-hidden');
                body.classList.remove('g-sidenav-pinned');
            } else {
                body.classList.remove('g-sidenav-pinned');
                body.classList.remove('g-sidenav-hidden');
            }
        }, 100);
    });
}); 