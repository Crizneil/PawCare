<!-- resources/views/partials/_register_pet_modal.blade.php -->
<div class="modal fade" id="registerPetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center">
                    <div class="bg-orange-subtle p-2 rounded-3 me-3">
                        <i data-lucide="paw-print" class="text-orange"></i>
                    </div>
                    <h5 class="fw-bold text-dark m-0">Pet Registration</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <form action="{{ route('pet-owner.pets.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block btn-camera-trigger" style="cursor: pointer;">
                            <img id="petImagePreview" src="https://ui-avatars.com/api/?name=Pet&background=fce7d6&color=ff6600"
                                class="rounded-circle border border-4 border-white shadow"
                                style="width: 120px; height: 120px; object-fit: cover;">
                            <label for="petImageInput" class="position-absolute bottom-0 end-0 bg-orange text-white rounded-circle p-2 shadow-sm" style="cursor: pointer; pointer-events: none;">
                                <i data-lucide="camera" style="width: 18px; height: 18px;"></i>
                            </label>
                            <input type="file" name="image" id="petImageInput" class="d-none" accept="image/*">
                            <input type="hidden" name="image_base64" id="pet_add_image_base64">
                        </div>
                        <p class="small text-muted mt-2">Click the photo to change</p>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Pet Name</label>
                        <input type="text" name="name" class="form-control rounded-pill bg-light border-0 px-3" placeholder="Buddy" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="small fw-bold text-muted mb-1">Species</label>
                            <select name="species" id="speciesSelect" class="form-select rounded-pill bg-light border-0 px-3" required>
                                <option value="" selected disabled>Select Species</option>
                                <option value="Dog">Dog</option>
                                <option value="Cat">Cat</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold text-muted mb-1">Gender</label>
                            <select name="gender" class="form-select rounded-pill bg-light border-0 px-3" required>
                                <option value="" selected disabled>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>

                    <div id="breedContainer" class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Breed</label>
                        <select name="breed" id="breedSelect" class="form-select rounded-pill bg-light border-0 px-3" required>
                            <option value="" selected disabled>Select a species first</option>
                        </select>
                    </div>

                    <div id="otherSpeciesContainer" class="mb-3 d-none">
                        <label class="small fw-bold text-muted mb-1">Specify Breed</label>
                        <input type="text" name="other_species" id="otherSpeciesInput" class="form-control rounded-pill bg-light border-0 px-3" placeholder="Enter pet breed">
                    </div>

                    <div class="mb-4">
                        <label class="small fw-bold text-muted mb-1">Birthdate</label>
                        <input type="date" name="birthday" class="form-control rounded-pill bg-light border-0 px-3" required>
                    </div>

                    <button type="submit" class="btn btn-orange w-100 rounded-pill fw-bold py-3 shadow-sm transition-all">
                        CONFIRM REGISTRATION
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('assets/js/pet-registration.js') }}?v={{ time() }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof initializePetBreedLogic === 'function') {
                initializePetBreedLogic({
                    speciesId: 'speciesSelect',
                    breedId: 'breedSelect',
                    breedContainerId: 'breedContainer',
                    otherContainerId: 'otherSpeciesContainer',
                    otherInputId: 'otherSpeciesInput'
                });
            } else {
                console.error("The pet-registration.js file was not loaded correctly.");
            }
        });
    </script>
@endpush
