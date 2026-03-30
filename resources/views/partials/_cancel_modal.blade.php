<div class="modal fade" id="cancelModal{{ $appointment->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.5rem;">
            <div class="modal-body p-4 text-center">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-trash3 text-danger fs-3"></i>
                </div>
                <h5 class="fw-bold text-dark">Cancel Appointment?</h5>
                <p class="text-muted small">Are you sure you want to cancel appointment <strong>#APT-{{ $appointment->id }}</strong>?</p>

                <form action="{{ route('pet-owner.appointments.cancel', $appointment->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger rounded-pill py-2 fw-bold shadow-sm">Yes, Cancel It</button>
                        <button type="button" class="btn btn-link text-muted btn-sm text-decoration-none fw-bold" data-bs-dismiss="modal">No, Keep It</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
