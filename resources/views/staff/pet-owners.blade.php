@extends('layout.admin')

@section('content')

<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('staff.pet-records') }}" class="btn btn-light rounded-circle me-3">
            <i data-lucide="arrow-left"></i>
        </a>
        <h3 class="fw-bold mb-0">Owner Profile</h3>
    </div>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Owner Information</h5>
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Full Name</small>
                    <div class="fw-bold text-dark">{{ $owner->name }}</div>
                </div>

                <div class="col-md-6 mb-3">
                    <small class="text-muted">Phone Number</small>
                    <div class="fw-bold text-dark">{{ $owner->phone ?? $owner->owner_phone ?? 'N/A' }}</div>
                </div>

                <div class="col-md-6 mb-3">
                    <small class="text-muted">Email</small>
                    <div class="fw-bold text-dark">
                    @if($owner->email)
                        {{ $owner->email }}
                    @else
                        <span class="badge bg-secondary">No Online Account</span>
                    @endif
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <small class="text-muted">Address</small>
                    <div class="fw-bold text-dark">
                        @php
                            // Clean up address by filtering out empty values
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

            {{-- ACCOUNT OPTION --}}
            <div class="border-top pt-3">
                <h6 class="fw-bold">Account Status</h6>

                @if(empty($owner->password))
                    {{-- CASE 1: OFFLINE RECORD --}}
                    <div class="mb-3">
                        <span class="badge bg-secondary">Offline Record Only</span>
                        <p class="text-muted small mt-1">This owner has no online login credentials yet.</p>
                    </div>

                    <form action="{{ route('staff.owner.createAccount', ['id' => $owner->id ?? $owner->pet_id]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="is_walkin" value="{{ empty($owner->id) ? '1' : '0' }}">

                        @if(!$owner->email || $owner->email == 'No Online Account')
                            <div class="mb-3 col-md-6">
                                <label class="small text-muted mb-1">Assign Email Address</label>
                                <input type="email" name="email" class="form-control rounded-pill shadow-sm"
                                    value="{{ $owner->email !== 'No Online Account' ? $owner->email : '' }}"
                                    placeholder="e.g. owner@email.com" required>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-orange rounded-pill shadow-sm">
                            <i data-lucide="user-plus" class="me-1" style="width:16px;"></i>
                            Create Online Account
                        </button>
                    </form>
                @else
                    {{-- CASE 2: REGISTERED USER / ONLINE ACCOUNT --}}
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
    <div class="card border-0 shadow-sm rounded-4 mt-4">
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
