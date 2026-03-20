@extends('layout.admin')

@section('page_title', 'Profile Dashboard')

@section('content')
<div class="container-fluid p-4">
    {{-- Header Section --}}
    <div class="mb-4">
        <h2 class="fw-bold mb-0">My Profile</h2>
        <p class="text-muted small">Manage your personal information and account security.</p>
    </div>

    <div class="row">
        {{-- Left Column: Avatar & Quick Info --}}
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4 h-100">
                <div class="card-body">
                    <div class="position-relative d-inline-block mb-4">
                        <img src="{{ auth()->user()->profile_image ? '/storage/' . auth()->user()->profile_image : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=d35400&color=fff&size=150' }}"
                             class="rounded-circle shadow-sm border border-4 border-white mb-3" 
                             style="width: 140px; height: 140px; object-fit: cover;"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=d35400&color=fff&size=150'"
                             alt="Profile Picture">
                        <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle" style="width: 18px; height: 18px;" title="Online"></span>
                    </div>

                    <h4 class="fw-bold text-dark mb-1">{{ auth()->user()->name }}</h4>
                    <span class="badge bg-orange-subtle text-orange px-3 py-2 rounded-pill small fw-bold mb-4">
                        {{ strtoupper(auth()->user()->role) }}
                    </span>

                    <div class="list-group list-group-flush text-start mt-2">
                        <div class="list-group-item border-0 px-0 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-2 me-3">
                                    <i data-lucide="mail" class="text-muted" style="width: 18px;"></i>
                                </div>
                                <div>
                                    <label class="small text-muted d-block">Email Address</label>
                                    <span class="fw-bold text-dark">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 px-0 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-2 me-3">
                                    <i data-lucide="phone" class="text-muted" style="width: 18px;"></i>
                                </div>
                                <div>
                                    <label class="small text-muted d-block">Mobile Number</label>
                                    {{-- Changed to 'phone' to match User model --}}
                                    <span class="fw-bold text-dark">{{ auth()->user()->phone ?? 'Not Set' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Detailed Account Information --}}
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 p-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark m-0">Account Details</h5>
                    <button class="btn btn-orange rounded-pill px-4 shadow-sm fw-bold btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#editProfileModal">
                        <i data-lucide="user-cog" class="me-2" style="width: 16px;"></i> Edit Profile
                    </button>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="row g-4">
                        {{-- Standardized Personal Data Row --}}
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold mb-1">Gender</label>
                            <p class="border-bottom pb-2 text-dark fw-bold">
                                {{ ucfirst(auth()->user()->gender) ?? 'Not Specified' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold mb-1">Mobile Number</label>
                            <p class="border-bottom pb-2 text-dark fw-bold">
                                {{ auth()->user()->phone ?? '---' }}
                            </p>
                        </div>

                        {{-- Address Section --}}
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-muted small text-uppercase mb-3">Address Information</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase fw-bold mb-1">House No. / Street</label>
                            <p class="border-bottom pb-2 text-dark fw-bold">{{ auth()->user()->house_number }} {{ auth()->user()->street }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase fw-bold mb-1">Barangay</label>
                            <p class="border-bottom pb-2 text-dark fw-bold">{{ auth()->user()->barangay }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase fw-bold mb-1">City / Province</label>
                            <p class="border-bottom pb-2 text-dark fw-bold">{{ auth()->user()->city }}, {{ auth()->user()->province }}</p>
                        </div>
                    </div>

                    {{-- Security Note Banner --}}
                    <div class="mt-5 p-3 rounded-4 d-flex align-items-center border" style="background-color: #fdfdfd;">
                        <div class="bg-orange-subtle rounded-circle p-3 me-3 text-orange">
                            <i data-lucide="shield-check" style="width: 24px;"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1 text-dark">Data Privacy</h6>
                            <p class="small text-muted mb-0">Your contact details are used for appointment reminders and emergency pet alerts.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials._edit_profile_modal')
@endsection
