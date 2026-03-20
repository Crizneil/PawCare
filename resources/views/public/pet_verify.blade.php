<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Pet - {{ $pet->pet_id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body style="background-color: #fcfaf2;">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                {{-- Verification Header --}}
                <div class="bg-success text-white p-4 text-center">
                    <i data-lucide="shield-check" class="mb-2" style="width: 48px; height: 48px;"></i>
                    <h4 class="fw-bold mb-0">Verified Pet Record</h4>
                    <p class="small mb-0 opacity-75">PawCare Veterinary Clinic Registry</p>
                </div>

                <div class="card-body p-4 text-center">
                    {{-- Pet Image --}}
                    <img src="{{ $pet->image_url ? '/storage/' . $pet->image_url : 'https://ui-avatars.com/api/?name='.urlencode($pet->name).'&background=fdfbf7&color=d35400' }}"
                         class="rounded-circle border border-4 border-white shadow-sm mb-3"
                         style="width: 120px; height: 120px; margin-top: -60px; object-fit: cover;"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($pet->name) }}&background=fdfbf7&color=d35400'">

                    <h2 class="fw-bold text-dark mb-1">{{ $pet->name }}</h2>
                    <span class="badge rounded-pill px-3 mb-4" style="background-color: #fdfbf7; color: #d35400; border: 1px solid #fce7d6;">#{{ $pet->pet_id }}</span>

                    <div class="row g-3 text-start p-3 rounded-3 mb-4" style="background-color: #faf8ef; border: 1px solid #eee;">
                        <div class="col-6">
                            <label class="text-muted small fw-bold text-uppercase">Species</label>
                            <p class="fw-bold mb-0">{{ ucfirst($pet->species) }}</p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small fw-bold text-uppercase">Breed</label>
                            <p class="fw-bold mb-0">{{ $pet->breed ?? 'N/A' }}</p>
                        </div>
                        <div class="col-12 border-top pt-2">
                            <label class="text-muted small fw-bold text-uppercase">Owner</label>
                            <p class="fw-bold mb-0">{{ $pet->user->name }}</p>
                        </div>
                    </div>

                    {{-- Recent Vaccination --}}
                    <h6 class="text-start fw-bold mb-3">Recent Vaccinations</h6>
                    <ul class="list-group list-group-flush text-start border rounded-3 mb-4">
                        @forelse($pet->vaccinations->sortByDesc('date_administered')->take(2) as $vax)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold d-block">{{ $vax->vaccine_name }}</span>
                                    <small class="text-muted">Administered</small>
                                </div>
                                <span class="badge bg-success-subtle text-success">{{ \Carbon\Carbon::parse($vax->date_administered)->format('M d, Y') }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted">No records found</li>
                        @endforelse
                    </ul>

                    <p class="text-muted small">
                        This is an official digital record of PawCare Veterinary Clinic.
                        If you have found this pet, please contact the clinic or owner immediately.
                    </p>
                </div>

                <div class="card-footer bg-white border-0 pb-4">
                     <a href="tel:CLINIC_PHONE_NUMBER" class="btn btn-primary w-100 rounded-pill fw-bold">
                        <i data-lucide="phone" class="me-2" style="width: 18px;"></i> Contact Clinic
                     </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
