@extends('layout.admin')

@section('page_title', 'Appointments Dashboard')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Appointment Management</h2>
                <p class="text-muted small">Schedule and manage pet healthcare visits.</p>
            </div>

            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-dark rounded-pill px-4 py-2 shadow-sm fw-bold dropdown-toggle" type="button" id="reportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Generate Report
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3" aria-labelledby="reportDropdown">
                        <li><h6 class="dropdown-header">Select Timeframe</h6></li>
                        <li>
                            {{-- Added target="_blank" --}}
                            <a class="dropdown-item py-2" href="{{ route('admin.reports.appointments', ['range' => 'daily']) }}" target="_blank">
                                <i class="bi bi-calendar-event me-2 text-primary"></i> Daily Report
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('admin.reports.appointments', ['range' => 'weekly']) }}" target="_blank">
                                <i class="bi bi-calendar-week me-2 text-success"></i> Weekly Report
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('admin.reports.appointments', ['range' => 'monthly']) }}" target="_blank">
                                <i class="bi bi-calendar-month me-2 text-orange"></i> Monthly Report
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card shadow-sm border-0 h-100 p-3 stat-card-hover"
                    style="background: linear-gradient(135deg, #ffffff 60%, #fffbf7 100%);">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-orange-subtle p-2 p-md-3 me-2 me-md-3">
                            <i class="bi bi-calendar-check text-orange fs-5 fs-md-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small fw-bold text-uppercase" style="font-size: 0.7rem;">Today</h6>
                            <h3 class="fw-bold mb-0 text-dark">{{ $counts['today'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card shadow-sm border-0 h-100 p-3 stat-card-hover"
                    style="background: linear-gradient(135deg, #ffffff 60%, #fffdf0 100%);">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning-subtle p-2 p-md-3 me-2 me-md-3">
                            <i class="bi bi-hourglass-split text-warning fs-5 fs-md-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small fw-bold text-uppercase" style="font-size: 0.7rem;">Pending</h6>
                            <h3 class="fw-bold mb-0 text-dark">{{ $counts['pending'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card shadow-sm border-0 h-100 p-3 stat-card-hover"
                    style="background: linear-gradient(135deg, #ffffff 60%, #f0f7ff 100%);">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary-subtle p-2 p-md-3 me-2 me-md-3">
                            <i class="bi bi-check2-circle text-primary fs-5 fs-md-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small fw-bold text-uppercase" style="font-size: 0.7rem;">Approved
                            </h6>
                            <h3 class="fw-bold mb-0 text-dark">{{ $counts['approved'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card shadow-sm border-0 h-100 p-3 stat-card-hover"
                    style="background: linear-gradient(135deg, #ffffff 60%, #f0fdf4 100%);">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success-subtle p-2 p-md-3 me-2 me-md-3">
                            <i class="bi bi-flag-fill text-success fs-5 fs-md-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0 small fw-bold text-uppercase" style="font-size: 0.7rem;">Done</h6>
                            <h3 class="fw-bold mb-0 text-dark">{{ $counts['completed'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        {{-- Master Appointment Calendar --}}
        <div class="card shadow-sm border-0 mb-2 p-3 rounded-4">
            <div id="masterCalendar"></div>
        </div>
        {{-- Calendar Legend --}}
        <div class="d-flex flex-wrap align-items-center gap-3 mb-4 small text-muted">
            <div class="d-flex align-items-center gap-2">
                <span class="rounded-circle" style="width: 12px; height: 12px; background-color: #fd7e14;"></span>
                <span>Pending</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="rounded-circle" style="width: 12px; height: 12px; background-color: #0d6efd;"></span>
                <span>Approved</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="rounded-circle" style="width: 12px; height: 12px; background-color: #198754;"></span>
                <span>Completed / Done</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="rounded-circle" style="width: 12px; height: 12px; background-color: #dc3545;"></span>
                <span>Cancelled</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="rounded-circle" style="width: 12px; height: 12px; background-color: #6c757d;"></span>
                <span>Rejected / Other</span>
            </div>
        </div>
    </div>

    {{-- Modal Fragments --}}
    @foreach($appointments as $appointment)
        @include('partials._reject_modal')
        @include('partials._cancel_modal')
    @endforeach

    <!-- Event Details Modal dynamically populated by JS -->
    <div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold text-dark m-0">Appointment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <span class="small fw-bold text-muted d-block mb-1">Pet Information</span>
                        <h5 class="fw-bold text-dark mb-0" id="eventPetName"></h5>
                        <span class="badge bg-light text-dark border mt-1" id="eventSpecies"></span>
                    </div>
                    <div class="row mb-4">
                        <div class="col-6">
                            <span class="small fw-bold text-muted d-block mb-1">Owner</span>
                            <div class="fw-bold text-dark" id="eventOwnerName"></div>
                        </div>
                        <div class="col-6">
                            <span class="small fw-bold text-muted d-block mb-1">Contact</span>
                            <div class="fw-bold text-dark" id="eventOwnerPhone"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <span class="small fw-bold text-muted d-block mb-1">Address</span>
                        <div class="fw-bold text-dark" id="eventOwnerAddress"></div>
                    </div>
                    <div class="mb-4">
                        <span class="small fw-bold text-muted d-block mb-1">Service Requested</span>
                        <div class="p-2 border rounded-3 bg-light d-inline-block px-3">
                            <i class="bi bi-tag-fill text-orange me-1"></i>
                            <span class="fw-bold text-dark" id="eventService"></span>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-6">
                            <span class="small fw-bold text-muted d-block mb-1">Date</span>
                            <div class="fw-bold text-primary" id="eventDate"></div>
                        </div>
                        <div class="col-6">
                            <span class="small fw-bold text-muted d-block mb-1">Time</span>
                            <div class="fw-bold text-primary" id="eventTime"></div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded-3 mb-4">
                        <span class="fw-bold text-secondary">Status:</span>
                        <span class="badge rounded-pill px-3 py-2 fs-6" id="eventStatusBadge"></span>
                    </div>

                    <div id="eventActionsContainer" class="d-grid gap-2">
                        <!-- Dynamic form actions injected here by JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <style>
        /* Card Hover Animations */
        .stat-card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.1) !important;
        }

        /* Modern Calendar Admin Overrides */
        #masterCalendar {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: #ffffff;
            border-radius: 16px;
        }

        .fc .fc-toolbar-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: -0.5px;
        }

        .fc-theme-standard td,
        .fc-theme-standard th {
            border-color: #f1f5f9;
        }

        .fc-scrollgrid {
            border: none !important;
        }

        .fc-day-today {
            background-color: #f8fafc !important;
        }

        .fc .fc-button-primary {
            background-color: #3b82f6;
            border-color: #3b82f6;
            color: #fff;
            text-transform: capitalize;
            border-radius: 8px;
            font-weight: 600;
            padding: 0.5rem 1rem;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2);
        }

        .fc .fc-button-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
            box-shadow: 0 6px 8px -1px rgba(59, 130, 246, 0.3);
            transform: translateY(-1px);
        }

        .fc .fc-button-primary:not(:disabled):active,
        .fc .fc-button-primary:not(:disabled).fc-button-active {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }

        .fc-event {
            cursor: pointer;
            border-radius: 6px;
            padding: 4px 8px;
            border: none !important;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .fc-event:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            filter: brightness(1.05);
        }

        .fc-event-main {
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.2px;
        }

        /* Modal Enhancements */
        .modal-content {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
        }
    </style>

    <script>
    function openRejectModalWithConfirm(appointmentId) {
        // 1. Hide the FullCalendar detail modal first to prevent backdrop bugs
        const detailModalEl = document.getElementById('eventDetailsModal');
        const detailModal = bootstrap.Modal.getInstance(detailModalEl);
        if (detailModal) detailModal.hide();

        // 2. Trigger your specific Blade modal from the partials
        const rejectModalEl = document.getElementById('rejectModal' + appointmentId);

        if (rejectModalEl) {
            const rejectModal = new bootstrap.Modal(rejectModalEl);
            rejectModal.show();
        } else {
            console.error("Modal not found: rejectModal" + appointmentId);
        }
    }

        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('masterCalendar');

            // Setup Modal Elements
            var eventModal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
            var eventPetName = document.getElementById('eventPetName');
            var eventSpecies = document.getElementById('eventSpecies');
            var eventService = document.getElementById('eventService');
            var eventOwnerName = document.getElementById('eventOwnerName');
            var eventOwnerPhone = document.getElementById('eventOwnerPhone');
            var eventDate = document.getElementById('eventDate');
            var eventTime = document.getElementById('eventTime');
            var eventStatusBadge = document.getElementById('eventStatusBadge');
            var eventActionsContainer = document.getElementById('eventActionsContainer');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay' // restore Month/Week/Day buttons
                },
                height: 'auto',
                events: '{{ route("admin.api.appointments") }}',
                editable: false, // disable drag & drop to avoid “moving” events
                droppable: false,

                // Handle Event Click Detail View
                eventClick: function (info) {
                info.jsEvent.preventDefault();
                let props = info.event.extendedProps;
                let statusStr = props.status.toLowerCase();

                // 1. Set Pet Info
                eventPetName.innerText = info.event.title.split(' (')[0];
                eventSpecies.innerText = props.species;
                eventService.innerText = props.service || 'Not Specified';

                // 2. Handle Owner Information
                let displayName = "Guest/Walk-in";
                let displayPhone = "N/A";
                let displayAddress = "Not Provided";

                // Check for Registered Owner first
                if (props.owner_name && props.owner_name !== 'Guest/Walk-in') {
                    displayName = props.owner_name;
                    displayPhone = props.owner_phone || 'N/A';
                    displayAddress = props.owner_address || 'Not Provided';
                }
                // Fallback to Guest Info if it's a walk-in
                else if (props.guest_name) {
                    displayName = props.guest_name + " (Guest)";
                    displayPhone = props.guest_phone || 'N/A';
                    displayAddress = props.guest_address || 'Not Provided';
                }

                eventOwnerName.innerText = displayName;
                eventOwnerPhone.innerText = displayPhone;
                document.getElementById('eventOwnerAddress').innerText = displayAddress;

                // 3. Format Date and ACTUAL Time
                eventDate.innerText = info.event.start.toLocaleDateString('default', {
                    weekday: 'short', month: 'long', day: 'numeric', year: 'numeric'
                });
                eventTime.innerText = info.event.start.toLocaleTimeString('en-US', {
                    hour: 'numeric', minute: '2-digit', hour12: true
                });

                // 4. Status Badge
                eventStatusBadge.innerText = props.status;
                eventStatusBadge.className = "badge rounded-pill px-3 py-2 fs-6 text-white";
                eventStatusBadge.style.backgroundColor = info.event.backgroundColor;

                // 5. Build dynamic action buttons (Keeping your existing logic)
                let actionsHtml = '';
                if (statusStr === 'pending') {
                    actionsHtml += `
                        <form action="/admin/appointments/${info.event.id}/approve" method="POST" class="d-grid mb-2">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="btn btn-success rounded-pill fw-bold text-uppercase py-3 shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Approve Appointment
                            </button>
                        </form>
                        <button type="button" class="btn btn-outline-danger rounded-pill fw-bold text-uppercase py-3 shadow-sm"
                            onclick="openRejectModalWithConfirm(${info.event.id})">
                            <i class="bi bi-x-circle me-1"></i> Reject Appointment
                        </button>`;
                } else if (statusStr === 'approved' || statusStr === 'rescheduled') {
                    actionsHtml += `
                        <form action="/admin/appointments/${info.event.id}/done" method="POST" class="d-grid"
                            onsubmit="return confirm('Mark this appointment as Done?');">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="btn btn-success rounded-pill fw-bold text-uppercase py-3 shadow-sm">
                                <i class="bi bi-check2-square me-1"></i> Mark as Done
                            </button>
                        </form>`;
                } else {
                    actionsHtml += `
                        <div class="alert alert-secondary text-center small border-0 w-100 rounded-4 mb-0">
                            This appointment is closed and no further actions can be taken.
                        </div>`;
                }

                eventActionsContainer.innerHTML = actionsHtml;
                eventModal.show();
            }
            });

            calendar.render();
        });
    </script>
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var myModal = new bootstrap.Modal(document.getElementById('addAppointmentModal'));
                myModal.show();
            });
        </script>
    @endif
@endpush
