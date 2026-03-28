@extends('layout.admin')

@section('page_title', 'Logs Dashboard')

@section('content')
<div class="container-fluid p-3 p-md-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1">{{ $view === 'archived' ? 'Archived' : 'Activity' }} Logs</h2>
            <p class="text-muted small mb-0">Track user actions and system changes.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            @if($view === 'archived')
                <button type="button" class="btn btn-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#restoreAllLogsModal">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Restore All
                </button>
                <a href="{{ route('admin.logs') }}" class="btn btn-light rounded-pill px-3 border">
                    <i class="bi bi-arrow-left me-1"></i> Back to Active
                </a>
            @else
                <a href="{{ route('admin.logs', ['view' => 'archived']) }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-archive me-1"></i> View Archived
                </a>
                <button type="button" class="btn btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#archiveLogsModal">
                    <i class="bi bi-trash2 me-1"></i> Archive Logs
                </button>
            @endif
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 mb-4 p-3">
        <form action="{{ route('admin.logs') }}" method="GET" class="row g-2">
            <input type="hidden" name="view" value="{{ $view }}">
            <div class="col-12 col-md-10">
                <div class="input-group">
                    <span class="input-group-text border-0 bg-light rounded-start-pill ps-3 ps-md-4">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}"
                           class="form-control border-0 bg-light rounded-end-pill py-2"
                           placeholder="Search by user, role, or action...">
                </div>
            </div>
            <div class="col-12 col-md-2">
                <button class="btn btn-orange w-100 rounded-pill py-2 fw-bold">Search</button>
            </div>
        </form>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 custom-mobile-table">
                    <thead class="bg-light d-none d-lg-table-header-group">
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Role</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th class="{{ $view === 'archived' ? '' : 'text-end' }} pe-4">Timestamp</th>
                            @if($view === 'archived')
                                <th class="text-end pe-4">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-transparent">
                        @forelse($logs as $log)
                        <tr>
                            <td data-label="User" class="ps-lg-4">
                                <span class="fw-semibold">{{ $log->user->name ?? 'System' }}</span>
                            </td>
                            <td data-label="Role">
                                <span class="badge rounded-pill {{ $log->role_color }}">
                                    {{ ucfirst($log->user->role ?? 'System') }}
                                </span>
                            </td>
                            <td data-label="Action">
                                <span class="badge rounded-pill {{ $log->action_color ?? 'bg-light text-dark' }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td data-label="Description" class="text-muted small text-wrap-break">
                                {{ $log->description }}
                            </td>
                            <td data-label="IP Address" class="font-monospace small text-truncate">{{ $log->ip_address }}</td>
                            <td data-label="Timestamp" class="{{ $view === 'archived' ? '' : 'text-lg-end' }} pe-lg-4 small text-muted">
                                {{ $log->created_at->timezone('Asia/Manila')->format('M d, Y • h:i A') }}
                            </td>

                            @if($view === 'archived')
                            <td data-label="Actions" class="text-lg-end pe-lg-4">
                                <form action="{{ route('admin.logs.restore', $log->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success rounded-pill px-3">
                                        Restore
                                    </button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No logs found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-3 d-flex justify-content-center">
        {{ $logs->appends(request()->query())->links() }}
    </div>
</div>

{{-- Confirmation Modals --}}
@if($view === 'archived')
<div class="modal fade" id="restoreAllLogsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body p-4 text-center">
                <i class="bi bi-arrow-counterclockwise text-success fs-1 mb-3 d-block"></i>
                <h5 class="fw-bold">Restore All?</h5>
                <p class="text-muted small">Move all archived activity logs back to the active list.</p>
                <form action="{{ route('admin.logs.restore-all') }}" method="POST">
                    @csrf
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success rounded-pill fw-bold">Yes, Restore All</button>
                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@else
<div class="modal fade" id="archiveLogsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body p-4 text-center">
                <i class="bi bi-archive text-danger fs-1 mb-3 d-block"></i>
                <h5 class="fw-bold">Archive All?</h5>
                <p class="text-muted small">Move all current activity logs to the archive center.</p>
                <form action="{{ route('admin.logs.archive') }}" method="POST">
                    @csrf
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger rounded-pill fw-bold">Yes, Archive All</button>
                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
