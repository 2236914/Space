document.addEventListener('DOMContentLoaded', function() {
    // Initialize ScrollReveal with slower animations
    const sr = ScrollReveal({
        origin: 'bottom',
        distance: '60px',
        duration: 1500, // Increase duration for slower animations
        delay: 300,     // Increase delay for a more gradual start
        reset: true
    });

    // Home Section
    sr.reveal('#home .col-lg-6:first-child', { 
        origin: 'left',
        delay: 500
    });
    sr.reveal('#home .col-lg-6:last-child', { 
        origin: 'right',
        delay: 550
    });

    // About Section
    sr.reveal('#about .col-lg-6:first-child', {
        origin: 'left',
        delay: 550
    });
    sr.reveal('#about .col-lg-6:last-child', {
        origin: 'right',
        delay: 550
    });

    // Team Section
    sr.reveal('#team .col-12.mb-5', {
        delay: 300
    });
    sr.reveal('#team .col-lg-4', {
        interval: 400
    });

    // Therapy Section
    sr.reveal('#therapy .col-md-6', {
        interval: 300
    });
    sr.reveal('#therapy .col-lg-4', {
        origin: 'right',
        delay: 500
    });

    // Contact Section
    sr.reveal('#contact .col-lg-6:first-child', {
        origin: 'left',
        delay: 500
    });
    sr.reveal('#contact .col-lg-6:last-child', {
        origin: 'right',
        delay: 600
    });

    // FAQ Section
    sr.reveal('.accordion-item', {
        interval: 300
    });

    // Footer as a whole
    sr.reveal('footer', {
        origin: 'top',
        delay: 600
    });

    // Initialize other existing scripts
    const tncModal = new bootstrap.Modal(document.getElementById('tncModal'));
    
    window.openTncModal = function() {
        tncModal.show();
    }

    window.closeTncModal = function() {
        tncModal.hide();
    }
}); 