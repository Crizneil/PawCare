@extends('layout.admin')

@section('page_title', 'Vaccination Status | Staff')

@section('content')
<div class="container-fluid p-4 fade-in">

    {{-- Header & Filters --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1">Vaccination Tracker</h2>
            <p class="text-muted small mb-0">Monitor immunization schedules and log new vaccinations.</p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('staff.vaccination-status', request('today') ? [] : ['today' => 1]) }}"
               class="btn {{ request('today') ? 'btn-success' : 'btn-outline-success' }} rounded-pill px-4 shadow-sm">
                <i data-lucide="calendar-check" class="me-2" style="width:16px;"></i>
                {{ request('today') ? 'Showing Today' : 'Vaccinated Today' }}
            </a>

            <form action="{{ route('staff.vaccination-status') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-control rounded-pill border-0 shadow-sm px-4"
                       placeholder="Search Pet or Owner...">
                <button class="btn btn-orange rounded-pill px-4 shadow-sm">
                    <i data-lucide="search" style="width:18px;"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                {{-- Added 'custom-mobile-table' class to trigger your CSS --}}
                <table class="table table-hover align-middle mb-0 custom-mobile-table">
                    <thead class="bg-light text-secondary small fw-bold text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">Pet Patient</th>
                            <th>Owner</th>
                            <th>Vaccine Type</th>
                            <th>Administered By</th>
                            <th>Date Given</th>
                            <th>Next Due</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pets as $pet)
                            @php
                                $targetAptId = request('appointment_id');
                                $vax = $pet->latestVaccination;

                                $latestApt = $pet->appointments
                                    ->where('appointment_date', date('Y-m-d'))
                                    ->whereIn('status', ['approved', 'checked-in', 'completed', 'done'])
                                    ->first();

                                $aptStatus = strtolower($latestApt->status ?? '');
                                $serviceType = strtolower(trim($latestApt->service_type ?? '')); // Normalized for easier checking
                                $isAppointedToday = (bool)$latestApt;

                                $simpleProcedures = ['kapon', 'check-up', 'consultation'];
                                $isSimpleProcedure = in_array($serviceType, $simpleProcedures);

                                // FIX: Comprehensive list of strings that count as a "Vaccination" service
                                $vaxKeywords = [
                                    'anti-rabies', 'rabies', 'deworming', '5-in-1', '5in1',
                                    '4-in-1', '4in1', 'vaccination', 'vax', 'shot'
                                ];

                                $isVaccination = false;
                                foreach ($vaxKeywords as $keyword) {
                                    if (stripos($serviceType, $keyword) !== false) {
                                        $isVaccination = true;
                                        break;
                                    }
                                }

                                $canLogToday = true;
                                $waitReason = "";

                                if ($vax) {
                                    $lastDate = \Carbon\Carbon::parse($vax->date_administered);
                                    $today = \Carbon\Carbon::today();
                                    $daysSince = $lastDate->diffInDays($today);

                                    $lastVaxName = strtolower(trim($vax->vaccine_name));
                                    $currentService = strtolower(trim($serviceType));

                                    // Logic A: Same vaccine check (Don't allow if before due date)
                                    if (stripos($lastVaxName, $currentService) !== false || stripos($currentService, $lastVaxName) !== false) {
                                        if ($vax->next_due_date) {
                                            $dueDate = \Carbon\Carbon::parse($vax->next_due_date);
                                            if ($today->lt($dueDate)) {
                                                $canLogToday = false;
                                                $waitReason = "Due on " . $dueDate->format('M d');
                                            }
                                        }
                                    }
                                    // Logic B: Different vaccine 15-day safety check
                                    elseif ($daysSince < 15) {
                                        $canLogToday = false;
                                        $waitReason = (15 - $daysSince) . " days wait";
                                    }
                                }

                                // Determine button visibility
                                // Added check to ensure it shows if status is 'approved' or 'checked-in'
                                $showLogButton = $isAppointedToday && $isVaccination && $canLogToday && !in_array($aptStatus, ['completed', 'done']);
                            @endphp

                            {{-- Only show the row if the pet has an appointment today --}}
                            @if($isAppointedToday)
                            <tr>
                                <td class="ps-4" data-label="Pet">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-blue-light rounded-circle p-2 me-3 text-primary text-center d-none d-md-block" style="width:40px; height:40px;">
                                            <i data-lucide="dog" style="width:20px;"></i>
                                        </div>
                                        <div class="text-end-mobile">
                                            <div class="fw-bold text-dark">{{ $pet->name }}</div>
                                            @if($aptStatus == 'checked-in')
                                                <span class="badge bg-soft-warning text-warning" style="font-size: 0.65rem;">READY FOR SHOT</span>
                                            @elseif(in_array($aptStatus, ['done', 'completed']))
                                                <span class="badge bg-soft-success text-success" style="font-size: 0.65rem;">TREATMENT COMPLETED</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td data-label="Owner">
                                    @if($pet->user)
                                        <div class="fw-medium text-dark">{{ $pet->user->name }}</div>
                                        <small class="text-muted">{{ $pet->user->phone }}</small>
                                    @else
                                        <div class="fw-medium text-dark">{{ $pet->owner }}</div>
                                        <span class="badge bg-secondary-subtle text-secondary border small" style="font-size: 0.7rem;">Walk-in Guest</span>
                                    @endif
                                </td>

                                <td data-label="Vaccine">
                                    @if($vax)
                                        <span class="badge bg-info-subtle text-info border border-info px-3">
                                            {{ $vax->vaccine_name }}
                                        </span>
                                    @else
                                        <span class="text-muted small italic">No History</span>
                                    @endif
                                </td>

                                <td data-label="Admin By">
                                    <div class="small">
                                        {{ $vax->staff->name ?? 'System' }}
                                    </div>
                                </td>

                                <td class="small" data-label="Date Given">
                                    {{ $vax ? \Carbon\Carbon::parse($vax->date_administered)->format('M d, Y') : '--' }}
                                </td>

                                <td data-label="Next Due">
                                    @if($vax && $vax->next_due_date)
                                        @php
                                            $dueDate = \Carbon\Carbon::parse($vax->next_due_date);
                                            $isOverdue = $dueDate->isPast() && !$dueDate->isToday();
                                        @endphp
                                        <div class="small fw-bold {{ $isOverdue ? 'text-danger' : 'text-success' }}">
                                            {{ $dueDate->format('M d, Y') }}
                                        </div>
                                    @else
                                        <span class="text-muted small">--</span>
                                    @endif
                                </td>

                                <td class="text-end pe-4" data-label="Actions">
                                    @if($showLogButton)
                                        <button class="btn btn-sm btn-dark rounded-pill px-3 shadow-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#updateVax{{ $pet->id }}">
                                            <i data-lucide="plus-circle" class="me-1" style="width:14px;"></i> Log Shot
                                        </button>
                                    @elseif($isVaccination && !$canLogToday)
                                        <span class="badge bg-soft-secondary text-muted border">
                                            <i data-lucide="clock" class="me-1" style="width:12px;"></i> {{ $waitReason }}
                                        </span>
                                    @elseif($isSimpleProcedure && !in_array($aptStatus, ['completed', 'done']))
                                        <form action="{{ route('staff.appointments.update', $latestApt->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"
                                                    onclick="return confirm('Complete this procedure?')">
                                                <i data-lucide="check-circle" class="me-1" style="width:14px;"></i> Mark as Done
                                            </button>
                                        </form>
                                    @elseif(in_array($aptStatus, ['completed', 'done']))
                                        <span class="badge bg-soft-success text-success border border-success">
                                            <i data-lucide="check" class="me-1" style="width:12px;"></i> Logged
                                        </span>
                                    @else
                                        <span class="text-muted small">--</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i data-lucide="calendar-x" class="mb-3" style="width:48px; height:48px; opacity: 0.5;"></i>
                                        <h5 class="fw-bold">No Appointments Today</h5>
                                        <p class="small">Only pets scheduled for today's date will appear in this tracker.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $pets->appends(request()->query())->links() }}
    </div>
