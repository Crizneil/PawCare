<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | PawCare</title>

    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/themify-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/sass/style.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">

    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/newicon.png') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

    <style>
        .grayscale {
            filter: grayscale(100%);
            transition: filter 0.3s ease;
        }

        .tiny-badge {
            font-size: 8px;
            font-weight: 800;
            letter-spacing: 0.5px;
            padding: 2px 6px;
        }

        .grayscale:hover {
            filter: grayscale(0%);
        }

        /* Force SweetAlert to appear in front of Bootstrap Modals */
        .swal2-container {
            z-index: 999999 !important;
        }
    </style>
</head>

<body>
    <div id="sidebarOverlay" class="sidebar-overlay"></div>
    <div class="d-flex">
        <aside id="sidebar" class="d-flex flex-column px-3 py-4">
            <div class="p-2 d-flex justify-content-end d-md-none">
                <button id="closeSidebar" class="btn border-0 text-dark">
                    <i class="bi bi-x-lg" style="font-size: 24px;"></i>
                </button>
            </div>

            <div class="mb-4 px-3 d-none d-md-block">
                <img src="{{ asset('assets/images/newlogo.png') }}" alt="PawCare" style="height: 40px;">
            </div>

            <div class="sidebar-nav-scroll flex-grow-1 px-2">
                <nav class="nav flex-column">
                    {{-- ADMIN LINKS --}}
                    @if(Auth::check() && strtolower(Auth::user()->role) === 'admin')
                        <a href="{{ route('admin.dashboard') }}"
                            class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i data-lucide="layout-grid"></i> Overview
                        </a>
                        <a href="{{ route('admin.appointments') }}"
                            class="nav-link {{ request()->is('admin/appointments*') ? 'active' : '' }}">
                            <i data-lucide="calendar"></i> Appointments
                        </a>
                        <a href="{{ route('admin.pet-records') }}"
                            class="nav-link {{ request()->routeIs('admin.pet-records') ? 'active' : '' }}">
                            <i data-lucide="shield-check"></i> Pet Database
                        </a>
                        <a href="{{ route('admin.employees') }}"
                            class="nav-link {{ request()->is('admin/employees*') ? 'active' : '' }}">
                            <i data-lucide="users"></i> Staff
                        </a>
                        <a href="{{ route('admin.logs') }}"
                            class="nav-link {{ request()->is('admin/logs*') ? 'active' : '' }}">
                            <i data-lucide="history"></i> Logs
                        </a>
                        <a href="{{ route('admin.archive') }}"
                            class="nav-link {{ request()->is('admin/archive*') ? 'active' : '' }}">
                            <i data-lucide="archive"></i> Archive Center
                        </a>
                        <a href="{{ route('admin.vaccination-status') }}"
                            class="nav-link {{ request()->routeIs('admin.vaccination-status') ? 'active' : '' }}">
                            <i data-lucide="syringe"></i> Vaccination Status
                        </a>
                        <a href="{{ route('admin.vaccinations') }}"
                            class="nav-link {{ request()->routeIs('admin.vaccinations') ? 'active' : '' }}">
                            <i data-lucide="package"></i> Vaccine Inventory
                        </a>
                        <a href="{{ route('admin.profile') }}"
                            class="nav-link {{ request()->is('admin/profile*') ? 'active' : '' }}">
                            <i data-lucide="user-cog"></i> Profile
                        </a>

                        {{-- STAFF LINKS --}}
                    @elseif(Auth::check() && strtolower(Auth::user()->role) === 'staff')
                        <a href="{{ route('staff.dashboard') }}"
                            class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                            <i data-lucide="layout-dashboard"></i> Overview
                        </a>
                        <a href="{{ route('staff.appointments') }}"
                            class="nav-link {{ request()->is('staff/appointments*') ? 'active' : '' }}">
                            <i data-lucide="calendar"></i> Appointments
                        </a>
                        <a href="{{ route('staff.pet-records') }}"
                            class="nav-link {{ request()->routeIs('staff.pet-records') ? 'active' : '' }}">
                            <i data-lucide="file-spreadsheet"></i> Pet Records
                        </a>
                        <a href="{{ route('staff.vaccination-status') }}"
                            class="nav-link {{ request()->routeIs('staff.vaccination-status') ? 'active' : '' }}">
                            <i data-lucide="syringe"></i> Vaccination Status
                        </a>
                        <a href="{{ route('staff.vaccination-history') }}"
                            class="nav-link {{ request()->routeIs('staff.vaccination-history') ? 'active' : '' }}">
                            <i data-lucide="history"></i> Vaccination History
                        </a>
                        <a href="{{ route('staff.vaccine-inventory') }}"
                            class="nav-link {{ request()->routeIs('staff.vaccine-inventory') ? 'active' : '' }}">
                            <i data-lucide="package"></i> Vaccine Inventory
                        </a>
                        <a href="{{ route('staff.profile') }}"
                            class="nav-link {{ request()->is('staff/profile*') ? 'active' : '' }}">
                            <i data-lucide="user-cog"></i> Profile
                        </a>

                        {{-- PET OWNER LINKS --}}
                    @elseif(Auth::check() && strtolower(Auth::user()->role) === 'owner')
                        <a href="{{ route('pet-owner.dashboard') }}"
                            class="nav-link {{ request()->routeIs('pet-owner.dashboard') ? 'active' : '' }}">
                            <i data-lucide="layout-dashboard"></i> Overview
                        </a>
                        <a href="{{ route('pet-owner.appointments') }}"
                            class="nav-link {{ request()->routeIs('pet-owner.appointments') ? 'active' : '' }}">
                            <i data-lucide="calendar"></i> Appointment
                        </a>
                        <a href="{{ route('pet-owner.pet-records') }}"
                            class="nav-link {{ request()->routeIs('pet-owner.pet-records') ? 'active' : '' }}">
                            <i data-lucide="dog"></i> My Pets
                        </a>
                        <a href="{{ route('pet-owner.vaccination-history') }}"
                            class="nav-link {{ request()->routeIs('pet-owner.vaccination-history') ? 'active' : '' }}">
                            <i data-lucide="syringe"></i> Vaccination History
                        </a>
                        <a href="{{ route('pet-owner.profile') }}"
                            class="nav-link {{ request()->routeIs('pet-owner.profile') ? 'active' : '' }}">
                            <i data-lucide="user"></i> Profile
                        </a>
                    @endif
                </nav>
            </div>

            <div class="mt-auto pt-3 px-2">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-logout w-100 d-flex align-items-center justify-content-center">
                        <i data-lucide="log-out" class="me-2"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        <div class="content-area flex-grow-1">

            @include('layout.header')

            <div class="main-content-wrapper">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/admin.js') }}"></script>
    @stack('scripts')
    <script>
        lucide.createIcons();

        $(document).ready(function () {
            // Handle Session Alerts
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    showConfirmButton: true
                });
            @endif

            @if(session('status_changed') && session('status_changed')['type'] === 'DECEASED')
                Swal.fire({
                    title: 'In Loving Memory',
                    text: 'Pet record for {{ session('status_changed')['pet_name'] }} has been updated to DECEASED status. This record is now archived.',
                    icon: 'info',
                    iconColor: '#2c3e50',
                    confirmButtonText: 'Understood',
                    confirmButtonColor: '#2c3e50',
                    background: '#f8f9fa',
                    customClass: {
                        popup: 'rounded-4 border-0 shadow-lg',
                        title: 'fw-bold text-dark',
                        confirmButton: 'rounded-pill px-5'
                    }
                });
            @endif

            // Global Form Submission Handler
            $(document).on('submit', 'form', function (e) {
                const $form = $(this);

                // Prevent duplicate prompt if already confirmed
                if ($form.data('confirmed')) {
                    return true;
                }

                const $submitBtn = $form.find('button[type="submit"]');
                const $statusSelect = $form.find('select[name="status"]');

                // Identify action types
                const isDeleteAction = $form.find('input[name="_method"][value="DELETE"]').length > 0 ||
                                       $submitBtn.text().trim().toLowerCase().includes('delete');
                const isDeceasedStatus = $statusSelect.length && $statusSelect.val() === 'DECEASED';

                // Bypass custom logic for specific delete modals
                if (isDeleteAction && $form.closest('[id^="delete"]').length > 0) {
                    return true;
                }

                // Intercept DECEASED status modifications
                if (isDeceasedStatus) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Confirm Deceased Status',
                        text: "Marking this pet as DECEASED will archive the record. This is a solemn action.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#2c3e50',
                        confirmButtonText: 'Yes, confirm DECEASED',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            popup: 'rounded-4 border-0 shadow-lg',
                            confirmButton: 'rounded-pill px-4',
                            cancelButton: 'rounded-pill px-4'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $form.data('confirmed', true);
                            $form[0].submit();
                        }
                    });

                    return;
                }

                // Automatically close Bootstrap modals on successful submission
                const $modal = $form.closest('.modal');
                if ($modal.length && typeof bootstrap !== 'undefined') {
                    const modalInstance = bootstrap.Modal.getInstance($modal[0]);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }
            });
        });
    </script>
    @include('partials._chat_widget')
</body>

</html>
