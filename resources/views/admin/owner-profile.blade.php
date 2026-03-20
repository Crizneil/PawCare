@extends('layout.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.owners') }}" class="btn btn-light rounded-circle me-3">
            <i data-lucide="arrow-left"></i>
        </a>
        <h3 class="fw-bold mb-0">Owner Profile</h3>
    </div>

    <div class="card border-0 shadow-sm rounded-4" style="background-color: #fdfbf7;">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Owner Information</h5>
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Full Name</small>
                    <div class="fw-bold text-dark">
                        {{ $owner->name ?? $owner->owner ?? 'Guest' }}
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <small class="text-muted">Phone Number</small>
                    <div class="fw-bold text-dark">
                        {{ $owner->phone ?? $owner->owner_phone ?? 'N/A' }}
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <small class="text-muted">Email Address</small>
                    <div class="fw-bold text-dark">
                        @if($owner->email && $owner->email !== 'No Online Account')
                            {{ $owner->email }}
                        @else
                            <span class="text-muted small">No Email Assigned</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <small class="text-muted">Address</small>
                    <div class="fw-bold text-dark">
                        @php
                            $addressParts = array_filter([
                                $owner->house_no,
                                $owner->street,
                                $owner->barangay,
                                $owner->city,
                                $owner->province
                            ]);
                        @endphp
                        {{ !empty($addressParts) ? implode(', ', $addressParts) : 'Address not provided' }}
                    </div>
                </div>
            </div>

            {{-- ACCOUNT STATUS SECTION (From your Staff Logic) --}}
            <div class="border-top pt-3">
                <h6 class="fw-bold mb-2">Account Status</h6>
                @if(empty($owner->password))
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary px-3 rounded-pill me-2">
                            Offline Record Only
                        </span>
                        <small class="text-muted">This owner has no online login credentials yet.</small>
                    </div>

                    {{-- Optional: Admin-specific Create Account Form --}}
                    <div class="mt-3">
                        <form action="{{ route('admin.owner.createAccount', ['id' => $owner->id ?? $owner->pet_id]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="is_walkin" value="{{ empty($owner->id) ? '1' : '0' }}">
                            <button type="submit" class="btn btn-sm btn-outline-orange rounded-pill">
                                <i data-lucide="user-plus" class="me-1" style="width:14px;"></i> Setup Online Access
                            </button>
                        </form>
                    </div>
                @else
                    <div>
                        <span class="badge bg-success px-3 rounded-pill">
                            <i data-lucide="check-circle" class="me-1" style="width:14px;"></i> Active Online Account
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- REGISTERED PETS WITH STATUS BADGES --}}
    <div class="card border-0 shadow-sm rounded-4 mt-4" style="background-color: #fdfbf7;">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Registered Pets</h5>
            @if($owner->pets->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($owner->pets as $pet)
                        <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 mb-2">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="fw-bold d-block">{{ $pet->name }}</span>
                                    <span class="text-muted small">({{ $pet->species }})</span>
                                </div>

                                {{-- VACCINATION STATUS BADGE --}}
                                @if(isset($pet->vax_status))
                                    <span class="badge rounded-pill {{ $pet->vax_status->class }} py-2 px-3 ms-2" style="font-size:0.75rem;">
                                        {!! $pet->vax_status->icon !!}
                                        <span class="ms-1">{{ $pet->vax_status->label }}</span>
                                    </span>
                                @endif
                            </div>
                            <span class="badge bg-light text-dark border rounded-pill">
                                {{ $pet->pet_id }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">No pets registered to this owner.</p>
            @endif
        </div>
    </div>
</div>
@endsection
