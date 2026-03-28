@extends('layout.admin')

@section('content')
<div class="container-fluid p-4 fade-in">
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Staff Dashboard</h2>
            <p class="text-muted">Welcome back! Here's what's happening today.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <button type="button" class="btn btn-orange rounded-pill px-4 fw-semibold shadow-sm text-white" style="height: 38px;" data-bs-toggle="modal" data-bs-target="#addPetModal">
                <i data-lucide="plus-circle" class="me-2" style="width: 16px;"></i> Enroll Pet
            </button>
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                <i data-lucide="calendar" class="me-1" style="width: 14px;"></i> {{ now()->format('M d, Y') }}
            </span>
        </div>
    </div>

    {{-- Skeleton Loader (Visible initially) --}}
    <div id="skeleton-loader">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="d-flex align-items-center">
                        <div class="skeleton-circle me-3"></div>
                        <div class="flex-grow-1">
                            <div class="skeleton skeleton-title"></div>
                            <div class="skeleton skeleton-text"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="d-flex align-items-center">
                        <div class="skeleton-circle me-3"></div>
                        <div class="flex-grow-1">
                            <div class="skeleton skeleton-title"></div>
                            <div class="skeleton skeleton-text"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="d-flex align-items-center">
                        <div class="skeleton-circle me-3"></div>
                        <div class="flex-grow-1">
                            <div class="skeleton skeleton-title"></div>
                            <div class="skeleton skeleton-text"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <div class="skeleton skeleton-title mb-4"></div>
                    <div class="skeleton" style="height: 200px;"></div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <div class="skeleton skeleton-title mb-4"></div>
                    <div class="skeleton skeleton-text mb-2"></div>
                    <div class="skeleton skeleton-text mb-2"></div>
                    <div class="skeleton skeleton-text"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Actual Content (Hidden initially) --}}
    <div id="actual-content" style="display: none;">
        {{-- 1. Top Row Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary-light p-3 rounded-3 text-primary me-3">
                            <i data-lucide="calendar-check"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0">{{ $appointmentsToday->count() }}</h4>
                            <small class="text-muted">Today's Appointments</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning-light p-3 rounded-3 text-warning me-3">
                            <i data-lucide="alert-triangle"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0">{{ $dueForVaccination->count() }}</h4>
                            <small class="text-muted">Pets Due for Vax</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                    <div class="d-flex align-items-center">
                        <div class="bg-success-light p-3 rounded-3 text-success me-3">
                            <i data-lucide="check-circle"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0">{{ $recentVaccinations->count() }}</h4>
                            <small class="text-muted">Recently Vaccinated</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <div class="row">
        {{-- Left Column: Scanner & Inventory --}}
        <div class="col-lg-5">
            {{-- Scanner Section --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-dark text-white p-3 d-flex justify-content-between align-items-center border-0">
                    <h5 class="mb-0 small fw-bold text-white"><i data-lucide="scan" class="me-2"></i> Quick Patient Scan</h5>
                    <span class="badge bg-warning text-dark">HARDWARE COMPATIBLE</span>
                </div>
                <div class="card-body p-4 text-center">

                    {{-- HARDWARE SCANNER INPUT (Hidden until focused or always active) --}}
                    <form action="{{ route('staff.pet-records') }}" method="GET" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="search" id="hardwareScanInput"
                                class="form-control border-0 bg-light"
                                placeholder="Scan QR with Hardware or Enter ID..."
                                autofocus autocomplete="off">
                            <button class="btn btn-warning px-3 fw-bold" type="submit">SEARCH</button>
                        </div>
                        <small class="text-muted mt-2 d-block">Scanner hardware will automatically redirect upon scan.</small>
                    </form>

                    <div class="hr-text text-muted mb-3">OR USE CAMERA</div>

                    {{-- CAMERA SCANNER SECTION --}}
                    <div id="reader" style="width: 100%; display:none; margin-bottom: 20px; border-radius: 15px; overflow:hidden;"></div>
                    <div id="scan-placeholder" class="py-2">
                        <button id="startScanBtn" class="btn btn-outline-dark rounded-pill fw-bold px-4 btn-sm">
                            <i data-lucide="camera" class="me-2"></i> START CAMERA SCANNER
                        </button>
                    </div>
                    <button id="stopScanBtn" class="btn btn-secondary rounded-pill fw-bold px-4 shadow-sm" style="display:none;">STOP CAMERA</button>
                </div>
            </div>

        </div>

        {{-- Right Column: Appointments & Recent Activity --}}
        <div class="col-lg-7">
            {{-- Today's Appointments --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Today's Schedule</h6>
                        <a href="{{ route('staff.appointments') }}" class="small text-decoration-none">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle small">
                            <thead class="bg-light">
                                <tr>
                                    <th>Pet / Owner</th>
                                    <th>Service</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($appointmentsToday as $apt)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $apt->pet_name }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $apt->user->name ?? 'Guest' }}</div>
                                    </td>
                                    <td><span class="badge bg-blue-light text-primary">{{ $apt->service_type }}</span></td>
                                    <td>{{ date('h:i A', strtotime($apt->appointment_time)) }}</td>
                                    <td class="text-end">
                                        <form action="{{ route('staff.appointments.update', $apt->id) }}" method="POST">
                                            @csrf
                                            {{-- This ensures $request->status is 'completed' --}}
                                            <input type="hidden" name="status" value="checked-in">
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                                Check-in
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted">No appointments for today.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Recently Vaccinated --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Recent Activity</h6>
                    @foreach($recentVaccinations as $vax)
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light rounded-circle p-2 me-3">
                            <i data-lucide="syringe" class="text-success" style="width: 16px;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="mb-0 small fw-bold text-dark">{{ $vax->pet->name }} received {{ $vax->vaccine_name }}</p>
                            <small class="text-muted">{{ $vax->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
        </div>
    </div>
</div>
</div>

{{-- Modals --}}
@include('partials._add_pet_modal')

@endsection

@push('scripts')
{{-- QR Scanner Script --}}
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    const html5QrCode = new Html5Qrcode("reader");
    const qrConfig = { fps: 10, qrbox: { width: 250, height: 250 } };
    const startBtn = document.getElementById('startScanBtn');
    const stopBtn = document.getElementById('stopScanBtn');
    const reader = document.getElementById('reader');
    const placeholder = document.getElementById('scan-placeholder');

    if(startBtn) {
        startBtn.addEventListener('click', () => {
            reader.style.display = 'block';
            placeholder.style.display = 'none';
            startBtn.style.display = 'none';
            stopBtn.style.display = 'block';
            html5QrCode.start({ facingMode: "environment" }, qrConfig, onScanSuccess);
        });
    }

    if(stopBtn) {
        stopBtn.addEventListener('click', () => {
            html5QrCode.stop().then(() => {
                reader.style.display = 'none';
                placeholder.style.display = 'block';
                startBtn.style.display = 'block';
                stopBtn.style.display = 'none';
            });
        });
    }

   function onScanSuccess(decodedText) {
    html5QrCode.stop().then(() => {
        console.log("Scanned Content: " + decodedText);

        let petId = '';

        // Improved logic to handle URLs, walk-in paths, or raw IDs
        if (decodedText.includes('/verify-pet/')) {
            petId = decodedText.split('/verify-pet/').pop();
        } else if (decodedText.includes('WALK-')) {
            // Specifically catches walk-in IDs if they are raw or in a different URL
            petId = decodedText.substring(decodedText.indexOf('WALK-'));
        } else {
            // Fallback for raw PC- IDs or numeric IDs
            petId = decodedText;
        }

        // Clean up any remaining URL parameters if they exist
        petId = petId.split('?')[0];

        // Redirect to Pet Records
        window.location.href = "{{ route('staff.pet-records') }}?search=" + encodeURIComponent(petId);
    }).catch((err) => {
        console.error("Failed to stop scanner", err);
    });
}
const hardwareInput = document.getElementById('hardwareScanInput');

    // 1. Keep focus on the input so the hardware scanner always has a target
    document.addEventListener('click', () => {
        // Delay slightly so it doesn't interrupt specific clicks on other inputs
        setTimeout(() => {
            if (document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                hardwareInput.focus();
            }
        }, 1000);
    });

    // 2. Auto-submit logic for the hardware scanner
    hardwareInput.addEventListener('input', function () {
        const val = this.value.trim();

        // Detect if it's a URL or a specific ID format
        const isUrl = val.includes('http');
        const isWalkIn = val.startsWith('WALK-');
        const isRegistered = val.startsWith('PC-');

        if (isUrl || isWalkIn || isRegistered) {
            this.classList.add('is-valid');
            // Small delay to ensure the scanner finished typing the full string
            setTimeout(() => {
                this.form.submit();
            }, 300);
        }
    });

    // Handle Skeleton Loader Toggle
    $(document).ready(function() {
        setTimeout(function() {
            $('#skeleton-loader').fadeOut(300, function() {
                $('#actual-content').fadeIn(400);
            });
        }, 1200);
    });
</script>
@endpush
