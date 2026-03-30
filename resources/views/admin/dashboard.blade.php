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

        {{-- Skeleton Loader (Visible initially) --}}
        <div id="skeleton-loader">
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <div class="skeleton skeleton-title mb-4"></div>
                        <div class="skeleton" style="height: 300px;"></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <div class="skeleton skeleton-title mb-4"></div>
                        <div class="skeleton-circle mx-auto mb-4" style="width: 200px; height: 200px;"></div>
                        <div class="skeleton skeleton-text mt-4"></div>
                        <div class="skeleton skeleton-text"></div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4">
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
            <div class="row g-4 mb-4">
                {{-- Charts Row --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Vaccination Status Distribution</h5>
                            <i data-lucide="bar-chart-3" class="text-muted"></i>
                        </div>
                        <canvas id="vaxChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <h5 class="fw-bold mb-4">Patient Demographics</h5>
                        <canvas id="speciesChart" style="max-height: 250px;"></canvas>
                        <div class="mt-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Dogs</span>
                                <span class="fw-bold small">{{ $speciesStats['dogs'] ?? 0 }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Cats</span>
                                <span class="fw-bold small">{{ $speciesStats['cats'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notifications/Upcoming Row --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">
                                <i data-lucide="bell" class="me-2 text-primary"></i> Upcoming Vaccinations
                            </h5>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3">NEXT 14 DAYS</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle border-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 small fw-bold text-muted ps-3">PET</th>
                                        <th class="border-0 small fw-bold text-muted">OWNER</th>
                                        <th class="border-0 small fw-bold text-muted">LAST VACCINE</th>
                                        <th class="border-0 small fw-bold text-muted text-center">DUE DATE</th>
                                        <th class="border-0 small fw-bold text-muted text-end pe-3">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($upcomingVaccinations ?? [] as $vax)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light rounded-circle p-2 me-3">
                                                        <i data-lucide="{{ strtolower($vax->pet->species) === 'dog' ? 'dog' : 'cat' }}" class="text-primary size-20"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark">{{ $vax->pet->name }}</div>
                                                        <div class="text-muted small">{{ $vax->pet->pet_id }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small fw-semibold text-dark">{{ $vax->pet->user->name ?? 'N/A' }}</div>
                                                <div class="text-muted" style="font-size: 11px;">{{ $vax->pet->user->phone ?? 'N/A' }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary-subtle text-secondary small">{{ $vax->vaccine_name }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="fw-bold {{ \Carbon\Carbon::parse($vax->next_due_date)->isPast() ? 'text-danger' : 'text-primary' }}">
                                                    {{ \Carbon\Carbon::parse($vax->next_due_date)->format('M d, Y') }}
                                                </div>
                                                <div class="text-muted" style="font-size: 10px;">
                                                    {{ \Carbon\Carbon::parse($vax->next_due_date)->diffForHumans() }}
                                                </div>
                                            </td>
                                            <td class="text-end pe-3">
                                                <a href="{{ route('admin.pet-records', ['general_search' => $vax->pet->pet_id]) }}"
                                                   class="btn btn-sm btn-outline-primary rounded-pill px-3">View Record</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No upcoming vaccinations identified in the next 14 days.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

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
        </div>

                <div class="alert alert-info border-0 shadow-sm rounded-lg d-flex align-items-center mt-4">
                    <i data-lucide="info" class="me-3"></i>
                    <div>
                        <h6 class="fw-bold mb-0">System Status</h6>
                        <small>All systems operational. {{ $totalOwners ?? 0 }} registered owners in database.</small>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // --- Chart Data from Backend ---
    const vaxData = @json($vaccinationStats ?? []);
    const speciesData = @json($speciesStats ?? []);

    $(document).ready(function() {
        // Handle Skeleton Loader Toggle
        setTimeout(() => {
            $('#skeleton-loader').fadeOut(300, function() {
                $('#actual-content').fadeIn(400);
            });
        }, 800);

        // 1. Vaccination Status Chart
        const vaxCtx = document.getElementById('vaxChart');
        if (vaxCtx) {
            // Logic to sum up vaccinated numbers if the backend still sends them separately
            // Or just use vaxData.vaccinated if you updated the Controller
            const totalVaccinated = (vaxData.vaccinated)
                ? vaxData.vaccinated
                : (vaxData.fully_vaccinated || 0) + (vaxData.partially_vaccinated || 0);

            new Chart(vaxCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    // Updated Labels: Removed Fully/Partially
                    labels: ['Vaccinated', 'Due Soon', 'Overdue', 'Unvaccinated'],
                    datasets: [{
                        label: 'Status Count',
                        data: [
                            totalVaccinated,
                            vaxData.due_soon || 0,
                            vaxData.overdue || 0,
                            vaxData.unvaccinated || 0
                        ],
                        backgroundColor: [
                            'rgba(25, 135, 84, 0.7)', // Success (Green)
                            'rgba(255, 193, 7, 0.7)',  // Warning (Yellow)
                            'rgba(220, 53, 69, 0.7)',  // Danger (Red)
                            'rgba(108, 117, 125, 0.7)' // Muted (Gray)
                        ],
                        borderWidth: 0,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { display: false },
                            ticks: { stepSize: 1 }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // 2. Species Pie Chart
        const speciesCtx = document.getElementById('speciesChart');
        if (speciesCtx) {
            new Chart(speciesCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Dogs', 'Cats'],
                    datasets: [{
                        data: [speciesData.dogs || 0, speciesData.cats || 0],
                        backgroundColor: ['#d35400', '#f1c40f'],
                        hoverOffset: 4,
                        borderWidth: 5,
                        borderColor: 'transparent'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    cutout: '70%'
                }
            });
        }

        const scanInput = document.getElementById('scanInput');
        if (scanInput) {
            // 1. Maintain focus more aggressively
            const keepFocus = () => {
                if (document.activeElement !== scanInput) {
                    scanInput.focus();
                }
            };
            scanInput.focus();
            document.addEventListener('click', () => setTimeout(keepFocus, 500));

            // 2. Enhanced Detection Logic
            scanInput.addEventListener('input', function () {
                const val = this.value.trim();
                if (val.includes('http') || val.startsWith('WALK-') || val.startsWith('PC-')) {
                    this.classList.add('is-valid');
                    setTimeout(() => this.form.submit(), 300);
                }
            });

            scanInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') this.form.submit();
            });
        }
    });
</script>
@endpush
