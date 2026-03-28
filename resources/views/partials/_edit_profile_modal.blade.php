<div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">Update Profile Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ auth()->user()->role == 'admin' ? route('admin.profile.update') : route('pet-owner.profile.update') }}"
                    method="POST"
                    enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        {{-- Profile Picture --}}
                        <div class="col-md-12 mb-2">
                            <label class="form-label small fw-bold text-muted text-uppercase">Profile Picture</label>
                            <div class="input-group">
                                <input type="file" name="profile_image" class="form-control rounded-start-pill border-light bg-light">
                                <button type="button" class="btn btn-outline-secondary btn-camera-trigger rounded-end-pill px-3">
                                    <i data-lucide="camera" style="width: 18px;"></i>
                                </button>
                            </div>
                            <input type="hidden" name="profile_image_base64" id="profile_image_base64">
                            <small class="text-muted ms-2">Leave blank to keep current picture.</small>
                        </div>

                        {{-- Name & Email --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Full Name</label>
                            <input type="text" name="name" class="form-control rounded-pill border-light bg-light" value="{{ auth()->user()->name }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-pill border-light bg-light" value="{{ auth()->user()->email }}" required>
                        </div>

                        {{-- Mobile & Gender - FIXED CONSISTENCY --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Mobile Number</label>
                            <input type="text" name="phone" class="form-control rounded-pill border-light bg-light" value="{{ auth()->user()->phone }}" maxlength="11">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Gender</label>
                            <select name="gender" class="form-select rounded-pill border-light bg-light shadow-none">
                                <option value="" selected disabled>Select Gender</option>
                                <option value="male" {{ auth()->user()->gender == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ auth()->user()->gender == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>

                        {{-- Address Fields --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">House Number</label>
                            <input type="text" name="house_no" class="form-control rounded-pill border-light bg-light" value="{{ auth()->user()->house_no }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Street</label>
                            <input type="text" name="street" class="form-control rounded-pill border-light bg-light" value="{{ auth()->user()->street }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Barangay</label>
                            <input type="text" name="barangay" class="form-control rounded-pill border-light bg-light" value="{{ auth()->user()->barangay }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">City</label>
                            <input type="text" name="city" class="form-control rounded-pill border-light bg-light" value="{{ auth()->user()->city }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Province</label>
                            <input type="text" name="province" class="form-control rounded-pill border-light bg-light" value="{{ auth()->user()->province }}" required>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-orange rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
