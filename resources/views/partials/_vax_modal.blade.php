<div class="modal fade" id="updateVax{{ $pet->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            {{-- Updated route to match your preferred naming if necessary --}}
            <form action="{{ route('staff.vaccination.store', $pet->id) }}" method="POST">
                @csrf

                @php
                    // Logic: Find an approved appointment for today, or the most recent approved one
                    $currentApt = $pet->appointments
                        ->where('status', 'approved')
                        ->where('appointment_date', date('Y-m-d'))
                        ->first()
                        ?? $pet->appointments->where('status', 'approved')->first();
                @endphp

                <input type="hidden" name="pet_id" value="{{ $pet->id }}">
                <input type="hidden" name="appointment_id" value="{{ request('appointment_id') ?? ($currentApt->id ?? '') }}">

                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="fw-bold">New Vaccination: {{ $pet->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Vaccine</label>
                        <input type="text"
                            name="vaccine_name"
                            list="vaccineOptions{{ $pet->id }}"
                            class="form-control rounded-3 vax-name-input"
                            {{-- Pre-fill with the service type from the appointment --}}
                            value="{{ $latestApt->service_type ?? '' }}"
                            placeholder="Select or type vaccine..."
                            required>

                        <datalist id="vaccineOptions{{ $pet->id }}">
                            <option value="Anti-Rabies">
                            <option value="5-in-1">
                            <option value="4-in-1">
                            <option value="Deworming">
                        </datalist>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Date Administered</label>
                            <input type="date" name="date_administered" class="form-control rounded-3 vax-date-input" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Next Due Date</label>
                            <input type="date" name="next_due_date" class="form-control rounded-3 vax-due-input" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    {{-- Updated text to reflect completion --}}
                    <button type="submit" class="btn btn-orange rounded-pill px-4 shadow-sm">Save & Complete Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>
