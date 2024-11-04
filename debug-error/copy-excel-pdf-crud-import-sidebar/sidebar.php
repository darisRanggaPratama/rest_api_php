<?php
// sidebar.php
?>
<div class="sidebar-wrapper">
    <!-- Sidebar -->
    <nav id="sidebar" class="bg-dark text-white">
        <div class="sidebar-header p-3 d-flex align-items-center">
            <h5 class="mb-0 text-white flex-grow-1">Menu Navigation</h5>
            <button class="btn btn-link text-white sidebar-close d-lg-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="list-group list-group-flush">
            <a href="index.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-home me-2"></i> Dashboard
            </a>
            <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-user me-2"></i> Profile
            </a>
            <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-cog me-2"></i> Settings
            </a>
        </div>
    </nav>

    <!-- Main Content Header -->
    <div class="main-header">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center py-3">
                <button class="btn btn-link sidebar-toggle text-dark me-3" type="button">
                    <i class="fas fa-bars"></i>
                </button>
                <h4 class="mb-0">Data Avengers</h4>
            </div>
        </div>
    </div>
</div>

<style>
    /* Improved Sidebar Styles */
    .sidebar-wrapper {
        min-height: 100vh;
    }

    #sidebar {
        width: 250px;
        position: fixed;
        top: 0;
        left: -250px;
        height: 100vh;
        z-index: 1050;
        transition: all 0.3s ease-in-out;
        box-shadow: 3px 0 6px rgba(0,0,0,0.1);
    }

    /* Show sidebar on hover of the wrapper */
    .sidebar-wrapper:hover #sidebar {
        left: 0;
    }

    /* Show a thin strip for hover target when sidebar is hidden */
    .sidebar-hover-target {
        position: fixed;
        top: 0;
        left: 0;
        width: 15px;
        height: 100vh;
        z-index: 1049;
        background: transparent;
    }

    /* Main Header Styles */
    .main-header {
        position: fixed;
        top: 0;
        left: 15px; /* Account for hover strip */
        right: 0;
        background: #fff;
        z-index: 1040;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease-in-out;
    }

    .sidebar-wrapper:hover + .content-wrapper .main-header {
        left: 250px;
    }

    /* Adjust main content */
    .content-wrapper {
        padding-top: 70px;
        margin-left: 15px; /* Account for hover strip */
        transition: all 0.3s ease-in-out;
    }

    .sidebar-wrapper:hover + .content-wrapper {
        margin-left: 250px;
    }

    /* List group item hover effect */
    .list-group-item:hover {
        background-color: #495057 !important;
        color: #fff !important;
    }

    /* Hide toggle button as it's no longer needed */
    .sidebar-toggle {
        display: none;
    }

    /* Responsive styles */
    @media (max-width: 991.98px) {
        .sidebar-hover-target {
            display: none; /* Disable hover functionality on mobile */
        }

        .content-wrapper {
            margin-left: 0;
        }

        .main-header {
            left: 0;
        }

        .sidebar-toggle {
            display: block; /* Show toggle button on mobile */
        }

        /* Reset hover behaviors for mobile */
        .sidebar-wrapper:hover #sidebar {
            left: -250px;
        }

        .sidebar-wrapper:hover + .content-wrapper {
            margin-left: 0;
        }

        .sidebar-wrapper:hover + .content-wrapper .main-header {
            left: 0;
        }

        /* Mobile sidebar behavior */
        #sidebar.active {
            left: 0;
        }

        .content-wrapper.sidebar-active {
            margin-left: 0;
        }

        .sidebar-active .main-header {
            left: 0;
        }
    }
</style>

<script>
    // Update sidebar.php script section
    $(document).ready(function() {
        // Add hover target div after sidebar
        $('<div class="sidebar-hover-target"></div>').insertAfter('#sidebar');

        // Mobile toggle functionality
        function toggleSidebar() {
            $('#sidebar').toggleClass('active');
            $('.content-wrapper').toggleClass('sidebar-active');

            // Handle backdrop for mobile
            if ($('#sidebar').hasClass('active')) {
                $('<div class="sidebar-backdrop"></div>').appendTo('body');
            } else {
                $('.sidebar-backdrop').remove();
            }
        }

        // Mobile-only events
        $('.sidebar-toggle').on('click', function(e) {
            e.preventDefault();
            toggleSidebar();
        });

        $('.sidebar-close').on('click', function(e) {
            e.preventDefault();
            toggleSidebar();
        });

        // Close sidebar when clicking backdrop (mobile only)
        $(document).on('click', '.sidebar-backdrop', function() {
            toggleSidebar();
        });

        // Handle window resize
        let isMobile = window.matchMedia("(max-width: 991.98px)").matches;

        $(window).on('resize', function() {
            let wasNotMobile = !isMobile;
            isMobile = window.matchMedia("(max-width: 991.98px)").matches;

            // Clean up if switching between mobile and desktop
            if (wasNotMobile && isMobile) {
                $('#sidebar').removeClass('active');
                $('.content-wrapper').removeClass('sidebar-active');
                $('.sidebar-backdrop').remove();
            }
        });
    });
</script>