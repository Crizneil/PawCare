@extends('layout.admin')

@section('page_title', 'Vaccination Status Dashboard')

@section('content')
<div class="container-fluid p-4">

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1">Vaccination Status</h2>
            <p class="text-muted small mb-0">Track and update vaccination records for all pet patients.</p>
        </div>

        <div class="w-100 w-md-auto">
            <form action="{{ url()->current() }}" method="GET" class="row g-2 align-items-center">
                {{-- Search Input --}}
                <div class="col-12 col-sm-5">
                    <div class="input-group">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control rounded-pill-start border-0 bg-light px-4"
                            placeholder="Search Pet ID or Name...">
                    </div>
                </div>

                {{-- Status Dropdown --}}
                <div class="col-12 col-sm-5">
                    <select name="status" class="form-select rounded-pill-end border-0 bg-light px-4" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="unvaccinated" {{ request('status') == 'unvaccinated' ? 'selected' : '' }}>Unvaccinated</option>
                        <option value="partially_vaccinated" {{ request('status') == 'partially_vaccinated' ? 'selected' : '' }}>Partially Vaccinated</option>
                        <option value="fully_vaccinated" {{ request('status') == 'fully_vaccinated' ? 'selected' : '' }}>Fully Vaccinated</option>
                        <option value="due_soon" {{ request('status') == 'due_soon' ? 'selected' : '' }}>Due Soon</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>

                {{-- Manual Filter Button (optional for mobile) --}}
                <div class="col-12 col-sm-2">
                    <button type="submit" class="btn btn-orange rounded-pill w-100 fw-bold">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 custom-mobile-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Pet</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th>Vaccine Type</th>
                            <th>Last Vaccination</th>
                            <th>Next Due Date</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pets as $pet)
                        @php
                            $vaccination = $pet->latestVaccination;
                        @endphp
                        <tr>
                            <td class="ps-4" data-label="Pet">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $pet->image_url ? '/storage/' . $pet->image_url : 'https://ui-avatars.com/api/?name=' . urlencode($pet->name) . '&background=fdfbf7&color=d35400' }}"
                                        class="rounded-circle me-3 shadow-sm border"
                                        style="width:40px;height:40px;object-fit:cover;"
                                        onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($pet->name) }}&background=fdfbf7&color=d35400'">
                                    <div>
                                        <div class="fw-bold small">{{ $pet->name }}</div>
                                        <small class="text-muted">{{ $pet->unique_id }}</small>
                                    </div>
                                </div>
                            </td>

                            <td data-label="Owner">
                                @if($pet->user)
                                    <div class="fw-bold small">{{ $pet->user->name }}</div>
                                @else
                                    <div class="fw-bold small">{{ $pet->owner ?? 'Guest' }}</div>
                                    <span class="badge bg-secondary-subtle text-secondary border" style="font-size: 0.65rem;">Walk-in</span>
                                @endif
                            </td>

                            <td data-label="Status">
                                @php
                                    $status = $pet->calculated_status;
                                    $badgeClass = match($status) {
                                        'fully_vaccinated'     => 'bg-success',
                                        'partially_vaccinated' => 'bg-info text-primary',
                                        'due_soon'             => 'bg-warning text-dark',
                                        'overdue'              => 'bg-dark',
                                        'unvaccinated'         => 'bg-danger',
                                        default                => 'bg-secondary',
                                    };
                                    $statusLabel = ucwords(str_replace('_', ' ', $status));
                                @endphp
                                <span class="badge {{ $badgeClass }} rounded-pill px-3">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td data-label="Vaccine Type">
                                @if($vaccination)
                                    <span class="fw-bold">{{ $vaccination->vaccine_name }}</span>
                                @else
                                    <span class="text-muted small">No record</span>
                                @endif
                            </td>

                            <td data-label="Last Vaccination" class="fw-bold small">
                                @if($vaccination)
                                    {{ \Carbon\Carbon::parse($vaccination->date_administered)->format('M d, Y') }}
                                @else
                                    --
                                @endif
                            </td>

                            <td data-label="Next Due Date" class="fw-bold small">
                                @if($vaccination && $vaccination->next_due_date)
                                    {{ \Carbon\Carbon::parse($vaccination->next_due_date)->format('M d, Y') }}
                                @else
                                    --
                                @endif
                            </td>

                            <td class="text-end pe-4 action-cell" data-label="Actions">
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-light border rounded-pill px-3 fw-medium shadow-sm"
                                            type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport">
                                        Manage <i class="bi bi-three-dots-vertical ms-1"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                                        <li>
                                            <a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#viewVaxHistory{{ $pet->id }}">
                                                <i class="bi bi-eye me-2 text-info"></i> View
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#updateVax{{ $pet->id }}">
                                                <i class="bi bi-pencil me-2 text-warning"></i> Update
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $pets->appends(request()->query())->links() }}
    </div>
</div>
@foreach($pets as $pet)
    @include('partials._vaccination_modal')
@endforeach
@endsection
