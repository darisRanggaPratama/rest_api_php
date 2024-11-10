$(document).ready(function() {
    // Toggle sidebar
    $('.toggle-btn').on('click', function() {
        $('.sidebar').toggleClass('show');
        $('.content-wrapper').toggleClass('shifted');
    });

    // Close sidebar when clicking outside on mobile
    $(document).on('click touchstart', function(e) {
        if ($(window).width() <= 768) {
            if (!$(e.target).closest('.sidebar').length && 
                !$(e.target).closest('.toggle-btn').length && 
                $('.sidebar').hasClass('show')) {
                $('.sidebar').removeClass('show');
                $('.content-wrapper').removeClass('shifted');
            }
        }
    });
});