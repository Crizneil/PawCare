@extends('layout.admin')

@section('content')
<div class="container-fluid p-4">
    <h2 class="fw-bold mb-4">Owner Dashboard</h2>

    {{-- Stats Row --}}
    <div class="row mb-4">
        {{-- Pet Count --}}
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary-subtle p-3 rounded-circle me-3">
                        <i data-lucide="dog" class="text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Pets Registered</h6>
                        <h3 class="fw-bold mb-0">{{ $petCount }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Next Appointment --}}
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-info-subtle p-3 rounded-circle me-3">
                        <i data-lucide="calendar" class="text-info"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Upcoming Appointment</h6>
                        <h5 class="fw-bold text-primary mb-0">{{ $nextAppointment ?? 'No Appointment' }}</h5>
                        <small class="text-success fw-medium">{{ $appointmentStatus ?? '' }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Vaccine Due --}}
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-danger-subtle p-3 rounded-circle me-3">
                        <i data-lucide="syringe" class="text-danger"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">Vaccination Due</h6>
                        <h5 class="fw-bold text-danger mb-0">{{ $nextVaccine ?? 'No Due Vaccine' }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Telegram Join Notification --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background-color: #fdfbf7; border: 1px solid #fce7d6 !important;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center">
                <div class="bg-info-subtle p-3 rounded-circle me-3">
                    <i data-lucide="send" class="text-info"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="fw-bold text-dark mb-1">Get Real-time Notifications!</h5>
                    <p class="text-muted small mb-0">
                        Join our Telegram for instant appointment updates and vaccination reminders.
                    </p>
                    <small class="text-muted" style="font-size: 0.75rem;">
                        <i data-lucide="info" class="me-1 d-inline-block" style="width: 12px; height: 12px; vertical-align: middle;"></i>
                        No Telegram? No problem! You'll still receive all updates directly here in your dashboard.
                    </small>
                </div>
                <div class="ms-3">
                    <a href="https://t.me/PawCareAlerts_bot" target="_blank" class="btn btn-orange rounded-pill px-4 shadow-sm">
                        <i data-lucide="external-link" class="me-1" style="width: 14px; height: 14px;"></i> Connect Now
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Reminders & Announcements --}}
    @if(isset($vaccineReminder))
        <div class="alert alert-warning border-0 shadow-sm rounded-4 d-flex align-items-center mb-4 p-3">
            <i data-lucide="bell-ring" class="me-3 text-warning"></i>
            <div>
                <strong>Vaccination Reminder:</strong> {{ $vaccineReminder }}
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-2">
                <i data-lucide="megaphone" class="me-2 text-primary" style="width: 20px;"></i>
                <h6 class="fw-bold mb-0">Clinic Announcement</h6>
            </div>
            <p class="mb-0 text-secondary">
                {{ $announcement ?? 'Welcome to PawCare! Check back here for clinic updates and holiday schedules.' }}
            </p>
        </div>
    </div>

    {{-- Pet Cards Grid --}}
    <div class="row">
        @forelse($pets as $pet)
            <div class="col-lg-6 mb-4">
                <div class="flip-card" onclick="this.classList.toggle('flipped')">
                    <div class="flip-card-inner">

                        {{-- FRONT OF CARD --}}
                        <div class="flip-card-front p-4 rounded-4 shadow-sm border-0">
                            <div class="d-flex align-items-center mb-4">
                                <div class="me-3">
                                    <img src="{{ $pet->image_url ? '/storage/' . $pet->image_url : 'https://ui-avatars.com/api/?name=' . urlencode($pet->name) . '&background=fdfbf7&color=d35400' }}"
                                         class="rounded-circle border border-4 border-white shadow-sm"
                                         style="width: 85px; height: 85px; object-fit: cover;"
                                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($pet->name) }}&background=fdfbf7&color=d35400'">
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="fw-bold mb-0 text-dark">{{ $pet->name }}</h3>
                                    <span class="badge rounded-pill px-3" style="background-color: #fdfbf7; color: #d35400; border: 1px solid #fce7d6;">{{ $pet->breed ?? 'Unknown Breed' }}</span>
                                    <div class="mt-1">
                                        <small class="text-muted"><i data-lucide="cake" class="me-1" style="width: 12px;"></i> Born: {{ $pet->birthday ?? 'N/A' }}</small>
                                    </div>
                                </div>
                                <div class="ms-auto bg-white p-1 rounded-3 shadow-sm">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ $pet->pet_id }}"
                                         style="width: 65px; height: 65px;">
                                </div>
                            </div>
                            <div class="bg-light-subtle border rounded-3 p-2 text-center">
                                <small class="text-muted fw-bold text-uppercase" style="letter-spacing: 1px; font-size: 0.7rem;">
                                    <i data-lucide="mouse-pointer-2" class="me-1" style="width: 12px;"></i> Tap card for medical details
                                </small>
                            </div>
                        </div>

                        {{-- BACK OF CARD --}}
                        <div class="flip-card-back p-4 rounded-4 shadow-sm border-0 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold text-warning mb-0"><i data-lucide="shield-check" class="me-2"></i>Medical Record</h5>
                                <span class="badge bg-white text-dark rounded-pill shadow-sm">#{{ $pet->pet_id }}</span>
                            </div>

                            <div class="medical-info flex-grow-1">
                                <div class="mb-3 p-3 rounded-4" style="background: #fdfbf7; border: 1px solid #fce7d6;">
                                    <p class="mb-2 fw-bold text-uppercase small text-muted">Last Vaccination</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-dark">{{ $pet->vaccine_name ?? 'No Record' }}</span>
                                        <span class="badge bg-white text-success px-2">{{ $pet->last_date ? \Carbon\Carbon::parse($pet->last_date)->format('M d, Y') : 'N/A' }}</span>
                                    </div>
                                </div>

                                <div class="mb-4 p-3 rounded-4" style="background: #fdfbf7; border: 1px solid #fce7d6;">
                                    <p class="mb-2 fw-bold text-uppercase small text-muted">Next Vaccination Due</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-dark">Scheduled</span>
                                        <span class="badge bg-white text-danger px-2">{{ $pet->next_date ? \Carbon\Carbon::parse($pet->next_date)->format('M d, Y') : 'Not Scheduled' }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions for Consistency --}}
                            <div class="mt-auto">
                                <a href="{{ route('pet-owner.vaccination-history', ['pet_id' => $pet->id]) }}"
                                   class="btn btn-warning w-100 rounded-pill fw-bold py-2 shadow-sm"
                                   onclick="event.stopPropagation();">
                                    <i data-lucide="history" class="me-2" style="width: 16px;"></i> View Vaccination History
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 py-5 text-center">
                <div class="bg-white p-5 rounded-4 shadow-sm d-inline-block">
                    <i data-lucide="dog" class="text-muted mb-3" style="width: 64px; height: 64px;"></i>
                    <h4 class="fw-bold">No Pets Found</h4>
                    <p class="text-muted mb-0">Register your pet with the clinic to see their medical ID card here.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
