<div class="modal fade" id="addAppointmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-orange p-4">
                <div class="d-flex align-items-center">
                    <div class="bg-white p-2 rounded-3 me-3">
                        <i class="bi bi-calendar-plus-fill text-orange fs-4"></i>
                    </div>
                    <h4 class="fw-bold text-white m-0">Create New Appointment</h4>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('admin.appointments.store') }}" method="POST">
                @csrf
                <div class="modal-body p-0">
                    <div class="row g-0">
                        {{-- Left Column: Owner --}}
                        <div class="col-md-5 bg-light p-4 border-end">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-orange-subtle p-2 rounded-circle me-3">
                                    <i class="bi bi-person-fill text-orange" style="font-size: 20px;"></i>
                                </div>
                                <h5 class="fw-bold text-dark m-0">Owner Selection</h5>
                            </div>

                            <div class="mb-4">
                                <label class="small fw-bold text-muted mb-2 ps-2">Search Owner</label>
                                <select name="user_id" id="adminOwnerSelect" class="form-select bg-white border-1 px-3" required>
                                    <option value="">Select owner...</option>
                                    @foreach($owners as $owner)
                                        <option value="{{ $owner->id }}" data-name="{{ $owner->name }}">
                                            {{ $owner->name }} ({{ $owner->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="p-4 bg-white border rounded-4">
                                <div class="d-flex align-items-center text-muted">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <small class="fw-bold">Owner info and pet history will be fetched automatically after selection.</small>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Pet & Details --}}
                        <div class="col-md-7 p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-orange-subtle p-2 rounded-circle me-3">
                                    <i class="bi bi-dog-fill text-orange" style="font-size: 20px;"></i>
                                </div>
                                <h5 class="fw-bold text-dark m-0">Pet & Appointment Details</h5>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="small fw-bold text-muted mb-1 ps-2">Pet Name</label>
                                    <div id="adminPetSelectContainer">
                                        <select id="adminPetSelect" class="form-select rounded-pill bg-light border-0" required>
                                            <option value="">Select pet...</option>
                                        </select>
                                    </div>
                                    <input type="hidden" name="pet_name" id="adminPetNameHidden">
                                    <input type="text" id="adminPetNameInput" class="form-control rounded-pill bg-light border-0 d-none" placeholder="New pet name">
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold text-muted mb-1 ps-2">Birthdate</label>
                                    <input type="date" name="birthday" id="adminApptBirthday" class="form-control rounded-pill bg-light border-0" value="{{ date('Y-m-d') }}" required>
                                </div>

                                <div id="adminApptSpeciesCol" class="col-4">
                                    <label class="small fw-bold text-muted mb-1 ps-2">Species</label>
                                    <select name="species" id="adminApptSpecies" class="form-select rounded-pill bg-light border-0 px-1 text-center" required>
                                        <option value="Dog">Dog</option>
                                        <option value="Cat">Cat</option>
                                    </select>
                                    <input type="text" id="adminApptSpeciesDisplay" class="form-control rounded-pill bg-light-subtle text-muted border-0 d-none text-center" readonly>
                                </div>
                                <div id="adminApptGenderCol" class="col-4">
                                    <label class="small fw-bold text-muted mb-1 ps-2">Gender</label>
                                    <select name="gender" id="adminApptGender" class="form-select rounded-pill bg-light border-0 px-1 text-center" required>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                    <input type="text" id="adminApptGenderDisplay" class="form-control rounded-pill bg-light-subtle text-muted border-0 d-none text-center" readonly>
                                </div>
                                <div id="adminApptBreedCol" class="col-4">
                                    <label class="small fw-bold text-muted mb-1 ps-2">Breed</label>
                                    <div id="adminApptBreedContainer">
                                        <select name="breed" id="adminApptBreedSelect" class="form-select rounded-pill bg-light border-0 px-1 text-center" required>
                                            <option value="" selected disabled>Select</option>
                                        </select>
                                        <input type="text" id="adminApptBreedDisplay" class="form-control rounded-pill bg-light-subtle text-muted border-0 d-none text-center" readonly>
                                    </div>
                                    <div id="adminApptOtherBreedContainer" class="mt-2 d-none">
                                        <input type="text" name="other_breed" id="adminApptOtherBreedInput" class="form-control rounded-pill bg-light border-0 px-3" placeholder="Specify breed">
                                    </div>
                                </div>

                                <div class="col-12"><hr class="my-2 opacity-25"></div>

                                <div class="col-4">
                                    <label class="small fw-bold text-muted mb-1 ps-2">Schedule Date</label>
                                    <input type="date" name="appointment_date" class="form-control rounded-pill bg-light border-0 px-3" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-3">
                                    <label class="small fw-bold text-muted mb-1 ps-2">Time</label>
                                    <input type="time" name="appointment_time" class="form-control rounded-pill bg-light border-0 px-3" required>
                                </div>
                                <div class="col-5">
                                    <label class="small fw-bold text-muted mb-1 ps-2">Service Type</label>
                                    <select name="service_type" id="adminServiceType" class="form-select rounded-pill bg-light border-0 px-3" required>
                                        <option value="">Select Service</option>
                                        <option value="Anti-Rabies">Anti-Rabies</option>
                                        <option value="5in1">5-in-1</option>
                                        <option value="4in1">4-in-1 (Cat)</option>
                                        <option value="Deworming">Deworming</option>
                                        <option value="Check-up">Check-up</option>
                                        <option value="Kapon">Kapon</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-orange w-100 rounded-pill fw-bold py-3 mt-4 shadow-sm">
                                CONFIRM NEW APPOINTMENT
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Nuclear Fix: Hide search bars (TomSelect/Select2) if underlying select is hidden */
    select.d-none + .ts-wrapper, 
    select[style*="display: none"] + .ts-wrapper,
    select.d-none + .select2-container, 
    select[style*="display: none"] + .select2-container,
    select.d-none ~ .select2-container, 
    select[style*="display: none"] ~ .select2-container {
        display: none !important;
    }
</style>

@push('scripts')
    <script src="{{ asset('assets/js/pet-registration.js') }}?v={{ time() }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let tsAdminOwner, tsAdminPet;
            const ownerSelect = document.getElementById('adminOwnerSelect');
            const petSelect = document.getElementById('adminPetSelect');
            const nameHidden = document.getElementById('adminPetNameHidden');
            const nameInput = document.getElementById('adminPetNameInput');
            
            const speciesSelect = document.getElementById('adminApptSpecies');
            const genderSelect = document.getElementById('adminApptGender');
            const birthdayInput = document.getElementById('adminApptBirthday');
            const breedSelect = document.getElementById('adminApptBreedSelect');
            
            const speciesDisplay = document.getElementById('adminApptSpeciesDisplay');
            const genderDisplay = document.getElementById('adminApptGenderDisplay');
            const breedDisplay = document.getElementById('adminApptBreedDisplay');

            function togglePetFields(isReadonly) {
                const fieldPairs = [
                    { select: speciesSelect, display: speciesDisplay },
                    { select: genderSelect, display: genderDisplay },
                    { select: breedSelect, display: breedDisplay }
                ];

                const speciesCol = document.getElementById('adminApptSpeciesCol');
                const genderCol = document.getElementById('adminApptGenderCol');
                const breedCol = document.getElementById('adminApptBreedCol');

                if (isReadonly) {
                    if (breedCol) breedCol.style.setProperty('display', 'block', 'important');
                    if (speciesCol) { speciesCol.classList.remove('col-6'); speciesCol.classList.add('col-4'); }
                    if (genderCol) { genderCol.classList.remove('col-6'); genderCol.classList.add('col-4'); }
                } else {
                    if (breedCol) breedCol.style.setProperty('display', 'block', 'important');
                    if (speciesCol) { speciesCol.classList.remove('col-6'); speciesCol.classList.add('col-4'); }
                    if (genderCol) { genderCol.classList.remove('col-6'); genderCol.classList.add('col-4'); }
                }

                fieldPairs.forEach(pair => {
                    if (isReadonly) {
                        if (pair.select) {
                            pair.select.style.setProperty('display', 'none', 'important');
                            pair.select.classList.add('d-none');
                            // ROBUST HIDE: Find TomSelect/Select2 wrapper anywhere in the parent
                            const parent = pair.select.parentElement;
                            if (parent) {
                                parent.querySelectorAll('.ts-wrapper, .select2-container').forEach(w => w.style.setProperty('display', 'none', 'important'));
                            }
                        }
                        if (pair.display) {
                            pair.display.style.setProperty('display', 'block', 'important');
                            pair.display.classList.remove('d-none');
                            if (pair.select && pair.select.selectedIndex >= 0 && pair.select.options[pair.select.selectedIndex].text !== 'Select') {
                                pair.display.value = pair.select.options[pair.select.selectedIndex].text;
                            } else if (pair.select) {
                                pair.display.value = pair.select.value || '';
                            }
                        }
                    } else {
                        if (pair.select) {
                            pair.select.style.setProperty('display', 'block', 'important');
                            pair.select.classList.remove('d-none');
                            // ROBUST SHOW: Find TomSelect/Select2 wrapper anywhere in the parent
                            const parent = pair.select.parentElement;
                            if (parent) {
                                parent.querySelectorAll('.ts-wrapper, .select2-container').forEach(w => w.style.setProperty('display', 'block', 'important'));
                            }
                        }
                        if (pair.display) {
                            pair.display.style.setProperty('display', 'none', 'important');
                            pair.display.classList.add('d-none');
                        }
                    }
                });

                if (birthdayInput) {
                    if (isReadonly) {
                        birthdayInput.classList.add('bg-light-subtle', 'text-muted');
                        birthdayInput.style.pointerEvents = 'none';
                        birthdayInput.setAttribute('tabindex', '-1');
                    } else {
                        birthdayInput.classList.remove('bg-light-subtle', 'text-muted');
                        birthdayInput.style.pointerEvents = 'auto';
                        birthdayInput.removeAttribute('tabindex');
                    }
                }
            }

            // 1. Initialize TomSelect for Owner
            if (ownerSelect && typeof TomSelect !== "undefined") {
                tsAdminOwner = new TomSelect('#adminOwnerSelect', {
                    create: false,
                    placeholder: "Search owner...",
                    allowEmptyOption: true
                });
            }

            // 2. Initialize TomSelect for Pet
            if (petSelect && typeof TomSelect !== "undefined") {
                tsAdminPet = new TomSelect('#adminPetSelect', {
                    create: true,
                    placeholder: "Select or type new pet name...",
                    onOptionAdd: function(value, data) {
                        document.getElementById('adminPetNameInput').value = value;
                        togglePetFields(false); // Enable fields if creating new
                    }
                });
            }

            // 3. Fetch Pets when Owner changes
            if (tsAdminOwner) {
                tsAdminOwner.on('change', async function(userId) {
                    if (tsAdminPet) {
                        tsAdminPet.clear();
                        tsAdminPet.clearOptions();
                    }
                    if (!userId) {
                        togglePetFields(false);
                        return;
                    }

                    try {
                        const response = await fetch(`/admin/owner/${userId}/pets`);
                        const pets = await response.json();
                        
                        if (tsAdminPet) {
                            const options = pets.map(pet => ({
                                value: pet.id,
                                text: pet.name,
                                info: JSON.stringify(pet)
                            }));
                            tsAdminPet.addOptions(options);
                            tsAdminPet.open();
                        }
                    } catch (error) {
                        console.error("Error fetching pets:", error);
                    }
                });
            }

            // 4. Auto-fill when Pet is selected
            if (tsAdminPet) {
                tsAdminPet.on('change', function(value) {
                    const petData = tsAdminPet.options[value];
                    
                    if (petData && petData.info) {
                        try {
                            const pet = JSON.parse(petData.info);
                            if (nameHidden) nameHidden.value = pet.id; // Send ID for existing
                            if (speciesSelect) speciesSelect.value = pet.species;
                            if (genderSelect) genderSelect.value = pet.gender;
                            if (pet.birthday && birthdayInput) birthdayInput.value = pet.birthday.split(' ')[0];
                            
                            if (speciesSelect) speciesSelect.dispatchEvent(new Event('change'));

                            setTimeout(() => {
                                const targetBreed = pet.breed ? pet.breed.trim() : '';
                                if (breedSelect) {
                                    for (let i = 0; i < breedSelect.options.length; i++) {
                                        if (breedSelect.options[i].value === targetBreed || 
                                            breedSelect.options[i].text.includes(targetBreed)) {
                                            breedSelect.value = breedSelect.options[i].value;
                                            break;
                                        }
                                    }
                                }
                                // Lock fields AFTER all data is populated
                                setTimeout(() => {
                                    togglePetFields(true);
                                }, 50);
                            }, 600);
                        } catch (e) {
                            console.error("Parse error", e);
                        }
                    } else {
                        // User typed a new name or cleared
                        if (nameHidden) nameHidden.value = value;
                        togglePetFields(false);
                    }
                });
            }

            // Breed Logic Initialization
            if (typeof initializePetBreedLogic === 'function') {
                initializePetBreedLogic({
                    speciesId: 'adminApptSpecies',
                    breedId: 'adminApptBreedSelect',
                    breedContainerId: 'adminApptBreedContainer',
                    otherContainerId: 'adminApptOtherBreedContainer',
                    otherInputId: 'adminApptOtherBreedInput'
                });
            }
        });
    </script>
@endpush
