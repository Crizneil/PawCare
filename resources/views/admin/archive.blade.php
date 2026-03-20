@extends('layout.admin')

@section('page_title', 'Archive Center')

@section('content')
    <div class="container-fluid p-3 p-md-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold mb-1">Archive Center</h2>
                <p class="text-muted small mb-0">Restore or reactivate archived records.</p>
            </div>
        </div>

        {{-- Archive Tabs --}}
        <ul class="nav nav-pills mb-4 gap-2">
            <li class="nav-item">
                <a class="nav-link rounded-pill px-4 {{ $tab === 'pets' ? 'active bg-orange' : 'bg-light text-dark' }}"
                    href="{{ route('admin.archive', ['tab' => 'pets']) }}">
                    <i data-lucide="dog" class="me-2 size-18"></i> Pets
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link rounded-pill px-4 {{ $tab === 'staff' ? 'active bg-orange' : 'bg-light text-dark' }}"
                    href="{{ route('admin.archive', ['tab' => 'staff']) }}">
                    <i data-lucide="users" class="me-2 size-18"></i> Staff
                </a>
            </li>
        </ul>

        <div class="card shadow-sm border-0 rounded-4 mb-4 p-3">
            <form action="{{ route('admin.archive') }}" method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="tab" value="{{ $tab }}">

                {{-- Search Input --}}
                <div class="col-12 {{ $tab === 'pets' ? 'col-md-7' : 'col-md-10' }}">
                    <label class="small text-muted mb-1">Search Keywords</label>
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-light rounded-start-pill ps-3 ps-md-4">
                            <i data-lucide="search" class="text-muted size-18"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}"
                            class="form-control border-0 bg-light rounded-end-pill py-2"
                            placeholder="Search archived {{ $tab }}...">
                    </div>
                </div>

                {{-- Dynamic Status Filter for Pets --}}
                @if($tab === 'pets')
                <div class="col-12 col-md-3">
                    <label class="small text-muted mb-1">Pet Status</label>
                    <select name="status" class="form-select border-0 bg-light rounded-pill py-2">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="deceased" {{ request('status') == 'deceased' ? 'selected' : '' }}>Deceased</option>
                    </select>
                </div>
                @endif

                {{-- Search Button --}}
                <div class="col-12 col-md-2">
                    <button class="btn btn-orange w-100 rounded-pill py-2 fw-bold shadow-sm">Filter</button>
                </div>
            </form>
        </div>

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            @if($tab === 'pets')
                                <tr>
                                    <th class="ps-4">Pet Name</th>
                                    <th>Owner</th>
                                    <th>Breed</th>
                                    <th>Status</th>
                                    <th>Deleted At</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            @elseif($tab === 'staff')
                                <tr>
                                    <th class="ps-4">Name</th>
                                    <th>Email</th>
                                    <th>Deleted At</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            @endif
                        </thead>
                        <tbody>
                            @forelse($data as $item)
                                <tr>
                                    @if($tab === 'pets')
                                        <td class="ps-4 fw-bold text-dark">{{ $item->name }}</td>
                                        <td>{{ $item->user->name ?? 'Unknown' }}</td>
                                        <td>{{ $item->breed }}</td>
                                        <td>
                                            @if($item->trashed())
                                                <span class="badge rounded-pill bg-danger text-white">Removed</span>
                                            @elseif($item->status === 'DECEASED')
                                                <span class="badge rounded-pill bg-dark text-white">Deceased</span>
                                            @else
                                                <span class="badge rounded-pill bg-secondary text-white">Inactive</span>
                                            @endif
                                        </td>
                                    @elseif($tab === 'staff')
                                        <td class="ps-4 fw-bold text-dark">{{ $item->name }}</td>
                                        <td>{{ $item->email }}</td>
                                    @endif
                                    <td class="small text-muted">
                                        @if($item->trashed())
                                            {{-- Show actual deletion time if soft-deleted --}}
                                            {{ $item->deleted_at->format('M d, Y • h:i A') }}
                                        @else
                                            {{-- Show last status change time for Deceased/Inactive --}}
                                            <span class="text-secondary"></span>
                                            {{ $item->updated_at->format('M d, Y • h:i A') }}
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                                            @php
                                                $restoreRoute = route("admin.{$tab}.restore", $item->id);
                                            @endphp
                                            <form action="{{ $restoreRoute }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-success px-4 rounded-pill">
                                                    <i data-lucide="rotate-ccw" class="size-14 me-1"></i>
                                                    {{ $item->trashed() ? 'Restore' : 'Reactivate' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i data-lucide="inbox" class="size-48 mb-2 opacity-25"></i>
                                        <p class="mb-0">No archived {{ $tab }} found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-center">
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
@endsection
