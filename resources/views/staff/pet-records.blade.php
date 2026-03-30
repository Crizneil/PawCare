@extends('layout.admin')

@section('page_title', 'Pet Records | Staff')

@section('content')
    <div class="container-fluid p-4 fade-in">
        {{-- Header Section --}}
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <div>
                    <h2 class="fw-bold mb-0">{{ $view === 'archived' ? 'Archived' : 'Pet' }} Database</h2>
                    <p class="text-muted mb-0 small">
                        {{ $view === 'archived' ? 'Manage and recover records for deceased or deleted pets.' : 'Manage registry and vaccination records for all pets.' }}
                    </p>
                </div>

                {{-- Primary Action: Moved above filters --}}
                @if($view !== 'archived')
                    <div class="d-flex gap-2">
                        {{-- Reports Dropdown --}}
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary rounded-pill px-4 shadow-sm dropdown-toggle"
                                    type="button" id="reportsDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="height: 45px;">
                                <i class="bi bi-file-earmark-pdf me-2"></i> Reports
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="reportsDropdown">
                                <li><h6 class="dropdown-header">Active Pets Directory</h6></li>
                                {{-- Updated to use staff. route prefix --}}
                                <li><a class="dropdown-item py-2" href="{{ route('staff.reports.active-pets', ['pdf' => 1]) }}" target="_blank"><i class="bi bi-printer me-2 text-muted"></i> All Pets Report</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('staff.reports.active-pets', ['pdf' => 1, 'species' => 'Dog']) }}" target="_blank"><i class="bi bi-printer me-2 text-muted"></i> Dogs Only Report</a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('staff.reports.active-pets', ['pdf' => 1, 'species' => 'Cat']) }}" target="_blank"><i class="bi bi-printer me-2 text-muted"></i> Cats Only Report</a></li>
                            </ul>
                        </div>

                        {{-- Add New Pet Button --}}
                        <button class="btn btn-orange rounded-pill px-4 shadow-sm d-flex align-items-center gap-2"
                                data-bs-toggle="modal" data-bs-target="#addPetModal" style="height: 45px;">
                            <i data-lucide="plus-circle" style="width: 20px;"></i>
                            <span class="fw-bold">Add New Pet</span>
                        </button>
                    </div>
                @endif
            </div>

            {{-- Filters and Search Row --}}
            <div class="d-flex flex-wrap gap-2">
                <form action="{{ route('staff.pet-records') }}" method="GET" class="d-flex flex-wrap flex-md-nowrap align-items-center gap-2 w-100">
                    <input type="hidden" name="view" value="{{ $view }}">

                    {{-- Updated Status Filter --}}
                    <div style="min-width: 200px;">
                        <select name="status" class="form-select border-0 shadow-sm rounded-pill px-3" onchange="this.form.submit()" style="height: 45px;">
                            <option value="">All Vaccination Status</option>
                            <option value="unvaccinated" {{ request('status') == 'unvaccinated' ? 'selected' : '' }}>Unvaccinated</option>
                            <option value="vaccinated" {{ request('status') == 'vaccinated' ? 'selected' : '' }}>Vaccinated</option>
                            <option value="due_soon" {{ request('status') == 'due_soon' ? 'selected' : '' }}>Due Soon</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>

                    {{-- Search Input --}}
                    <div class="input-group shadow-sm rounded-pill bg-white overflow-hidden ms-md-auto" style="height: 45px; max-width: 400px;">
                        <input type="text" name="search" class="form-control border-0 px-3"
                            placeholder="Search Pet Name or ID..." value="{{ request('search') }}">
                        <button class="btn btn-orange px-4 border-0" type="submit">
                            <i data-lucide="search" style="width: 18px;"></i>
                        </button>
                    </div>

                    @if(request()->has('status') || request()->has('search'))
                        <a href="{{ route('staff.pet-records', ['view' => $view]) }}" class="btn btn-light rounded-pill shadow-sm border d-flex align-items-center justify-content-center" style="height: 45px; width: 45px;">
                            <i data-lucide="refresh-cw" style="width: 18px;"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    {{-- Added custom-mobile-table --}}
                    <table class="table table-hover align-middle mb-0 custom-mobile-table">
                        <thead class="bg-light text-secondary text-uppercase small fw-bold">
                            <tr>
                                <th class="ps-4 py-3">Pet ID</th>
                                <th>Pet Info</th>
                                <th>Type</th>
                                <th>Owner</th>
                                <th>Vax Status</th>
                                <th class="text-center pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pets as $pet)
                                @php $status = $pet->vax_status; @endphp

                                <tr class="pet-row @if(request('search') == $pet->pet_id) table-warning @endif">
                                    <td class="ps-4" data-label="Pet ID">
                                        <span
                                            class="badge {{ $pet->trashed() ? 'bg-danger-subtle text-danger' : 'bg-light text-dark' }} border">
                                            #{{ $pet->id }}{{ $pet->trashed() ? ' [Deleted]' : '' }}
                                        </span>
                                    </td>

                                    <td data-label="Pet Info">
                                        <div class="fw-bold text-dark">{{ $pet->name }}</div>
                                        <small class="text-muted">{{ $pet->breed ?? 'Unknown Breed' }}</small>
                                    </td>

                                    <td data-label="Type">
                                        <span
                                            class="badge bg-blue-light text-primary text-capitalize">{{ $pet->species ?? 'Dog' }}</span>
                                    </td>

                                    <td data-label="Owner">
                                        @if($pet->user_id && $pet->user)
                                            {{-- Only generate the route if user_id is NOT null --}}
                                            <a href="{{ route('staff.pet-owners', ['id' => $pet->user_id]) }}" class="text-decoration-none">
                                                <div class="text-dark fw-bold">{{ $pet->user->name }}</div>
                                                <small class="text-muted">
                                                    <i data-lucide="user-check" style="width: 12px; height: 12px;"></i> Member
                                                </small>
                                            </a>
                                        @else
                                            {{-- Fallback for walk-in clients or pets without a linked user account --}}
                                            <div class="text-secondary fw-bold">{{ $pet->owner ?? 'Unknown Owner' }}</div>
                                            <span class="badge bg-light text-secondary border small">
                                                <i data-lucide="info" style="width: 12px; height: 12px;"></i> Walk-in Client
                                            </span>
                                        @endif
                                    </td>

                                    <td data-label="Vax Status">
                                        @if($pet->status === 'DECEASED')
                                            <span class="badge rounded-pill bg-dark text-white px-3 py-2 fw-bold"
                                                style="font-size: 0.75rem;">
                                                DECEASED
                                            </span>
                                        @elseif($pet->trashed())
                                            <span class="badge rounded-pill bg-danger text-white px-3 py-2 fw-bold"
                                                style="font-size: 0.75rem;">
                                                ARCHIVED
                                            </span>
                                        @else
                                            <span class="badge rounded-pill border {{ $status->class }} px-3 py-2 fw-bold"
                                                style="font-size: 0.75rem; min-width: 110px; display: inline-block; text-align: center;">
                                                {{ $status->label }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-center pe-4" data-label="Actions">
                                        @if($view === 'archived')
                                            <div class="d-flex flex-wrap gap-1">
                                                @if($pet->trashed())
                                                    <form action="{{ route('staff.pets.restore', $pet->id) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button class="btn btn-sm btn-success rounded-pill px-3 shadow-sm fw-bold">
                                                            <i data-lucide="rotate-ccw" style="width: 14px;" class="me-1"></i> Restore
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('staff.pets.force-delete', $pet->id) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm fw-bold">
                                                            <i data-lucide="trash-2" style="width: 14px;" class="me-1"></i> Final Delete
                                                        </button>
                                                    </form>
                                                @elseif($pet->status === 'DECEASED' || $pet->status === 'INACTIVE')
                                                    <form action="{{ route('staff.pets.restore-deceased', $pet->id) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm fw-bold">
                                                            <i data-lucide="heart" style="width: 14px;" class="me-1"></i> Recover
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('staff.pets.force-delete', $pet->id) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm fw-bold">
                                                            <i data-lucide="trash-2" style="width: 14px;" class="me-1"></i> Final Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @else
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light border rounded-pill px-3 shadow-sm"
                                                    type="button" data-bs-toggle="dropdown">
                                                    Manage <i data-lucide="more-vertical" class="ms-1" style="width: 14px;"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3">
                                                    <li>
                                                        <a class="dropdown-item py-2" href="#" id="view-btn-{{ $pet->id }}"
                                                            data-bs-toggle="modal" data-bs-target="#viewPetModal{{ $pet->id }}">
                                                            <i data-lucide="eye" class="me-2 text-primary" style="width: 16px;"></i>
                                                            View Pet Profile
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item py-2 text-success"
                                                            href="{{ route('staff.vaccination-history', ['pet_id' => $pet->id]) }}">
                                                            <i data-lucide="history" class="me-2" style="width: 16px;"></i> Vax
                                                            History
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item py-2 text-danger"
                                                            href="{{ route('staff.reports.pet-medical-history', $pet->id) }}" target="_blank">
                                                            <i data-lucide="file-text" class="me-2" style="width: 16px;"></i> Medical History (PDF)
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i data-lucide="database" class="mb-2"
                                            style="width: 40px; height: 40px; opacity: 0.5;"></i>
                                        <p>No pet records found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($pets->hasPages())
                <div class="card-footer bg-white border-0 py-3 text-center">
                    {{ $pets->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modals --}}
    @foreach ($pets as $pet)
        @include('partials._view_pet_modal')
    @endforeach

    @include('partials._add_pet_modal', ['submitRoute' => route('staff.pets.store')])

    @include('partials._add_owner_modal', ['submitRoute' => route('staff.owners.store')])
@endsection
<style>
   /* 1. Force the inner container to match Bootstrap height */
    .choices__inner {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.375rem !important;
        /* Adjust this min-height to match your "Pet Name" input height exactly */
        min-height: 40px !important;
        padding: 4px 12px !important;
        display: flex;
        align-items: center;
    }

    /* 2. the height of the list/selected item */
    .choices__list--single {
        padding: 0 !important;
        line-height: 1.5;
    }

    /* 3. Ensure the search input inside the dropdown doesn't add extra bulk */
    .choices__input {
        background-color: transparent !important;
        font-size: 14px !important;
        margin-bottom: 0 !important;
    }

    /* 4. dropdown positioning and z-index */
    .choices__list--dropdown {
        z-index: 2000 !important;
        background-color: white !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
    }

    /* 5. Align the placeholder text and selected text vertically */
    .choices__placeholder {
        opacity: 1;
        color: #6c757d; /* Standard Bootstrap muted color */
    }
    .choices__list--single .choices__item {
    color: #212529 !important; /* Standard Bootstrap dark text */
    opacity: 1 !important;
    font-weight: 500;
    }

    /* Ensure the text remains visible even when the input is focused */
    .choices__list--single .choices__item--selectable {
        color: #212529 !important;
    }

    /* Fix for the "Bulldog" / selected breed looking less visible */
    .choices__inner .choices__item {
        font-size: 14px !important;
    }
</style>
@push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="{{ asset('assets/js/pet-registration.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- 1. OWNER SEARCH INITIALIZATION ---
    const ownerSelect = document.getElementById('ownerSearchSelect');
    if (ownerSelect && typeof Choices !== 'undefined') {
        new Choices(ownerSelect, {
            searchEnabled: true,
            itemSelectText: '',
            placeholder: true,
            placeholderValue: 'Select Registered Owner...',
            searchPlaceholderValue: 'Type name or email...',
            shouldSort: false,
            allowHTML: true,
        });
    }

    // --- 2. BREED LOGIC INITIALIZATION ---
    const speciesSelectElem = document.getElementById('speciesSelect');
    const breedSelectElem = document.getElementById('breedSelect');

    let breedChoicesInstance = null;
    let speciesChoicesInstance = null;

    if (breedSelectElem && typeof Choices !== 'undefined') {
        breedChoicesInstance = new Choices(breedSelectElem, {
            searchEnabled: true,
            itemSelectText: '',
            placeholder: true,
            placeholderValue: 'Select Breed',
            allowHTML: true,
            searchFloor: 0, // Ensure search starts immediately
            shouldSort: false,
            position: 'auto'
        });
    }

    // Initialize the logic from pet-registration.js
    if (typeof initializePetBreedLogic === 'function') {
        initializePetBreedLogic({
            speciesId: 'speciesSelect',
            breedId: 'breedSelect',
            otherContainerId: 'otherBreedContainer',
            otherInputId: 'otherBreedInput'
        }, breedChoicesInstance);
    }

    // --- 3. Manual Trigger for Choices.js Species Change ---
    // Choices.js hides the real select, so we listen to its custom event
    if (speciesSelectElem) {
        speciesSelectElem.addEventListener('change', function(event) {
            // This manually calls the update function when the user picks Dog/Cat
            if (typeof initializePetBreedLogic === 'function') {
                // We re-run the logic to refresh the breed dropdown
                const selectedSpecies = event.target.value;

                // If you want to force the registration.js to update:
                const config = {
                    speciesId: 'speciesSelect',
                    breedId: 'breedSelect',
                    otherContainerId: 'otherBreedContainer',
                    otherInputId: 'otherBreedInput'
                };
            }
        });
    }

    // --- 4. AUTO-OPEN MODAL ON SEARCH ---
    @if(request()->has('search'))
        const rows = document.querySelectorAll('.pet-row');
        if (rows.length === 1) {
            const viewBtn = rows[0].querySelector('[data-bs-toggle="modal"]');
            if (viewBtn) {
                setTimeout(() => { viewBtn.click(); }, 300);
            }
        }
    @endif
});

// --- 5. BARCODE / QR SCANNER LOGIC (Existing Logic) ---
let barcode = "";
let lastKeyTime = Date.now();

document.addEventListener('keydown', function (e) {
    // Don't trigger scanner if typing in an input
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) return;

    const currentTime = Date.now();
    // If typing is slow, it's a human, not a scanner (100ms threshold)
    if (currentTime - lastKeyTime > 100) barcode = "";

    if (e.key.length === 1) barcode += e.key;

    if (e.key === 'Enter' && barcode.length > 5) {
        e.preventDefault();
        window.location.href = "{{ route('staff.pet-records') }}?search=" + encodeURIComponent(barcode);
        barcode = "";
    }
    lastKeyTime = currentTime;
});
</script>
@endpush
