$(document).ready(function () {

    function adminDashboardLogic() {
        const $sidebar = $('#sidebar');
        const $overlay = $('#sidebarOverlay');
        // Targets the hamburger in header AND the toggle button
        const $toggleBtn = $('#sidebarToggle, .open-btn');
        const $closeBtn = $('#closeSidebar');

        function toggleMenu(e) {
            if(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            if ($(window).width() <= 768) {
                $sidebar.toggleClass('active');
                $overlay.toggleClass('active');
                $('body').toggleClass('overflow-hidden');
            } else {
                $sidebar.toggleClass('collapsed');
                // Save state to localStorage
                localStorage.setItem('sidebarCollapsed', $sidebar.hasClass('collapsed'));
            }
        }

        // Apply saved state on load for desktop
        if ($(window).width() > 768) {
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                $sidebar.addClass('collapsed');
            }
        }

        // Open/Toggle
        if ($toggleBtn.length) {
            $toggleBtn.on('click', toggleMenu);
        }

        // Close via X button or Overlay
        if ($closeBtn.length) $closeBtn.on('click', toggleMenu);
        if ($overlay.length) $overlay.on('click', toggleMenu);

        // Auto-close if user expands window to desktop size
        $(window).on('resize', function () {
            if ($(window).width() > 768 && $sidebar.hasClass('active')) {
                $sidebar.removeClass('active');
                $overlay.removeClass('active');
                $('body').removeClass('overflow-hidden');
            }
        });

        // Logout logic (Keep this as is)
        var logoutBtn = $('#logoutBtnSide');
        var logoutForm = $('#logoutForm');
        if (logoutBtn.length && logoutForm.length) {
            logoutBtn.on('click', function (e) {
                e.preventDefault();
                logoutForm.submit();
            });
        }
    }

    // --- NEW: Vaccination Auto-Status Logic ---
    function initVaccinationLogic() {
        // Use delegated event listener so it works even if modals are loaded dynamically
        $(document).on('change', 'input[name="next_due_date"]', function () {
            const $input = $(this);
            const $modal = $input.closest('.modal');
            const $statusSelect = $modal.find('select[name="status"]');

            if (!$input.val()) return; // Do nothing if date is cleared

            const dueDate = new Date($input.val());
            const today = new Date();

            // Reset hours for accurate date comparison
            today.setHours(0, 0, 0, 0);
            dueDate.setHours(0, 0, 0, 0);

            // Calculate difference in days
            const diffTime = dueDate - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays < 0) {
                $statusSelect.val('overdue');
            } else if (diffDays <= 14) {
                $statusSelect.val('due_soon');
            } else {
                $statusSelect.val('fully_vaccinated');
            }
        });
    }

    // --- Profile Image Preview Logic ---
    var imageInput = $('#imageInput');
    if (imageInput.length) {
        imageInput.on('change', function () {
            var file = this.files[0];
            var preview = $('#previewImage');

            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    preview.attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });
    }

    function handleMenu() {
        // This allows your sidebar to toggle on mobile without crashing the script
        $('#sidebar, #sidebarOverlay').toggleClass('active');
    }

    // Initialize all functions
    adminDashboardLogic();
    initVaccinationLogic();
});
