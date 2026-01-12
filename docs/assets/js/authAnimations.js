document.addEventListener('DOMContentLoaded', function() {
    // Initialize ScrollReveal with slower animations
    const sr = ScrollReveal({
        origin: 'top',
        distance: '60px',
        duration: 1000,
        delay: 500,
        reset: false
    });

    // Signin/Signup Pages Common Elements
    // Background Image Animation with fade-in
    sr.reveal('.col-6.d-lg-flex', { 
        origin: 'left',
        distance: '0', // Remove distance for fade effect
        duration: 500,
        opacity: 0,
        scale: 1,
        easing: 'ease-in-out'
    });

    // Card Animation
    sr.reveal('.card.card-plain', {
        origin: 'right',
        distance: '100px',
        duration: 2500,
        delay: 400
    });

    // Form Elements - Cascading effect
    sr.reveal('.card-header', {
        delay: 500,
        duration: 1500,
        distance: '40px'
    });

    // Input Fields - Sequential animation
    sr.reveal('.input-group-outline', {
        delay: 600,
        interval: 300,
        distance: '40px',
        origin: 'bottom'
    });

    // Button and Links
    sr.reveal('.btn-lg', {
        delay: 1500,
        duration: 1500,
        distance: '30px',
        origin: 'bottom'
    });

    sr.reveal('.card-footer', {
        delay: 1700,
        duration: 1500,
        distance: '30px',
        origin: 'bottom'
    });

    // Specific to Sign Up page
    if (document.getElementById('signupForm')) {
        // Terms checkbox
        sr.reveal('.form-check', {
            delay: 1600,
            duration: 1500,
            distance: '30px',
            origin: 'bottom'
        });
    }

    // Home button animation
    sr.reveal('.navbar', {
        origin: 'top',
        distance: '20px',
        duration: 1500,
        delay: 200
    });
}); 