@extends('layout.admin')

@section('page_title', 'Pet Records Dashboard')

@section('content')
    <div class="container-fluid p-4 fade-in">
        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Pet Database</h2>
                <p class="text-muted small mb-0">Manage registry and vaccination records for all active pets.</p>
            </div>
            <div class="d-flex gap-2">
                {{-- Link to your separate Archive Center route --}}
                <a href="{{ route('admin.archive') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                    <i class="bi bi-archive me-2"></i> Archive Center
                </a>
                <button type="button" class="btn btn-orange rounded-pill px-4 py-2 fw-semibold shadow-sm" data-bs-toggle="modal"
                    data-bs-target="#addPetModal">
                    <i class="fi flaticon-plus me-2"></i> Add New Pet
                </button>
            </div>
        </div>

        {{-- Alerts Section --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Unified Search Bar Card --}}
        <div class="card shadow-sm border-0 rounded-4 mb-4 p-3">
            <form action="{{ url()->current() }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-light rounded-start-pill ps-4">
                            <i class="bi bi-qr-code-scan text-muted"></i>
                        </span>
                        <input type="text" name="pet_id" value="{{ request('pet_id') }}"
                            class="form-control border-0 bg-light py-2" placeholder="Scan/Type Pet ID...">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-light ps-3">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="general_search" value="{{ request('general_search') }}"
                            class="form-control border-0 bg-light py-2" placeholder="Search by name, breed, or owner...">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-orange w-100 rounded-pill py-2 fw-bold shadow-sm">Search</button>
                </div>
            </form>
        </div>

        {{-- Table Card --}}
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 custom-mobile-table">
                        <thead class="bg-light d-none d-md-table-header-group">
                            <tr class="text-uppercase small fw-bold text-muted">
                                <th class="ps-4 py-3">Pet ID</th>
                                <th>Pet Info</th>
                                <th>Type</th>
                                <th>Owner</th>
                                <th>Status</th>
                                <th class="text-center pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pets as $pet)
                                <tr>
                                    <td class="ps-md-4" data-label="Pet ID">
                                        <span class="badge bg-light text-dark border">#{{ $pet->id }}</span>
                                    </td>

                                    <td data-label="Pet Info">
                                        <div class="fw-bold text-dark">{{ $pet->name }}</div>
                                        <small class="text-muted">{{ $pet->breed ?? 'Hybrid' }}</small>
                                    </td>

                                    <td data-label="Type">
                                        <span class="badge bg-blue-light text-primary text-capitalize">
                                            {{ $pet->type ?? $pet->species ?? 'Dog' }}
                                        </span>
                                    </td>

                                    <td data-label="Owner">
                                        <div class="text-dark fw-medium">{{ $pet->user->name ?? 'Unassigned' }}</div>
                                        <div class="text-muted small">{{ $pet->user->phone ?? 'No Phone' }}</div>
                                    </td>

                                    <td data-label="Status">
                                        @if(isset($pet->status) && $pet->status == 'needs_booster')
                                            <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning px-3">Booster Due</span>
                                        @else
                                            <span class="badge rounded-pill bg-success-subtle text-success border border-success px-3">Active</span>
                                        @endif
                                    </td>

                                    <td class="text-md-center pe-md-4" data-label="Actions">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border rounded-pill px-3 shadow-sm fw-medium w-100 w-md-auto"
                                                type="button" data-bs-toggle="dropdown">
                                                Manage <i class="bi bi-three-dots-vertical ms-1"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                                <li>
                                                    <a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#viewPetModal{{ $pet->id }}">
                                                        <i class="bi bi-eye me-2 text-primary"></i> View Profile
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#editPetModal{{ $pet->id }}">
                                                        <i class="bi bi-pencil me-2 text-warning"></i> Update Pet
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item py-2" href="{{ route('admin.reports.pet-medical-history', $pet->id) }}" target="_blank">
                                                        <i class="bi bi-file-pdf me-2 text-danger"></i> Medical History (PDF)
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item py-2" href="{{ route('admin.vaccination-status', ['search' => $pet->name]) }}">
                                                        <i class="bi bi-shield-check me-2 text-success"></i> Vax Status
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item py-2 text-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deletePetModal{{ $pet->id }}">
                                                        <i class="bi bi-trash me-2"></i> Delete Record
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-database-exclamation mb-2 fs-1 d-block"></i>
                                        No active pet records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(method_exists($pets, 'hasPages') && $pets->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $pets->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modals --}}
    @foreach ($pets as $pet)
        @include('partials._pet_modal')
        @include('partials._view_pet_modal')
    @endforeach

    @include('partials._add_pet_modal')
@endsection
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<style>
    /* Styling to match your Bootstrap theme */
    .choices__inner {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.375rem !important;
        min-height: 45px;
    }
    .choices__list--dropdown {
        z-index: 1060 !important; /* Higher than Bootstrap modal */
    }
</style>
@push('scripts')
<script src="{{ asset('assets/js/pet-registration.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Initialize Searchable Owner Dropdown (Choices.js)
        const ownerEl = document.getElementById('ownerSearchSelect');
        if (ownerEl) {
            new Choices(ownerEl, {
                searchEnabled: true,
                itemSelectText: '',
                placeholderValue: 'Type name or email...',
                searchPlaceholderValue: 'Search owners...',
                shouldSort: false,
                allowHTML: true, // Required for newer versions of Choices.js
            });
        }

        // 2. Dynamic Breed Selection Logic (Centralized)
        if (typeof initializePetBreedLogic === 'function') {
            initializePetBreedLogic({
                speciesId: 'speciesSelect',
                breedId: 'breedSelect',
                breedContainerId: null, // No specific container needed for admin view
                otherContainerId: null, // Handled inside logic
                otherInputId: 'otherBreedInput',
                swapNameOnOther: true
            });
        }

        // 3. Admin Scanner Auto-Modal Logic
        const urlParams = new URLSearchParams(window.location.search);
        const petIdParam = urlParams.get('pet_id') || urlParams.get('general_search');

        if (petIdParam) {
            const firstViewBtn = document.querySelector('[data-bs-target^="#viewPetModal"]');
            if (firstViewBtn) {
                setTimeout(() => {
                    firstViewBtn.click();
                }, 500);
            }
        }
    });
</script>
@endpush
