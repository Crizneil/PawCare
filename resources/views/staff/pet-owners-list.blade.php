@extends('layout.admin')

@section('content')
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <a href="{{ route('staff.pet-records') }}" class="btn btn-outline-secondary me-3 shadow-sm">
                    <i class="fi flaticon-back-arrow me-1"></i> Back to Pets
                </a>
                <h2 class="fw-bold mb-0">Pet Owners</h2>
            </div>

            <button type="button" class="btn btn-orange rounded-pill px-4 py-2 fw-semibold shadow-sm" data-bs-toggle="modal"
                data-bs-target="#addOwnerModal">
                <i class="fi flaticon-plus me-2"></i> Add New Owner
            </button>
        </div>

        {{-- Search Bar --}}
        <div class="card shadow-sm border-0 rounded-4 mb-4 p-3">
            <form action="{{ route('staff.owners') }}" method="GET" class="row g-2 align-items-center">
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

        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($owners as $owner)
                            <tr>
                                <td class="ps-4"><b>{{ $owner->name }}</b></td>
                                <td>{{ $owner->email }}</td>
                                <td><span class="badge bg-success rounded-pill">Active</span></td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('staff.owner.profile', $owner->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        View Profile
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">No owners found.</td>
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

    @include('partials._add_owner_modal', ['submitRoute' => route('staff.owners.store')])

@endsection
