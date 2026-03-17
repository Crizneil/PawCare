<div class="modal fade" id="viewVaxHistory{{ $pet->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 bg-light rounded-top-4 py-3 px-4">
                <h5 class="modal-title fw-bold">Vaccination History: {{ $pet->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle shadow-sm">
                        <thead class="bg-light">
                            <tr class="text-muted small uppercase">
                                <th class="ps-3">Vaccine / Batch</th>
                                <th>Administered By</th>
                                <th>Date Given</th>
                                <th>Next Due</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pet->vaccinations as $vax)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark">{{ $vax->vaccine_name }}</div>
                                    <small class="text-muted">Batch: {{ $vax->batch_no ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    {{-- Assuming vaccination model has a 'staff' or 'user' relationship --}}
                                    <div class="small fw-medium">{{ $vax->staff->name ?? 'System Admin' }}</div>
                                </td>
                                <td class="small">
                                    {{ \Carbon\Carbon::parse($vax->date_administered)->format('M d, Y') }}
                                </td>
                                <td>
                                    @if($vax->next_due_date)
                                        <span class="text-dark small">{{ \Carbon\Carbon::parse($vax->next_due_date)->format('M d, Y') }}</span>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusClass = 'bg-secondary-subtle text-secondary';
                                        $statusLabel = 'No Date';

                                        if ($vax->next_due_date) {
                                            $dueDate = \Carbon\Carbon::parse($vax->next_due_date);
                                            $now = \Carbon\Carbon::now();
                                            $daysRemaining = $now->diffInDays($dueDate, false); // false allows negative numbers for overdue

                                            if ($daysRemaining < 0) {
                                                // Past the due date
                                                $statusClass = 'bg-danger-subtle text-danger border-danger';
                                                $statusLabel = 'Overdue';
                                            } elseif ($daysRemaining <= 30) {
                                                // Within 30 days of due date
                                                $statusClass = 'bg-warning-subtle text-warning border-warning';
                                                $statusLabel = 'Due Soon';
                                            } else {
                                                // More than 30 days remaining
                                                $statusClass = 'bg-success-subtle text-success border-success';
                                                $statusLabel = 'Up to Date';
                                            }
                                        }
                                    @endphp
                                    <span class="badge border px-2 {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                            </tr>
                            {{-- If there are remarks, show them in a small row below --}}
                            @if($vax->remarks)
                            <tr class="table-light">
                                <td colspan="5" class="ps-3 py-1">
                                    <small class="text-muted italic"><strong>Remark:</strong> {{ $vax->remarks }}</small>
                                </td>
                            </tr>
                            @endif
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x fs-2 d-block mb-2"></i>
                                    No historical records found for this pet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="updateVax{{ $pet->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form action="{{ route('admin.vaccinations.update', $pet->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="staff_id" value="{{ auth()->id() }}">
                <div class="modal-header border-0 bg-light rounded-top-4 py-3 px-4">
                    <h5 class="modal-title fw-bold">Update Record: {{ $pet->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Vaccine Type</label>
                        <input type="text" name="vaccine_name" class="form-control rounded-pill bg-light border-0"
                               value="{{ $pet->latestVaccination->vaccine_name ?? '' }}" placeholder="e.g. 5-in-1, Rabies">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Last Vaccination</label>
                            <input type="date" name="date_administered" class="form-control rounded-pill bg-light border-0"
                                   value="{{ $pet->latestVaccination->date_administered ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted">Next Due Date</label>
                            <input type="date" name="next_due_date" class="form-control rounded-pill bg-light border-0"
                                   value="{{ $pet->latestVaccination->next_due_date ?? '' }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-orange rounded-pill px-4 fw-bold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
