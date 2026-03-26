@extends('layout.admin')

@section('content')
    <div class="container-fluid p-4 fade-in">
        {{-- Header - Updated with Report Dropdown --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-0">Appointments</h2>
                <p class="text-muted mb-0 small">Manage walk-ins and scheduled vaccinations.</p>
            </div>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-dark rounded-pill px-4 py-2 shadow-sm fw-bold dropdown-toggle" type="button" id="reportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Generate Report
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        {{-- Updated to use the new master route: staff.generate-report --}}
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item fw-bold" href="{{ route('staff.generate-report', ['type' => 'appointments', 'filter' => 'summary']) }}" target="_blank">
                                Daily Summary
                            </a>
                        </li>
                    </ul>
                </div>
                <button class="btn btn-orange rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#walkInModal">
                    <i data-lucide="plus" class="me-2" style="width: 18px;"></i> New Walk-in
                </button>
            </div>
        </div>

        {{-- Sub-menu Navigation - Modern Segmented Control --}}
        <div class="d-flex justify-content-center mb-4">
            <div class="bg-light rounded-pill p-1 shadow-sm d-inline-flex flex-wrap" style="border: 1px solid #e9ecef;">
                <a class="btn rounded-pill px-4 fw-bold {{ $view == 'today' ? 'btn-primary text-white shadow-sm' : 'btn-light text-muted border-0' }}"
                    href="{{ route('staff.appointments', ['view' => 'today']) }}" style="transition: all 0.2s;">
                    <i data-lucide="calendar" class="me-1 d-none d-sm-inline-block" style="width: 16px;"></i> Today
                </a>
                <a class="btn rounded-pill px-4 fw-bold {{ $view == 'upcoming' ? 'btn-primary text-white shadow-sm' : 'btn-light text-muted border-0' }}"
                    href="{{ route('staff.appointments', ['view' => 'upcoming']) }}" style="transition: all 0.2s;">
                    <i data-lucide="clock" class="me-1 d-none d-sm-inline-block" style="width: 16px;"></i> Upcoming
                </a>
                <a class="btn rounded-pill px-4 fw-bold {{ $view == 'completed' ? 'btn-primary text-white shadow-sm' : 'btn-light text-muted border-0' }}"
                    href="{{ route('staff.appointments', ['view' => 'completed']) }}" style="transition: all 0.2s;">
                    <i data-lucide="check-circle" class="me-1 d-none d-sm-inline-block" style="width: 16px;"></i> Completed
                </a>
                <a class="btn rounded-pill px-4 fw-bold {{ $view == 'missed' ? 'btn-danger text-white shadow-sm' : 'btn-light text-muted border-0' }}"
                    href="{{ route('staff.appointments', ['view' => 'missed']) }}">
                    <i data-lucide="alert-circle" class="me-1 d-none d-sm-inline-block" style="width: 16px;"></i> Missed
                </a>
            </div>
        </div>

        {{-- Appointment List --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                {{-- Added custom-mobile-table --}}
                <table class="table table-hover align-middle mb-0 custom-mobile-table">
                    <thead class="bg-light text-secondary text-uppercase small fw-bold">
                        <tr>
                            <th class="ps-4">Pet Details</th>
                            <th>Owner</th>
                            <th>Service</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $apt)
                            @php
                                $isCompleted = in_array(strtolower($apt->status), ['done', 'completed']);
                                $isApproved = in_array(strtolower($apt->status), ['approved', 'rescheduled', 'pending']);
                            @endphp
                            <tr>
                                <td class="ps-4" data-label="Pet Details">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0"
                                            style="width: 45px; height: 45px;">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($apt->pet_name) }}&background=fdfbf7&color=d35400&rounded=true"
                                                alt="Pet" class="w-100 h-100 object-fit-cover">
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $apt->pet_name }}</div>
                                            <small class="text-muted text-uppercase">{{ $apt->species }}</small>
                                        </div>
                                    </div>
                                </td>

                                <td data-label="Owner">
                                    <div class="fw-bold text-dark">
                                        {{-- Show the name from the Pet record (which stores the Walk-in name)
                                            or fallback to the User account name --}}
                                        {{ $apt->pet->owner ?? ($apt->user->name ?? 'Unknown') }}
                                    </div>

                                    @if($apt->user_id)
                                        <small class="text-muted"><i data-lucide="user-check" style="width: 14px;"></i> Member</small>
                                    @else
                                        <small class="text-muted"><i data-lucide="user" style="width: 14px;"></i> Guest</small>
                                    @endif
                                </td>

                                <td data-label="Service">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 rounded-pill"
                                        style="letter-spacing: 0.3px; font-size: 0.75rem; font-weight: 600;">
                                        {{-- ucfirst() ensures only the first letter of the string is uppercase --}}
                                        {{ ucfirst(str_replace(['5in1', '4in1'], ['5-in-1', '4-in-1'], $apt->service_type)) }}
                                    </span>
                                </td>

                                <td data-label="Schedule">
                                    <div class="small fw-bold text-dark">{{ date('M d, Y', strtotime($apt->appointment_date)) }}
                                    </div>
                                    <div class="text-muted small">{{ date('h:i A', strtotime($apt->appointment_time)) }}</div>
                                </td>

                                <td data-label="Status">
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-warning-subtle text-warning border-warning',
                                            'approved' => 'bg-info-subtle text-info border-info',
                                            'rescheduled' => 'bg-info-subtle text-info border-info',
                                            'checked-in' => 'bg-success-subtle text-success border-success',
                                            'late' => 'bg-secondary-subtle text-secondary border-secondary',
                                            'missed' => 'bg-danger-subtle text-danger border-danger',
                                            'completed' => 'bg-success-subtle text-success border-success',
                                            'done' => 'bg-success-subtle text-success border-success',
                                        ];
                                        $currentStatus = strtolower($apt->status);
                                        $class = $statusClasses[$currentStatus] ?? 'bg-light text-dark';

                                        // Force display text to "Completed" if status is "done"
                                        $displayText = ($currentStatus === 'done') ? 'Completed' : ucwords(str_replace('-', ' ', $apt->status));
                                    @endphp
                                    <span class="badge rounded-pill px-3 border {{ $class }}">
                                        {{ $displayText }}
                                    </span>
                                </td>

                                {{-- Actions Column --}}
                                <td class="text-end pe-4" data-label="Actions">
                                    @if($view === 'upcoming')
                                        <span class="text-muted small fst-italic">No Action Available</span>
                                    @else
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border rounded-pill px-3 shadow-sm" type="button" data-bs-toggle="dropdown">
                                                Manage <i data-lucide="more-vertical" class="ms-1" style="width: 14px;"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3">

                                                {{-- STAGE 0: Pending Status (Restricted Actions) --}}
                                                @if($currentStatus === 'pending')
                                                    <li>
                                                        <span class="dropdown-item py-2 text-muted small fst-italic">
                                                            <i data-lucide="clock" class="me-2" style="width: 16px;"></i> Awaiting Approval
                                                        </span>
                                                    </li>
                                                    {{-- Optional: Add a link here to the approval page if you have one --}}
                                                @endif

                                                {{-- STAGE 1: Approved/Rescheduled -> Check-In or Late --}}
                                                @if(in_array($currentStatus, ['approved', 'rescheduled']))
                                                    <li>
                                                        <form action="{{ route('staff.appointments.update', $apt->id) }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="status" value="checked-in">
                                                            <button class="dropdown-item py-2 text-primary">
                                                                <i data-lucide="user-check" class="me-2" style="width: 16px;"></i> Check-In Patient
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('staff.appointments.update', $apt->id) }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="status" value="late">
                                                            <button class="dropdown-item py-2 text-muted">
                                                                <i data-lucide="clock-alert" class="me-2" style="width: 16px;"></i> Mark as Late
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif

                                                {{-- STAGE 2: Late -> Can still Check-In or Reschedule --}}
                                                @if($currentStatus == 'late')
                                                    <li>
                                                        <form action="{{ route('staff.appointments.update', $apt->id) }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="status" value="checked-in">
                                                            <button class="dropdown-item py-2 text-primary">
                                                                <i data-lucide="user-check" class="me-2" style="width: 16px;"></i> Late Arrival (Check-In)
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item py-2" data-bs-toggle="modal" data-bs-target="#rescheduleModal{{ $apt->id }}">
                                                            <i data-lucide="calendar-days" class="me-2" style="width: 16px;"></i> Reschedule
                                                        </button>
                                                    </li>
                                                @endif

                                                {{-- STAGE 3: Checked-In -> Actionable Services --}}
                                                @if($currentStatus == 'checked-in')
                                                    @if(stripos($apt->service_type, 'vaccination') !== false ||
                                                        stripos($apt->service_type, 'deworming') !== false ||
                                                        stripos($apt->service_type, 'kapon') !== false)
                                                        <li>
                                                            <a class="dropdown-item py-2 text-primary fw-bold"
                                                                href="{{ route('staff.vaccination-status', ['search' => $apt->pet_name, 'appointment_id' => $apt->id]) }}">
                                                                <i data-lucide="syringe" class="me-2" style="width: 16px;"></i> Start Procedure
                                                            </a>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <button type="button" class="dropdown-item py-2 text-success" data-bs-toggle="modal" data-bs-target="#confirmDoneModal{{ $apt->id }}">
                                                            <i data-lucide="check-circle" class="me-2" style="width: 16px;"></i> Mark as Done
                                                        </button>
                                                    </li>
                                                @endif

                                                {{-- Missed & Completed Views remain the same --}}
                                                @if($currentStatus == 'missed')
                                                    <li>
                                                        <button class="dropdown-item py-2" data-bs-toggle="modal" data-bs-target="#rescheduleModal{{ $apt->id }}">
                                                            <i data-lucide="calendar-days" class="me-2" style="width: 16px;"></i> Reschedule Missed Visit
                                                        </button>
                                                    </li>
                                                @endif

                                                @if(in_array($currentStatus, ['done', 'completed']))
                                                    <li>
                                                        <button class="dropdown-item py-2" data-bs-toggle="modal" data-bs-target="#viewResultModal{{ $apt->id }}">
                                                            <i data-lucide="eye" class="me-2 text-info" style="width: 16px;"></i> View Results
                                                        </button>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No records found for this view.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($appointments->hasPages())
                <div class="card-footer bg-white border-0 p-3">
                    {{ $appointments->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modals Loop --}}
    @foreach($appointments as $apt)
        @php
            $currentStatus = strtolower($apt->status);
            $isCompleted = in_array($currentStatus, ['done', 'completed']);
            $isActionable = in_array($currentStatus, ['approved', 'rescheduled', 'pending', 'checked-in']);
            // NEW: Check if the appointment is eligible for rescheduling
            $canReschedule = in_array($currentStatus, ['late', 'missed', 'approved']);
        @endphp

        {{-- Change this condition --}}
        @if($canReschedule)
            @include('partials._reschedule_modal', ['appointment' => $apt])
        @endif

        @if($isCompleted)
            @include('partials._view_appointment_result_modal', ['appointment' => $apt])
        @endif

        @if($isActionable)
            @include('partials._confirm_done_modal', ['appointment' => $apt])
        @endif
    @endforeach
    @include('partials._walk-in_modal')

    @push('scripts')
        <script src="{{ asset('assets/js/pet-registration.js') }}?v={{ time() }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Auto re-open walk-in modal if validation errors exist
                @if ($errors->any())
                    var walkInModal = new bootstrap.Modal(document.getElementById('walkInModal'));
                    walkInModal.show();
                @endif
            });
        </script>
    @endpush
@endsection
