<div class="modal fade" id="adminConfirmDoneModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                {{-- Decorative Icon --}}
                <div class="bg-success-subtle text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-check2-circle fs-2"></i>
                </div>

                <h5 class="fw-bold">Mark as Service Rendered?</h5>
                <p class="text-muted small">Are you sure this appointment is finished? This will update the pet's vaccination status.</p>

                <form id="adminDoneForm" method="POST">
                    @csrf
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success rounded-pill fw-bold py-2 shadow-sm">
                            Yes, it's Done
                        </button>
                        <button type="button" class="btn btn-light rounded-pill fw-bold text-muted py-2" data-bs-dismiss="modal">
                            No, go back
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
