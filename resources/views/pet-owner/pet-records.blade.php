@extends('layout.admin')

@section('page_title', 'My Pets')

@section('content')
    @php
        /** @var \App\Models\User $user */
        $user = Auth::user();
    @endphp
    <div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">My Pets</h2>
            <p class="text-muted small">Manage your pets and view their digital ID cards.</p>
        </div>
    </div>

    </button>
</div>

<div class="row">
    @forelse(Auth::user()->pets as $pet)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-body p-4">
                {{-- Header with Image and Name --}}
                <div class="d-flex align-items-center mb-4 position-relative">
                    <div class="position-relative">
                        <img src="{{ $pet->image_url ? asset($pet->image_url) : 'https://ui-avatars.com/api/?name=' . urlencode($pet->name) . '&background=fce7d6&color=primary' }}"
                            class="rounded-circle border border-3 border-white shadow-sm {{ $pet->status === 'DECEASED' ? 'grayscale opacity-75' : '' }}"
                            style="width: 80px; height: 80px; object-fit: cover;">
                        @if($pet->status === 'DECEASED')
                        <div
                            class="position-absolute bottom-0 start-50 translate-middle-x bg-dark text-white tiny-badge px-2 rounded-pill shadow-sm">
                            MEMORIAM
                        </div>
                        @endif
                    </div>
                    <div class="ms-3">
                        <h4 class="fw-bold text-dark mb-0 {{ $pet->status === 'DECEASED' ? 'text-muted' : '' }}">
                            {{ $pet->name }}
                            @if($pet->status === 'DECEASED')
                            <i class="bi bi-heart-fill text-danger ms-1 small" style="font-size: 12px;"></i>
                            @endif
                        </h4>
                        <span
                            class="badge rounded-pill {{ $pet->status === 'DECEASED' ? 'bg-secondary-subtle text-secondary' : 'bg-primary-subtle text-primary border border-primary' }} px-3">
                            {{ ucfirst($pet->species) }}
                        </span>
                    </div>

                    {{-- Edit Button Trigger --}}
                    <button class="btn btn-sm btn-light rounded-circle position-absolute top-0 end-0 shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#editPetModal{{ $pet->id }}" title="Edit Pet">
                        <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i>
                    </button>
                </div>

                {{-- Breed and Birthdate (Real Data) --}}
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <label class="small text-muted d-block text-uppercase fw-bold">Breed</label>
                        <span class="text-dark fw-medium">{{ $pet->breed ?? 'N/A' }}</span>
                    </div>
                    <div class="col-6">
                        <label class="small text-muted d-block text-uppercase fw-bold">Birthdate</label>
                        <span
                            class="text-dark fw-medium">{{ $pet->birthday ? \Carbon\Carbon::parse($pet->birthday)->format('M d, Y') : 'N/A' }}</span>
                    </div>
                </div>

                {{-- REAL Vaccination Status --}}
                <div class="bg-light rounded-3 p-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-bold text-dark">Status</span>

                        @php $vaxStatus = $pet->vax_status; @endphp

                        <span class="badge rounded-pill {{ $vaxStatus->class }} px-2">
                            @if($vaxStatus->label == 'Up to Date')
                            <i data-lucide="check-circle" class="me-1" style="width: 14px;"></i>
                            @elseif($vaxStatus->label == 'Due Soon')
                            <i data-lucide="clock" class="me-1" style="width: 14px;"></i>
                            @else
                            <i data-lucide="alert-circle" class="me-1" style="width: 14px;"></i>
                            @endif
                            {{ $vaxStatus->label }}
                        </span>
                    </div>

                    <div class="small text-muted">
                        Last: {{ $pet->latestVaccination->vaccine_name ?? 'None' }}
                    </div>

                    @if($pet->latestVaccination && $pet->latestVaccination->next_due_date)
                    <div class="small text-muted mt-1">
                        Next: {{ \Carbon\Carbon::parse($pet->latestVaccination->next_due_date)->format('M d, Y') }}
                    </div>
                    @endif
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('pet-owner.digital-id', $pet->id) }}"
                        class="btn btn-outline-dark rounded-pill shadow-sm">
                        <i data-lucide="qr-code" class="me-2" style="width: 16px;"></i> View Digital ID
                    </a>
                    <a href="{{ route('pet-owner.reports.pet-medical-history', $pet->id) }}"
                        class="btn btn-outline-danger rounded-pill shadow-sm" target="_blank">
                        <i data-lucide="file-text" class="me-2" style="width: 16px;"></i> Download Medical History
                    </a>
                </div>
            </div>
        </div>
    </div>
    @include('partials._edit_pet_modal')
    @empty
    <div class="col-12 text-center py-5">
        <div class="mb-3"><i data-lucide="paw-print" class="text-muted" style="width: 60px; height: 60px;"></i>
        </div>
        <h5 class="text-muted">No pets registered yet.</h5>
        <p class="text-muted small">Please visit the clinic to register your pet's information.</p>
    </div>
    @endforelse
</div>
</div>

<script>
    function previewImage(input, petId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview' + petId).src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
