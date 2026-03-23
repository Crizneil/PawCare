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
                                $vax = $pet->latestVaccination;
                                // Get the single most recent appointment
                                $latestApt = $pet->appointments->sortByDesc('appointment_date')->first();
                                $aptStatus = strtolower($latestApt->status ?? '');

                                // SECURITY CHECK: Is the appointment today?
                                $isAppointedToday = $latestApt && \Carbon\Carbon::parse($latestApt->appointment_date)->isToday();

                                // Only allow logging if the pet is "Checked-In" or "Approved" TODAY
                                $canLogShot = $isAppointedToday && in_array($aptStatus, ['checked-in', 'approved']);
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
                                    @if($canLogShot)
                                        <button class="btn btn-sm btn-dark rounded-pill px-3 shadow-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#updateVax{{ $pet->id }}">
                                            <i data-lucide="plus-circle" class="me-1" style="width:14px;"></i> Log Shot
                                        </button>
                                    @else
                                        <span class="badge bg-light text-muted border">Completed</span>
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
    const vaxContainers = document.querySelectorAll('.modal-content');

    vaxContainers.forEach(container => {
        const nameInput = container.querySelector('.vax-name-input');
        const dateAdminInput = container.querySelector('.vax-date-input');
        const dueDateInput = container.querySelector('.vax-due-input');

        if (nameInput && dateAdminInput && dueDateInput) {
            const calculateDueDate = () => {
                const vaxName = nameInput.value.toLowerCase();
                const adminDateValue = dateAdminInput.value;

                if (!adminDateValue) return;

                const adminDate = new Date(adminDateValue);
                if (isNaN(adminDate.getTime())) return;

                const targetVaccines = ['anti rabies', '5 in 1', '4 in 1', '5-in-1', '4-in-1', 'anti-rabies'];

                if (targetVaccines.some(v => vaxName.includes(v))) {
                    const nextYear = new Date(adminDate);
                    nextYear.setFullYear(nextYear.getFullYear() + 1);

                    // Correcting for timezone offset to ensure the date doesn't jump back a day
                    const year = nextYear.getFullYear();
                    const month = String(nextYear.getMonth() + 1).padStart(2, '0');
                    const day = String(nextYear.getDate()).padStart(2, '0');

                    dueDateInput.value = `${year}-${month}-${day}`;
                }else {
                    // Clear the due date if the vaccine doesn't require a 1-year follow-up
                    dueDateInput.value = '';
                }
            };

            nameInput.addEventListener('input', calculateDueDate);
            // Change is better for date inputs to ensure the user has finished picking
            dateAdminInput.addEventListener('change', calculateDueDate);
        }
    });
});
</script>
@endpush
