<div class="modal fade" id="addPetModal" tabindex="-1" aria-labelledby="addPetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background: #2c3e50;">
                <h5 class="modal-title fw-bold" id="addPetModalLabel">Register New Pet</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.pets.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="image_base64" id="pet_add_image_base64">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- Searchable Owner Dropdown --}}
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Select Owner</label>
                            <select name="user_id" id="ownerSearchSelect" class="form-select" required>
                                <option value="" selected disabled>Type name or email to search...</option>
                                @foreach($owners as $owner)
                                    <option value="{{ $owner->id }}">{{ $owner->name }} ({{ $owner->email }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Need a new owner? <a href="{{ route('admin.owners') }}" class="text-primary text-decoration-none">Register them here</a>.</small>
                        </div>
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img id="petImagePreview" src="https://ui-avatars.com/api/?name=Pet&background=fce7d6&color=ff6600"
                                    class="rounded-circle border border-4 border-white shadow"
                                    style="width: 120px; height: 120px; object-fit: cover;">
                                <label class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 shadow-sm btn-camera-trigger" style="cursor: pointer; width: 36px; height: 36px;" title="Take Photo">
                                    <i data-lucide="camera" style="width: 20px; height: 20px;"></i>
                                </label>
                                <label for="petImageInput" class="position-absolute bottom-0 start-0 bg-orange text-white rounded-circle p-2 shadow-sm" style="cursor: pointer; width: 36px; height: 36px;" title="Upload Photo">
                                    <i data-lucide="upload" style="width: 20px; height: 20px;"></i>
                                    <input type="file" name="image" id="petImageInput" class="d-none" accept="image/*">
                                </label>
                            </div>
                            <p class="small text-muted mt-2">Click the camera to upload a photo</p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Pet Name</label>
                            <input type="text" class="form-control bg-light" name="name" required placeholder="e.g., Bella" value="{{ old('name') }}">
                        </div>

                        {{-- Added Gender Field --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Gender</label>
                            <select name="gender" id="genderSelect" class="form-select bg-light" required>
                                <option value="" selected disabled>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Species</label>
                            <select name="species" id="speciesSelect" class="form-select bg-light" required>
                                <option value="" selected disabled>Select Species</option>
                                <option value="Dog">Dog</option>
                                <option value="Cat">Cat</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Breed</label>
                            <select name="breed" id="breedSelect" class="form-select bg-light" required disabled>
                                <option value="" selected disabled>Select Breed</option>
                            </select>
                            <input type="text" class="form-control bg-light mt-2 d-none" name="other_breed" id="otherBreedInput" placeholder="Specify breed">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Birthdate</label>
                            <input type="date" class="form-control bg-light" name="birthdate" required value="{{ old('birthdate') }}" max="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white px-4 fw-bold" style="background:#ff6b6b;">Register Pet</button>
                </div>
            </form>
        </div>
    </div>
</div>
