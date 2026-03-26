<!-- Add Owner Modal -->
<div class="modal fade" id="addOwnerModal" tabindex="-1" aria-labelledby="addOwnerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background: #2c3e50;">
                <h5 class="modal-title fw-bold" id="addOwnerModalLabel">Add New Pet Owner</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ $submitRoute }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0" style="background: #eef2f5; color: #333;">
                        <i class="fi flaticon-info me-2 text-primary"></i>
                        <strong>Note:</strong> Creating an account will automatically generate a secure password and email it to the owner.
                    </div>

                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            <label class="form-label fw-bold d-block text-primary border-bottom pb-2 text-start">Profile Photo</label>
                            
                            <!-- Profile Picture Preview -->
                            <div class="mb-3 position-relative d-inline-block">
                                <img id="ownerProfilePreview" src="{{ asset('assets/images/user-default.png') }}" class="rounded-circle border shadow-sm" style="width: 120px; height: 120px; object-fit: cover;" alt="Profile Preview" onerror="this.src='https://ui-avatars.com/api/?name=Owner&background=random'">
                                
                                <label for="ownerProfileImage" class="position-absolute bottom-0 end-0 bg-orange text-white rounded-circle p-2 shadow" style="cursor: pointer; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                    <i data-lucide="camera" style="width: 16px;"></i>
                                </label>
                            </div>
                            
                            <input type="file" id="ownerProfileImage" name="profile_image" class="d-none" accept="image/*" onchange="previewOwnerImage(this)">
                            <small class="d-block text-muted">Upload a clear photo of the owner (Max: 2MB).</small>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 text-primary border-bottom pb-2">Personal Information</h6>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">First Name</label>
                            <input type="text" class="form-control bg-light" name="first_name" required placeholder="Juan" value="{{ old('first_name') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">M.I. <span class="text-muted fw-normal">(Opt)</span></label>
                            <input type="text" class="form-control bg-light" name="middle_initial" maxlength="2" placeholder="D." value="{{ old('middle_initial') }}">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Last Name</label>
                            <input type="text" class="form-control bg-light" name="last_name" required placeholder="Dela Cruz" value="{{ old('last_name') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Gender</label>
                            <select name="gender" class="form-select bg-light" required>
                                <option value="" selected disabled>Select Gender</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" class="form-control bg-light" name="email" required placeholder="juan@example.com" value="{{ old('email') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Mobile Number</label>
                            <input type="text" class="form-control bg-light" name="phone" required placeholder="09123456789" value="{{ old('phone') }}">
                        </div>
                    </div>

                    <h6 class="fw-bold mt-4 mb-3 text-primary border-bottom pb-2">Address Information</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Unit/House No.</label>
                            <input type="text" class="form-control bg-light" name="house_no" required placeholder="B1 L2" value="{{ old('house_no') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Street</label>
                            <input type="text" class="form-control bg-light" name="street" required placeholder="St. Mary St." value="{{ old('street') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Barangay</label>
                            <input type="text" class="form-control bg-light" name="barangay" required placeholder="e.g., Banga" value="{{ old('barangay') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">City/Municipality</label>
                            <input type="text" class="form-control bg-light" name="city" required value="Meycauayan" value="{{ old('city') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Province</label>
                            <input type="text" class="form-control bg-light" name="province" required value="Bulacan" value="{{ old('province') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white px-4 fw-bold" style="background:#ff6b6b;">Register Owner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function previewOwnerImage(input) {
        const preview = document.getElementById('ownerProfilePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = "https://ui-avatars.com/api/?name=Owner&background=random";
        }
    }
</script>