</div>

@foreach($pets as $pet)
    @include('partials._vax_modal')
@endforeach
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const vaxContainers = document.querySelectorAll('.modal');

    vaxContainers.forEach(modal => {
        const nameInput = modal.querySelector('.vax-name-input');
        const dateAdminInput = modal.querySelector('.vax-date-input');
        const dueDateInput = modal.querySelector('.vax-due-input');

        const calculateDueDate = () => {
            if (!nameInput.value || !dateAdminInput.value) return;

            const vaxName = nameInput.value.toLowerCase();
            const adminDate = new Date(dateAdminInput.value);
            let nextDate = new Date(adminDate);

            // 1. Core Vaccines (21 days for boosters as requested)
            if (vaxName.includes('5-in-1') || vaxName.includes('4-in-1') || vaxName.includes('5in1') || vaxName.includes('4in1')) {
                nextDate.setDate(adminDate.getDate() + 21);
            }
            // 2. Deworming (3 Months)
            else if (vaxName.includes('deworming')) {
                nextDate.setMonth(adminDate.getMonth() + 3);
            }
            // 3. Anti-Rabies (1 Year)
            else if (vaxName.includes('rabies')) {
                nextDate.setFullYear(adminDate.getFullYear() + 1);
            } else {
                return; // Don't overwrite manual entry for unknown types
            }

            const year = nextDate.getFullYear();
            const month = String(nextDate.getMonth() + 1).padStart(2, '0');
            const day = String(nextDate.getDate()).padStart(2, '0');
            dueDateInput.value = `${year}-${month}-${day}`;
        };

        // Listen for changes
        nameInput.addEventListener('input', calculateDueDate);
        dateAdminInput.addEventListener('change', calculateDueDate);

        // Auto-calculate when modal is shown (since we pre-fill the vaccine name)
        modal.addEventListener('shown.bs.modal', calculateDueDate);
    });
});
</script>
@endpush
