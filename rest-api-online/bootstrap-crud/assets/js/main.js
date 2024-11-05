$(document).ready(function () {
    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
        $('#content').toggleClass('active');
    });

    // Close sidebar on mobile when clicking outside
    $(document).click(function (e) {
        const sidebar = $("#sidebar");
        const button = $("#sidebarCollapse");

        if (!sidebar.is(e.target) &&
            !button.is(e.target) &&
            button.has(e.target).length === 0 &&
            sidebar.has(e.target).length === 0) {

            if ($(window).width() <= 768) {
                sidebar.addClass('active');
                $('#content').removeClass('active');
            }
        }
    });

    // Adjust sidebar on window resize
    $(window).resize(function () {
        if ($(window).width() <= 768) {
            $('#sidebar').addClass('active');
            $('#content').removeClass('active');
        }
    });
});