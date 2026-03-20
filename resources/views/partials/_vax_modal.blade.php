<div class="modal fade" id="updateVax{{ $pet->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('staff.vaccination.store', $pet->id) }}" method="POST">
                @csrf
                @php
                    $currentApt = $pet->appointments->where('status', 'approved')->first();
                @endphp
                <input type="hidden" name="appointment_id" value="{{ $currentApt->id ?? '' }}">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="fw-bold">New Vaccination: {{ $pet->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Vaccine Brand/Name</label>
                        <input type="text" name="vaccine_name" list="vaccineOptions" class="form-control rounded-3 vax-name-input" placeholder="Choose or type vaccine name..." required>
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
                    <button type="submit" class="btn btn-orange rounded-pill px-4 shadow-sm">Log Shot</button>
                </div>
            </form>
        </div>
    </div>
</div>
