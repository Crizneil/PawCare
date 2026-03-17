@extends('layout.admin')

@section('page_title', 'Overview')

@section('content')
    <div class="container-fluid animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold">Overview Dashboard</h3>
            <div class="d-flex align-items-center">
                <span class="badge bg-white shadow-sm text-dark p-2 rounded-pill me-3">
                    <i data-lucide="calendar" class="size-14 me-1"></i> {{ date('F d, Y') }}
                </span>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-lg p-4 mb-4 bg-dark text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-white">
                            <i data-lucide="scan" class="me-2"></i> Quick Patient Scan
                        </h5>
                        <span class="badge bg-warning text-dark">HARDWARE COMPATIBLE</span>
                    </div>
                    <form action="{{ route('admin.search-pet') }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="search" id="scanInput"
                                class="form-control form-control-lg border-0 bg-light" placeholder="Scan QR or Enter ID..."
                                autofocus autocomplete="off">
                            <button class="btn btn-warning px-4 fw-bold" type="submit">SEARCH</button>
                        </div>
                        <small class="text-white-50 mt-2 d-block">Instant redirection to pet medical records upon
                            scan.</small>
                    </form>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm p-4 text-center h-100">
                            <h6 class="text-muted text-uppercase fw-bold small">Total Patients</h6>
                            <h2 class="display-5 fw-bold text-dark mb-0">{{ $totalPets ?? 0 }}</h2>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm p-4 text-center h-100" style="border-left: 5px solid #d35400;">
                            <h6 class="text-uppercase fw-bold small" style="color: #d35400;">Active Staff Members</h6>
                            <h2 class="display-5 fw-bold mb-0" style="color: #d35400;">{{ $totalStaff ?? 0 }}</h2>
                        </div>
                    </div>
                </div>

                {{-- Low Stock Alert --}}
                @if(isset($lowStockVaccines) && $lowStockVaccines->count() > 0)
                    <div class="alert alert-danger border-0 shadow-sm rounded-lg d-flex align-items-center mb-4">
                        <i data-lucide="alert-triangle" class="me-3 size-24"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Low Inventory Alert</h6>
                            <p class="small mb-0">The following items are running low on stock:
                                <strong>{{ $lowStockVaccines->pluck('name')->implode(', ') }}</strong>.
                                Please restock soon to avoid shortages.
                            </p>
                        </div>
                    </div>
                @endif


                <div class="alert alert-info border-0 shadow-sm rounded-lg d-flex align-items-center">
                    <i data-lucide="info" class="me-3"></i>
                    <div>
                        <h6 class="fw-bold mb-0">System Status</h6>
                        <small>All systems operational. {{ $totalOwners ?? 0 }} registered owners in database.</small>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Logic for Rejection Modals --}}
        @foreach($requests ?? [] as $req)
                    @php /** @var \App\Models\UserRequest $req */ @endphp
            <div class="modal fade" id="rejectModal{{ $req?->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold">Reject Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('admin.requests.update', $req?->id ?? 0) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="rejected">
                            <div class="modal-body">
                                <label class="small fw-bold text-uppercase text-muted">Reason for Rejection</label>
                                <textarea name="remarks" class="form-control bg-light border-0 mt-2" rows="3"
                                    placeholder="Explain why..." required></textarea>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-link text-muted text-decoration-none"
                                    data-bs-dismiss="modal">CANCEL</button>
                                <button type="submit" class="btn btn-danger rounded-pill px-4">CONFIRM REJECTION</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
 <script>
    const scanInput = document.getElementById('scanInput');

    // 1. Maintain focus more aggressively
    const keepFocus = () => {
        if (document.activeElement !== scanInput) {
            scanInput.focus();
        }
    };

    window.addEventListener('load', () => scanInput.focus());
    // Refocus if user clicks anywhere, but don't interrupt typing
    document.addEventListener('click', () => {
        setTimeout(keepFocus, 500);
    });

    // 2. Enhanced Detection Logic
    scanInput.addEventListener('input', function () {
        const val = this.value.trim();

        // Check if it's a full URL OR a specific Pet ID format (WALK- or PC-)
        const isUrl = val.includes('http');
        const isWalkIn = val.startsWith('WALK-');
        const isRegistered = val.startsWith('PC-'); // Assuming your IDs start with PC-

        if (isUrl || isWalkIn || isRegistered) {
            // Visual feedback that something is happening
            scanInput.classList.add('is-valid');

            // Short delay to allow hardware scanner to finish sending characters
            setTimeout(() => {
                this.form.submit();
            }, 300);
        }
    });

    // 3. Handle the "Enter" key (most scanners send 'Enter' at the end)
    scanInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            this.form.submit();
        }
    });
</script>
@endsection
