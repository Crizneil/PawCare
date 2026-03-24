<div class="modal fade" id="addAppointmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark m-0">Create New Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('admin.appointments.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <h6 class="text-orange fw-bold mb-3"><i class="bi bi-person-fill me-2"></i>Owner Information
                        </h6>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="small fw-bold text-muted">Select Client</label>
                                <select name="user_id" class="form-control rounded-pill bg-light border-0" required>
                                    <option value="">Select Owner...</option>
                                    @foreach($owners as $owner)
                                        <option value="{{ $owner->id }}">{{ $owner->name }}
                                            ({{ $owner->phone ?? 'No Phone' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr class="text-muted opacity-25">

                    <div class="mb-4">
                        <h6 class="text-orange fw-bold mb-3"><i class="bi bi-paw-fill me-2"></i>Pet Information</h6>
                        <p class="small text-muted mb-2">If the pet name doesn't exist for this client, a new record
                            will be created automatically.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted">Pet Name</label>
                                <input type="text" name="pet_name" class="form-control rounded-pill bg-light border-0"
                                    placeholder="Buddy" required>
                            </div>
                            <div class="col-md-3">
                                <label class="small fw-bold text-muted">Species</label>
                                <select name="species" id="adminApptSpecies"
                                    class="form-control rounded-pill bg-light border-0" required>
                                    <option value="Dog">Dog</option>
                                    <option value="Cat">Cat</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small fw-bold text-muted">Gender</label>
                                <select name="gender" class="form-control rounded-pill bg-light border-0" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Unknown">Unknown</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6" id="adminApptBreedContainer">
                                <label class="small fw-bold text-muted">Breed</label>
                                <select name="breed" id="adminApptBreedSelect"
                                    class="form-control rounded-pill bg-light border-0" required>
                                    <option value="" selected disabled>Select species first</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-none" id="adminApptOtherBreedContainer">
                                <label class="small fw-bold text-muted">Specify Breed</label>
                                <input type="text" name="other_breed" id="adminApptOtherBreedInput"
                                    class="form-control rounded-pill bg-light border-0" placeholder="Enter breed">
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted">Birthdate (Estimate)</label>
                                <input type="date" name="birthday" class="form-control rounded-pill bg-light border-0"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <hr class="text-muted opacity-25">

                    <div class="mb-4">
                        <h6 class="text-orange fw-bold mb-3"><i class="bi bi-clock-fill me-2"></i>Appointment Details
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted">Date</label>
                                <input type="date" name="appointment_date"
                                    class="form-control rounded-pill bg-light border-0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted">Preferred Time</label>
                                <input type="time" name="appointment_time"
                                    class="form-control rounded-pill bg-light border-0" required>
                            </div>
                            <div class="col-md-12">
                                <select name="service_type" class="form-control rounded-pill bg-light border-0"
                                    required>
                                    <option value="">Select Service</option>
                                    <option value="Anti-Rabies">Anti-Rabies Vaccination</option>
                                    <option value="5in1">5-in-1 Vaccination</option>
                                    <option value="4in1">4-in-1 Vaccination (Cat)</option>
                                    <option value="Deworming">Deworming</option>
                                    <option value="Check-up">Check-up</option>
                                    <option value="Kapon">Kapon</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-orange w-100 rounded-pill fw-bold py-3 mt-2 shadow-sm">
                        CONFIRM NEW APPOINTMENT
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('assets/js/pet-registration.js') }}?v={{ time() }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Breed Logic for Admin New Appointment (walk-in-style)
            if (typeof initializePetBreedLogic === 'function') {
                initializePetBreedLogic({
                    speciesId: 'adminApptSpecies',
                    breedId: 'adminApptBreedSelect',
                    breedContainerId: 'adminApptBreedContainer',
                    otherContainerId: 'adminApptOtherBreedContainer',
                    otherInputId: 'adminApptOtherBreedInput'
                });
            } else {
                console.error("The pet-registration.js file was not loaded correctly for admin appointment modal.");
            }
        });
    </script>
@endpush
