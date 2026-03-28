<div class="modal fade" id="editPetModal{{ $pet->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Edit {{ $pet->name }}'s Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pet-owner.update-pet', $pet->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body text-center">
                    {{-- Image Upload Section --}}
                    <div class="mb-4">
                        <label for="imageUpload{{ $pet->id }}" class="position-relative d-inline-block" style="cursor: pointer;">
                            <img id="petPreview{{ $pet->id }}"
                                src="{{ $pet->image_url ? asset('storage/' . $pet->image_url) : 'https://ui-avatars.com/api/?name=' . urlencode($pet->name) . '&background=fdfbf7&color=d35400' }}"
                                class="rounded-circle shadow-sm border border-3 border-white"
                                style="width: 80px; height: 80px; object-fit: cover;"
                                onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($pet->name) }}&background=fdfbf7&color=d35400'">

                            {{-- Icon Overlay --}}
                            <div class="bg-orange rounded-circle position-absolute bottom-0 end-0 p-2 text-white shadow-sm"
                                style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                <i data-lucide="camera" style="width: 16px; height: 16px;"></i>
                            </div>
                        </label>

                        {{-- Use image/* for the broadest support of both Camera and Gallery --}}
                        <input type="file" name="image" id="imageUpload{{ $pet->id }}"
                            class="d-none pet-image-input"
                            accept="image/*">

                        <p class="small text-muted mt-2 mb-0">Tap photo to upload</p>
                    </div>

                    <div class="text-start">
                        <div class="mb-3">
                            <label class="small fw-bold text-muted text-uppercase">Pet Name</label>
                            <input type="text" name="name" class="form-control rounded-pill bg-light border-0 px-3"
                                value="{{ $pet->name }}" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="small fw-bold text-muted text-uppercase">Species</label>
                                <select name="species" id="speciesSelect{{ $pet->id }}" class="form-select rounded-pill bg-light border-0 px-3">
                                    <option value="Dog" {{ strtolower($pet->species) == 'dog' ? 'selected' : '' }}>Dog</option>
                                    <option value="Cat" {{ strtolower($pet->species) == 'cat' ? 'selected' : '' }}>Cat</option>
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="small fw-bold text-muted text-uppercase">Breed</label>
                                <select name="breed" id="breedSelect{{ $pet->id }}" class="form-select rounded-pill bg-light border-0 px-3">
                                    <option value="" disabled>Select Breed</option>
                                    @if($pet->breed)
                                        {{-- This ensures the recorded breed is visible immediately --}}
                                        <option value="{{ $pet->breed }}" selected>{{ $pet->breed }}</option>
                                    @endif
                                </select>
                            </div>
                            {{-- Container for "Other" breed if needed --}}
                            <div class="col-12 mb-3 d-none" id="otherBreedContainer{{ $pet->id }}">
                                <label class="small fw-bold text-muted text-uppercase">Specify Breed</label>
                                <input type="text" name="other_breed" id="otherBreedInput{{ $pet->id }}" class="form-control rounded-pill bg-light border-0 px-3" placeholder="Enter breed name">
                            </div>

                            <div class="col-12 mb-3">
                                <label class="small fw-bold text-muted text-uppercase">Health Status</label>
                                <select name="status" class="form-select rounded-pill bg-light border-0 px-3">
                                    <option value="ACTIVE" {{ $pet->status == 'ACTIVE' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="INACTIVE" {{ $pet->status == 'INACTIVE' ? 'selected' : '' }}>Inactive
                                    </option>
                                    <option value="DECEASED" {{ $pet->status == 'DECEASED' ? 'selected' : '' }}>Deceased
                                        (Hide from Bookings)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-orange rounded-pill px-4 fw-bold shadow-sm">Save
                        Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof initializePetBreedLogic === 'function') {
        initializePetBreedLogic({
            speciesId: 'speciesSelect{{ $pet->id }}',
            breedId: 'breedSelect{{ $pet->id }}',
            breedContainerId: null,
            otherContainerId: 'otherBreedContainer{{ $pet->id }}',
            otherInputId: 'otherBreedInput{{ $pet->id }}',
            initialSpecies: '{{ $pet->species }}',
            initialBreed: '{{ $pet->breed }}',
            otherBreedValue: '{{ $pet->other_breed ?? '' }}'
        });
    }
});
</script>
