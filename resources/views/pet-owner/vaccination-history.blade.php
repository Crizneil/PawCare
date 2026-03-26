@extends('layout.admin')

@section('page_title', 'Vaccination History')

@section('content')
    <div class="container-fluid p-4">
        <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-0">
                {{ $selectedPet ? "Vaccination History for " . $selectedPet->name : "All Vaccination History" }}
            </h2>
            <p class="text-muted small">
                {{ $selectedPet ? "Specific records for " . $selectedPet->name : "Comprehensive record of your pets' immunizations." }}
            </p>
        </div>

        {{-- Show a 'Show All' button only if a pet is selected --}}
        @if($selectedPet)
            <a href="{{ route('pet-owner.vaccination-history') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i data-lucide="list" class="me-1" style="width: 14px;"></i> View All Pets
            </a>
        @endif
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                {{-- Added custom-mobile-table class --}}
                <table class="table table-hover align-middle mb-0 custom-mobile-table">
                    <thead class="bg-light">
                        <tr class="text-secondary text-uppercase small fw-bold">
                            <th class="ps-4 py-3">Pet Name</th>
                            <th>Vaccine</th>
                            <th>Date Administered</th>
                            <th>Next Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vaccinations as $record)
                            @php
                                $statusConfigs = [
                                    'overdue'          => ['class' => 'vax-danger-subtle', 'icon' => 'alert-circle', 'label' => 'Overdue'],
                                    'due_soon'         => ['class' => 'vax-due-soon',      'icon' => 'clock',        'label' => 'Due Soon'],
                                    'fully_vaccinated' => ['class' => 'vax-up-to-date',    'icon' => 'check-circle', 'label' => 'Up to Date'],
                                ];

                                $config = $statusConfigs[$record->status] ?? [
                                    'class' => 'bg-light text-muted border',
                                    'icon'  => 'shield',
                                    'label' => ucwords(str_replace('_', ' ', $record->status ?? 'Recorded'))
                                ];
                            @endphp
                            <tr>
                                {{-- Added data-labels for mobile view --}}
                                <td class="ps-4" data-label="Pet Name">
                                    <div class="fw-bold text-dark">{{ $record->pet->name }}</div>
                                </td>
                                <td class="fw-bold text-dark" data-label="Vaccine">
                                    {{ $record->vaccine_name }}
                                </td>
                                <td class="fw-bold small" data-label="Date Administered">
                                    {{ \Carbon\Carbon::parse($record->date_administered)->format('M d, Y') }}
                                </td>
                                <td data-label="Next Due Date">
                                    <span class="fw-bold small {{ $record->status === 'overdue' ? 'text-danger' : '' }}">
                                        {{ $record->next_due_date ? \Carbon\Carbon::parse($record->next_due_date)->format('M d, Y') : 'N/A' }}
                                    </span>
                                </td>
                                <td data-label="Status">
                                    <span class="badge {{ $config['class'] }} rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1">
                                        <i data-lucide="{{ $config['icon'] }}" style="width: 14px; height: 14px;"></i>
                                        {{ $config['label'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i data-lucide="shield-off" class="text-muted mb-2" style="width: 40px; height: 40px;"></i>
                                    <p class="text-muted mb-0">No vaccination records found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $vaccinations->links() }}
    </div>
</div>
@endsection
