@extends('layout.admin')

@section('content')
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.pet-records') }}" class="btn btn-outline-secondary me-3 shadow-sm">
                    <i class="fi flaticon-back-arrow me-1"></i> Back to Pets
                </a>
                <h2 class="fw-bold mb-0">Pet Owners</h2>
            </div>

            <button type="button" class="btn btn-orange rounded-pill px-4 py-2 fw-semibold shadow-sm" data-bs-toggle="modal"
                data-bs-target="#addOwnerModal">
                <i class="fi flaticon-plus me-2"></i> Add New Owner
            </button>
        </div>

        {{-- NEW: Search Bar --}}
        <div class="card shadow-sm border-0 rounded-4 mb-4 p-3">
            <form action="{{ route('admin.owners') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-light rounded-start-pill ps-4">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control border-0 bg-light py-2 rounded-end-pill" placeholder="Search owner by name or email...">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-orange w-100 rounded-pill py-2 fw-bold shadow-sm">Search</button>
                </div>
            </form>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Name</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($owners as $owner)
                            <tr>
                                <td class="ps-4"><b>{{ $owner->name }}</b></td>
                                <td>{{ $owner->email }}</td>
                                <td><span class="badge bg-success rounded-pill">Active</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">No owners found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($owners->hasPages())
                <div class="card-footer bg-white border-top-0 py-3">
                    {{ $owners->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Add Owner Modal -->
    <div class="modal fade" id="addOwnerModal" tabindex="-1" aria-labelledby="addOwnerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white" style="background: #2c3e50;">
                    <h5 class="modal-title fw-bold" id="addOwnerModalLabel">Add New Pet Owner</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.owners.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0" style="background: #eef2f5; color: #333;">
                            <i class="fi flaticon-info me-2 text-primary"></i>
                            <strong>Note:</strong> Creating an account will automatically generate a secure password and email it to the owner.
                        </div>

                        <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">Personal Information</h6>
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label fw-bold">First Name</label>
                                <input type="text" class="form-control bg-light" name="first_name" required placeholder="Juan" value="{{ old('first_name') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold">M.I. <span class="text-muted fw-normal">(Opt)</span></label>
                                <input type="text" class="form-control bg-light" name="middle_initial" maxlength="2" placeholder="D." value="{{ old('middle_initial') }}">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold">Last Name</label>
                                <input type="text" class="form-control bg-light" name="last_name" required placeholder="Dela Cruz" value="{{ old('last_name') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Gender</label>
                                <select name="gender" class="form-select bg-light" required>
                                    <option value="" selected disabled>Select Gender</option>
                                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control bg-light" name="email" required placeholder="juan@example.com" value="{{ old('email') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Mobile Number</label>
                                <input type="text" class="form-control bg-light" name="phone" required placeholder="09123456789" value="{{ old('phone') }}">
                            </div>
                        </div>

                        <h6 class="fw-bold mt-4 mb-3 text-primary border-bottom pb-2">Address Information</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Unit/House No.</label>
                                <input type="text" class="form-control bg-light" name="house_no" required placeholder="B1 L2" value="{{ old('house_no') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Street</label>
                                <input type="text" class="form-control bg-light" name="street" required placeholder="St. Mary St." value="{{ old('street') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Barangay</label>
                                <input type="text" class="form-control bg-light" name="barangay" required placeholder="e.g., Banga" value="{{ old('barangay') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">City/Municipality</label>
                                <input type="text" class="form-control bg-light" name="city" required value="Meycauayan" value="{{ old('city') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Province</label>
                                <input type="text" class="form-control bg-light" name="province" required value="Bulacan" value="{{ old('province') }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn text-white px-4 fw-bold" style="background:#ff6b6b;">Register Owner</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
