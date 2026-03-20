<div class="modal fade" id="editPetModal{{ $pet->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">Update Pet: {{ $pet->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.pets.update', $pet->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body px-4">
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Pet Image</label>
                        <div class="input-group">
                            <input type="file" name="image" class="form-control rounded-start-pill bg-light border-0">
                            <button type="button" class="btn btn-outline-secondary btn-camera-trigger rounded-end-pill px-3">
                                <i data-lucide="camera" style="width: 18px;"></i>
                            </button>
                        </div>
                        <input type="hidden" name="image_base64" id="pet_image_base64_{{ $pet->id }}">
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Pet Name</label>
                        <input type="text" name="name" class="form-control rounded-pill bg-light border-0"
                            value="{{ $pet->name }}">
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Breed</label>
                        <input type="text" name="breed" class="form-control rounded-pill bg-light border-0"
                            value="{{ $pet->breed }}">
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted">Status</label>
                        <select name="status" class="form-select rounded-pill bg-light border-0">
                            <option value="ACTIVE" {{ $pet->status == 'ACTIVE' ? 'selected' : '' }}>Active</option>
                            <option value="INACTIVE" {{ $pet->status == 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                            <option value="DECEASED" {{ $pet->status == 'DECEASED' ? 'selected' : '' }}>Deceased</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-orange rounded-pill px-4 fw-bold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deletePetModal{{ $pet->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body text-center p-4">
                <i class="bi bi-exclamation-circle text-danger fs-1 mb-3 d-block"></i>
                <h5 class="fw-bold">Remove Record?</h5>
                <p class="text-muted small">Are you sure you want to delete <strong>{{ $pet->name }}</strong>? This
                    action cannot be undone.</p>

                <form action="{{ route('admin.pets.destroy', $pet->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger rounded-pill fw-bold">Yes, Delete</button>
                        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
