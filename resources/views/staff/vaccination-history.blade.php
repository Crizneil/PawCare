@extends('layout.admin')

@section('page_title', 'Vaccination History | Staff')

@section('content')
<div class="container-fluid p-4 fade-in">
    {{-- UPDATED HEADER WITH REPORT BUTTON --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <h2 class="fw-bold text-dark mb-0">Vaccination History</h2>

        <div class="dropdown">
            <button class="btn btn-outline-dark rounded-pill px-4 py-2 shadow-sm fw-bold dropdown-toggle" type="button" id="reportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-file-earmark-pdf me-1"></i> Generate Report
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li>
                    {{-- Print / Preview --}}
                    <a class="dropdown-item" href="{{ route('staff.generate-report', array_merge(request()->all(), ['type' => 'vaccination_history'])) }}" target="_blank">
                        <i data-lucide="printer" class="me-2" style="width: 14px;"></i> Print / Preview
                    </a>
                </li>
                <li>
                    {{-- Download PDF --}}
                    <a class="dropdown-item" href="{{ route('staff.generate-report', array_merge(request()->all(), ['type' => 'vaccination_history', 'pdf' => 1])) }}">
                        <i data-lucide="download" class="me-2" style="width: 14px;"></i> Download PDF
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- 1. FILTER NOTICE --}}
    @if(request('pet_id'))
        @php $petName = $history->first()->pet->name ?? 'this pet'; @endphp
        <div class="alert {{ $history->isEmpty() ? 'alert-warning' : 'alert-primary' }} border-0 shadow-sm rounded-4 d-flex justify-content-between align-items-center mb-4 p-3">
            <div class="small">
                <i data-lucide="{{ $history->isEmpty() ? 'alert-triangle' : 'info' }}" class="me-2 text-primary" style="width: 20px;"></i>
                @if($history->isEmpty())
                    No vaccination history found for this pet.
                @else
                    Currently showing records for: <strong>{{ $petName }}</strong>
                @endif
            </div>
            <a href="{{ route('staff.vaccination-history') }}" class="btn btn-sm {{ $history->isEmpty() ? 'btn-warning' : 'btn-primary' }} rounded-pill px-3 shadow-sm">
                <i data-lucide="x-circle" class="me-1" style="width:14px;"></i> View All
            </a>
        </div>
    @endif

    {{-- 1.5 QUICK SHORTCUTS --}}
    <div class="d-flex justify-content-center mb-4">
        <div class="bg-light rounded-pill p-1 shadow-sm d-inline-flex border">
            @php $currentPeriod = request('period'); @endphp
            <a class="btn rounded-pill px-4 fw-bold {{ $currentPeriod == 'today' ? 'btn-primary text-white shadow-sm' : 'btn-light text-muted border-0' }}"
                href="{{ route('staff.vaccination-history', ['period' => 'today']) }}">Today</a>

            <a class="btn rounded-pill px-4 fw-bold {{ $currentPeriod == 'weekly' ? 'btn-primary text-white shadow-sm' : 'btn-light text-muted border-0' }}"
                href="{{ route('staff.vaccination-history', ['period' => 'weekly']) }}">Weekly</a>

            <a class="btn rounded-pill px-4 fw-bold {{ $currentPeriod == 'monthly' ? 'btn-primary text-white shadow-sm' : 'btn-light text-muted border-0' }}"
                href="{{ route('staff.vaccination-history', ['period' => 'monthly']) }}">Monthly</a>

            <a class="btn rounded-pill px-4 fw-bold {{ !$currentPeriod ? 'btn-primary text-white shadow-sm' : 'btn-light text-muted border-0' }}"
                href="{{ route('staff.vaccination-history') }}">All Time</a>
        </div>
    </div>

    {{-- 2. ADVANCED FILTER BAR --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('staff.vaccination-history') }}" method="GET" class="row g-3 align-items-center">
                {{-- Keep the period state if filtering by staff/vaccine --}}
                @if(request('period'))
                    <input type="hidden" name="period" value="{{ request('period') }}">
                @endif

                {{-- Custom Date Range (The "Two Dates") --}}
                <div class="col-12 col-md-4 col-xl-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-0 rounded-start-pill text-muted small">Range:</span>
                        <input type="date" name="start_date" class="form-control border-0 bg-light py-2" value="{{ request('start_date') }}">
                        <input type="date" name="end_date" class="form-control border-0 bg-light py-2 rounded-end-pill" value="{{ request('end_date') }}">
                    </div>
                </div>

                {{-- Staff Filter --}}
                <div class="col-6 col-md-2 col-xl-2">
                    <select name="staff_id" class="form-select form-select-sm border-0 bg-light rounded-pill shadow-sm px-3 py-2" onchange="this.form.submit()">
                        <option value="">All Staff</option>
                        @foreach($staffList as $staff)
                            <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Vaccine Filter --}}
                <div class="col-6 col-md-2 col-xl-2">
                    <select name="vaccine_name" class="form-select form-select-sm border-0 bg-light rounded-pill shadow-sm px-3 py-2" onchange="this.form.submit()">
                        <option value="">All Vaccines</option>
                        @foreach($vaccineList as $vax)
                            <option value="{{ $vax->name }}" {{ request('vaccine_name') == $vax->name ? 'selected' : '' }}>{{ $vax->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-auto ms-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm">
                        <i data-lucide="search" class="me-1" style="width:14px;"></i> Apply Filters
                    </button>
                    <a href="{{ route('staff.vaccination-history') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-4 shadow-sm">
                        <i data-lucide="refresh-cw" class="me-1" style="width:14px;"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- 3. HISTORY TABLE --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 custom-mobile-table">
                <thead class="bg-light text-secondary small fw-bold text-uppercase">
                    <tr>
                        <th class="ps-4 py-3">Date</th>
                        <th>Pet Name</th>
                        <th>Vaccine</th>
                        <th>Batch</th>
                        <th>Staff</th>
                        <th>Next Due</th>
                        <th class="text-end pe-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $record)
                        @php
                            $today = \Carbon\Carbon::today();
                            $dueDate = $record->next_due_date ? \Carbon\Carbon::parse($record->next_due_date) : null;

                            $statusLabel = 'Up to Date';
                            $statusClass = 'bg-success-subtle text-success border-success';

                            if ($dueDate) {
                                $daysUntilDue = $today->diffInDays($dueDate, false);
                                if ($daysUntilDue < 0) {
                                    $statusLabel = 'Overdue';
                                    $statusClass = 'bg-danger-subtle text-danger border-danger';
                                } elseif ($daysUntilDue <= 14) {
                                    $statusLabel = 'Due Soon';
                                    $statusClass = 'bg-warning-subtle text-warning border-warning';
                                }
                            }
                        @endphp
                        <tr>
                            <td class="ps-4" data-label="Date">
                                <div class="fw-medium text-dark">
                                    {{ \Carbon\Carbon::parse($record->date_administered)->format('M d, Y') }}
                                </div>
                            </td>

                            <td data-label="Pet Name">
                                <div class="fw-bold text-primary">{{ $record->pet->name }}</div>
                                <small class="text-muted">ID: #{{ $record->pet_id }}</small>
                            </td>

                            <td data-label="Vaccine">
                                <span class="badge bg-blue-light text-primary px-3 rounded-pill border border-primary-subtle">
                                    {{ $record->vaccine_name }}
                                </span>
                            </td>

                            <td data-label="Batch">
                                <code class="text-secondary small fw-bold">{{ $record->batch_no ?? 'N/A' }}</code>
                            </td>

                            <td data-label="Staff">
                                <div class="small fw-medium">
                                    <i data-lucide="user-check" class="text-muted me-1 d-none d-md-inline" style="width:14px;"></i>
                                    {{ $record->staff->name ?? 'System' }}
                                </div>
                            </td>

                            <td data-label="Next Due">
                                <span class="small @if($statusLabel === 'Overdue') text-danger fw-bold @else text-dark @endif">
                                    {{ $record->next_due_date ? \Carbon\Carbon::parse($record->next_due_date)->format('M d, Y') : '--' }}
                                </span>
                            </td>

                            <td class="text-end pe-4" data-label="Status">
                                <span class="badge rounded-pill border {{ $statusClass }} px-3 py-1 shadow-xs" style="min-width: 90px;">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i data-lucide="calendar-x" class="mb-2 opacity-50" style="width: 48px; height: 48px;"></i>
                                    <p class="mb-0">No vaccination records found for this selection.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
