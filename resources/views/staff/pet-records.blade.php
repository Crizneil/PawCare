@extends('layout.admin')

@section('page_title', 'Pet Records | Staff')

@section('content')
    <div class="container-fluid p-4 fade-in">
        {{-- Header Section - Added flex-wrap for mobile responsiveness --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-0">{{ $view === 'archived' ? 'Archived' : 'Pet' }} Database</h2>
                <p class="text-muted mb-0 small">
                    {{ $view === 'archived' ? 'Manage and recover records for deceased or deleted pets.' : 'Manage registry and vaccination records for all pets.' }}
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2 flex-grow-1 flex-md-grow-0">
                <form action="{{ route('staff.pet-records') }}" method="GET" class="d-flex flex-wrap flex-md-nowrap align-items-center gap-2">
                    {{-- Preserve View state --}}
                    <input type="hidden" name="view" value="{{ $view }}">

                    {{-- Status Filter Dropdown --}}
                    <div style="min-width: 180px;">
                        <select name="status" class="form-select border-0 shadow-sm rounded-pill px-3" onchange="this.form.submit()" style="height: 45px;">
                            <option value="">All Statuses</option>
                            <option value="no_records" {{ request('status') == 'no_records' ? 'selected' : '' }}>No Records</option>
                            <option value="partially_vaccinated" {{ request('status') == 'partially_vaccinated' ? 'selected' : '' }}>Partially Vaccinated</option>
                            <option value="fully_vaccinated" {{ request('status') == 'fully_vaccinated' ? 'selected' : '' }}>Vaccinated</option>
                            <option value="due_soon" {{ request('status') == 'due_soon' ? 'selected' : '' }}>Due Soon</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>

                    {{-- Search Input Group --}}
                    <div class="input-group shadow-sm rounded-pill bg-white overflow-hidden" style="height: 45px; min-width: 250px;">
                        <input type="text" name="search" class="form-control border-0 px-3"
                            placeholder="Search Pet..." value="{{ request('search') }}">
                        <button class="btn btn-orange px-4 border-0" type="submit">
                            <i data-lucide="search" style="width: 18px;"></i>
                        </button>
                    </div>

                    {{-- Reset Button --}}
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
                                            @if($pet->latestVaccination)
                                                <div class="mt-1">
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">
                                                        {{ $pet->latestVaccination->vaccine_name }}
                                                    </small>
                                                </div>
                                            @endif
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

    @include('partials._add_owner_modal', ['submitRoute' => route('staff.owners.store')])

    {{-- Scripts --}}
    @if(request()->has('search'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const rows = document.querySelectorAll('.pet-row');
                if (rows.length === 1) {
                    const viewBtn = rows[0].querySelector('[data-bs-toggle="modal"]');
                    if (viewBtn) {
                        setTimeout(() => { viewBtn.click(); }, 300);
                    }
                }
            });
        </script>
    @endif

    <script>
        let barcode = "";
        let lastKeyTime = Date.now();

        document.addEventListener('keydown', function (e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

            const currentTime = Date.now();
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
@endsection
