<div class="modal fade" id="walkInModal" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-orange p-4">
                <div class="d-flex align-items-center">
                    <div class="bg-white p-2 rounded-3 me-3">
                        <i data-lucide="footprints" class="text-orange"></i>
                    </div>
                    <h4 class="fw-bold text-white m-0">Walk-in Appointment Desk</h4>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('staff.appointments.store') }}" method="POST" id="walkinForm" novalidate>
                @csrf
                <div class="modal-body p-0">
                    <div class="row g-0">
                        {{-- Left Column: Owner Information --}}
                        <div class="col-md-5 bg-light p-4 border-end">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-orange-subtle p-2 rounded-circle me-3">
                                    <i data-lucide="user" class="text-orange" size="20"></i>
                                </div>
                                <h5 class="fw-bold text-dark m-0">Owner Details</h5>
                            </div>
                            
                            {{-- 1. Owner Status Selection --}}
                            <div class="mb-4">
                                <label class="small fw-bold text-muted mb-2 d-block">Owner Status</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="owner_status" id="statusExisting" value="existing" checked autocomplete="off">
                                    <label class="btn btn-outline-orange rounded-start-pill py-2" for="statusExisting">Existing</label>

                                    <input type="radio" class="btn-check" name="owner_status" id="statusNew" value="new" autocomplete="off">
                                    <label class="btn btn-outline-orange rounded-end-pill py-2" for="statusNew">New</label>
                                </div>
                            </div>

                            {{-- 2. IF EXISTING: Search Owner --}}
                            <div id="existingOwnerSection" class="mb-4">
                                <label class="small fw-bold text-muted mb-1">Search Owner</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text bg-light border-0"><i data-lucide="search" size="18"></i></span>
                                    <select name="user_id" id="ownerSearchSelect" class="form-select bg-light border-0 px-3">
                                        <option value="" disabled selected>Search owner...</option>
                                        @foreach($owners as $owner)
                                            <option value="{{ $owner->id }}" data-email="{{ $owner->email }}" data-phone="{{ $owner->phone }}">
                                                {{ $owner->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- 3. IF NEW OWNER: Registration Fields --}}
                            <div id="newOwnerSection" style="display: none;">
                                <div class="row g-2 mb-2">
                                    <div class="col-6"><input type="text" name="first_name" class="form-control rounded-pill bg-light border-0" placeholder="First Name"></div>
                                    <div class="col-6"><input type="text" name="last_name" class="form-control rounded-pill bg-light border-0" placeholder="Last Name"></div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-6"><input type="text" name="phone" class="form-control rounded-pill bg-white border-1" placeholder="Mobile #"></div>
                                    <div class="col-6"><input type="email" name="email" class="form-control rounded-pill bg-white border-1" placeholder="Email Address"></div>
                                </div>
                                <label class="small fw-bold text-muted mb-2 ps-2">Home Address</label>
                                <input type="text" name="street" class="form-control rounded-pill bg-white border-1 mb-2" placeholder="Street, Barangay">
                                <input type="text" name="city" class="form-control rounded-pill bg-light border-1 mb-4" value="Meycauayan City, Bulacan" readonly>
                                
                                <div id="accountOptionSection" class="form-check form-switch p-3 bg-white border rounded-4 mb-3">
                                    <input class="form-check-input ms-1 shadow-none" type="checkbox" id="createAccountToggle" name="create_online_account" value="1" checked>
                                    <label class="form-check-label small fw-bold text-orange ps-3" for="createAccountToggle">Register Online Account?</label>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Pet & Appointment --}}
                        <div class="col-md-7 p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-orange-subtle p-2 rounded-circle me-3">
                                    <i data-lucide="dog" class="text-orange" size="20"></i>
                                </div>
                                <h5 class="fw-bold text-dark m-0">Pet & Visit Information</h5>
                            </div>
                            
                            <div class="row g-3">
                                {{-- Pet Detail Row 1 --}}
                                <div class="col-6">
                                    <label class="small fw-bold text-muted mb-1">Pet Name</label>
                                    <div id="petNameSelectContainer">
                                        <select id="petNameSelect" class="form-select rounded-pill bg-light border-0 px-3">
                                            <option value="">Select pet...</option>
                                        </select>
                                    </div>
                                    <div id="petNameInputContainer" style="display: none;">
                                        <input type="text" id="petNameInput" name="pet_name" class="form-control rounded-pill bg-light border-0 px-3" placeholder="Enter name...">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold text-muted mb-1">Birthday</label>
                                    <input type="date" name="birthday" id="walkinBirthdayInput" max="{{ date('Y-m-d') }}" class="form-control rounded-pill bg-light border-0 px-3" value="{{ date('Y-m-d') }}" required>
                                </div>

                                {{-- Pet Detail Row 2 --}}
                                <div id="walkinSpeciesCol" class="col-4">
                                    <label class="small fw-bold text-muted mb-1">Species</label>
                                    <select name="species" id="walkinSpeciesSelect" class="form-select rounded-pill bg-light border-0 px-1 text-center" required>
                                        <option value="" selected disabled>Select</option>
                                        <option value="Dog">Dog</option>
                                        <option value="Cat">Cat</option>
                                    </select>
                                    <input type="text" id="walkinSpeciesDisplay" class="form-control rounded-pill bg-light-subtle text-muted border-0 px-3 d-none text-center" readonly>
                                </div>
                                <div id="walkinGenderCol" class="col-4">
                                    <label class="small fw-bold text-muted mb-1">Gender</label>
                                    <select name="gender" id="walkinGenderSelect" class="form-select rounded-pill bg-light border-0 px-1 text-center" required>
                                        <option value="" selected disabled>Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                    <input type="text" id="walkinGenderDisplay" class="form-control rounded-pill bg-light-subtle text-muted border-0 px-3 d-none text-center" readonly>
                                </div>
                                <div id="walkinBreedCol" class="col-4">
                                    <label class="small fw-bold text-muted mb-1 ps-2">Breed</label>
                                    <div id="walkinBreedContainer">
                                        <select name="breed" id="walkinBreedSelect" class="form-select rounded-pill bg-light border-0 px-1 text-center" required>
                                            <option value="" selected disabled>Select</option>
                                        </select>
                                        <input type="text" id="walkinBreedDisplay" class="form-control rounded-pill bg-light-subtle text-muted border-0 px-3 d-none text-center" readonly>
                                    </div>
                                    <div id="walkinOtherBreedContainer" class="mt-2 d-none">
                                        <input type="text" name="other_breed" id="walkinOtherBreedInput" class="form-control rounded-pill bg-light border-0 px-3" placeholder="Specify breed">
                                    </div>
                                </div>

                                <div class="col-12"><hr class="my-2 opacity-25"></div>

                                {{-- Appointment Row --}}
                                <div class="col-4">
                                    <label class="small fw-bold text-muted mb-1">Date</label>
                                    <input type="date" name="schedule_date" min="{{ date('Y-m-d') }}" id="walkinDate" class="form-control rounded-pill bg-light border-0 px-3" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-8">
                                    <label class="small fw-bold text-muted mb-1">Service</label>
                                    <select name="service_type" id="walkinService" class="form-select rounded-pill bg-light border-0 px-3" required>
                                        <option value="">Select Service</option>
                                        <option value="Anti-Rabies">Anti-Rabies</option>
                                        <option value="5in1">5-in-1</option>
                                        <option value="4in1">4-in-1 (Cat)</option>
                                        <option value="Deworming">Deworming</option>
                                        <option value="Check-up">Check-up</option>
                                        <option value="Kapon">Kapon</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="small fw-bold text-muted mb-1">Time Slot</label>
                                    <select name="schedule_time" id="walkinTimeSlot" class="form-select rounded-pill bg-light border-0 px-3" required>
                                        <option value="" selected disabled>Select date first...</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                <div class="modal-footer border-0 p-4">
                    <button type="submit" class="btn btn-orange w-100 rounded-pill py-3 shadow-sm fw-bold">CREATE APPOINTMENT</button>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    let tsOwner, tsPet;
    const ownerSearchSelect = document.getElementById('ownerSearchSelect');
    const petNameSelect = document.getElementById('petNameSelect');
    
    // Define pet detail fields early for visibility in all functions
    const speciesSelect = document.getElementById('walkinSpeciesSelect');
    const breedSelect = document.getElementById('walkinBreedSelect');
    const genderSelect = document.getElementById('walkinGenderSelect');
    const birthdayInput = document.getElementById('walkinBirthdayInput');
    
    const statusExisting = document.getElementById('statusExisting');
    const statusNew = document.getElementById('statusNew');
    const existingSection = document.getElementById('existingOwnerSection');
    const newSection = document.getElementById('newOwnerSection');
    const petNameInputContainer = document.getElementById('petNameInputContainer');
    const petNameSelectContainer = document.getElementById('petNameSelectContainer');
    const petNameInput = document.getElementById('petNameInput');
    const speciesDisplay = document.getElementById('walkinSpeciesDisplay');
    const genderDisplay = document.getElementById('walkinGenderDisplay');
    const breedDisplay = document.getElementById('walkinBreedDisplay');

    // Initialize TomSelect for Owner
    if (ownerSearchSelect && typeof TomSelect !== "undefined") {
        tsOwner = new TomSelect('#ownerSearchSelect', {
            create: false,
            searchField: ['text', 'email', 'phone'],
            sortField: { field: "text", direction: "asc" },
            placeholder: "Type name or email to search...",
            allowEmptyOption: true,
            dropdownParent: 'body',
            render: {
                option: function(data, escape) {
                    const subText = data.email ? data.email : (data.phone ? data.phone : 'No Contact');
                    return `<div class="py-2 px-3">
                                <div class="fw-bold text-dark">${escape(data.text)}</div>
                                <div class="text-muted small">${escape(subText)}</div>
                            </div>`;
                },
                item: function(data, escape) {
                    const subText = data.email ? data.email : (data.phone ? data.phone : 'No Contact');
                    return `<div class="item d-flex align-items-center gap-2">
                                <span class="fw-bold text-dark">${escape(data.text)}</span>
                                <span class="text-muted small">(${escape(subText)})</span>
                            </div>`;
                }
            },
            onInitialize: function() {
                this.control.addEventListener('click', () => {
                    if (this.items.length > 0) {
                        this.clear();
                        this.focus();
                    }
                });
            }
        });
    }
    // FIX: Modal Accessibility & Cleanup Logic
        const walkInModalEl = document.getElementById('walkInModal');
        if (walkInModalEl) {
            walkInModalEl.addEventListener('show.bs.modal', function () {
                this.removeAttribute('aria-hidden');
            });

            walkInModalEl.addEventListener('hidden.bs.modal', function () {
                if (tsOwner) tsOwner.clear(true);
                if (tsPet) {
                    tsPet.clear(true);
                    tsPet.clearOptions();
                }
                document.body.focus();
            });
        }

    // Initialize TomSelect for Pet Name
    if (petNameSelect && typeof TomSelect !== "undefined") {
        tsPet = new TomSelect('#petNameSelect', {
            create: false, // Changed to false as we have a separate input for new pets
            placeholder: "Select pet from list...",
            allowEmptyOption: true,
            searchField: ['text'],
            render: {
                option: function(data, escape) {
                    return `<div class="py-2 px-3"><div class="fw-bold text-dark">${escape(data.text)}</div></div>`;
                },
                item: function(data, escape) {
                    return `<div class="item"><span class="fw-bold text-dark">${escape(data.text)}</span></div>`;
                }
            }
        });
    }

    // --- Toggle Logic for New vs Existing ---

    function toggleSections() {
        if (statusExisting.checked) {
            existingSection.style.setProperty('display', 'block', 'important');
            newSection.style.setProperty('display', 'none', 'important');

            // Show Select, Hide Input
            petNameSelectContainer.style.display = 'block';
            petNameInputContainer.style.display = 'none';
            petNameSelect.name = 'pet_name';
            petNameSelect.required = true;
            petNameInput.removeAttribute('name');
            petNameInput.required = false;

            // Reset details for fresh start
            togglePetFields(true); // Default to locked for existing
            
            newSection.querySelectorAll('input, select').forEach(i => i.required = false);
        } else {
            existingSection.style.setProperty('display', 'none', 'important');
            newSection.style.setProperty('display', 'block', 'important');

            // Hide Select, Show Input
            petNameSelectContainer.style.display = 'none';
            petNameInputContainer.style.display = 'block';
            petNameInput.name = 'pet_name';
            petNameInput.required = true;
            petNameSelect.removeAttribute('name');
            petNameSelect.required = false;

            if (tsOwner) tsOwner.clear();
            if (tsPet) tsPet.clearOptions();

            // Reset Pet Details for New Owner
            petNameInput.value = '';
            speciesSelect.value = '';
            genderSelect.value = '';
            birthdayInput.value = "{{ date('Y-m-d') }}";
            togglePetFields(false); // Editable for new
            
            if (typeof breedSelect !== 'undefined' && breedSelect) {
                breedSelect.innerHTML = '<option value="" selected disabled>Select breed...</option>';
            }
            ['first_name', 'last_name', 'phone'].forEach(name => {
                const el = newSection.querySelector(`[name="${name}"]`);
                if (el) el.required = true;
            });
        }
    }

    function togglePetFields(isReadonly) {
        const fieldPairs = [
            { select: speciesSelect, display: speciesDisplay },
            { select: genderSelect, display: genderDisplay },
            { select: breedSelect, display: breedDisplay }
        ];

        const speciesCol = document.getElementById('walkinSpeciesCol');
        const genderCol = document.getElementById('walkinGenderCol');
        const breedCol = document.getElementById('walkinBreedCol');

        if (isReadonly) {
            // Ensure breed column is visible for display
            if (breedCol) breedCol.style.setProperty('display', 'block', 'important');
            // Restore col-4 size for all three
            if (speciesCol) { speciesCol.classList.remove('col-6'); speciesCol.classList.add('col-4'); }
            if (genderCol) { genderCol.classList.remove('col-6'); genderCol.classList.add('col-4'); }
        } else {
            // SHOW breed column for new pets
            if (breedCol) breedCol.style.setProperty('display', 'block', 'important');
            // RESTORE col-4 size
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
                    // Show the text name of the selected option or raw value
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

        // Breed-specific container logic (Other breed)
        const otherContainer = document.getElementById('walkinOtherBreedContainer');
        if (otherContainer && isReadonly) {
            otherContainer.style.setProperty('display', 'none', 'important');
            otherContainer.classList.add('d-none');
        }

        // Birthday remains as is but readonly
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

    if (statusExisting && statusNew) {
        statusExisting.addEventListener('change', toggleSections);
        statusNew.addEventListener('change', toggleSections);
        toggleSections();
    }

    // (Moved to top)

    if (tsOwner) {
        tsOwner.on('change', async function(userId) {
            if (tsPet) {
                tsPet.clear();
                tsPet.clearOptions();
            }
            if (!userId) return;

            try {
                // Correct Route: /staff/owner/{id}/pets
                const response = await fetch(`/staff/owner/${userId}/pets`);
                const pets = await response.json();

                if (pets.length > 0 && tsPet) {
                    const options = pets.map(pet => ({
                        value: pet.id, // Use ID as value for unique tracking
                        text: pet.name,
                        info: JSON.stringify(pet)
                    }));
                    tsPet.addOptions(options);
                    tsPet.open();
                }
            } catch (error) {
                console.error("Error fetching pets:", error);
            }
        });
    }

   if (tsPet) {
    tsPet.on('change', function(value) {
        // Find the option data directly from TomSelect's internal storage
        const petData = tsPet.options[value];
        
        if (petData && petData.info) {
            try {
                const pet = JSON.parse(petData.info);
                
                // 1. Set basic fields
                speciesSelect.value = pet.species;
                genderSelect.value = pet.gender;
                
                if (pet.birthday) {
                    birthdayInput.value = pet.birthday.split(' ')[0];
                }

                // 2. Trigger species change and wait for breed list
                speciesSelect.dispatchEvent(new Event('change'));

                setTimeout(() => {
                    const targetBreed = pet.breed ? pet.breed.trim() : '';
                    let foundMatch = false;
                    for (let i = 0; i < breedSelect.options.length; i++) {
                        if (breedSelect.options[i].value === targetBreed || 
                            breedSelect.options[i].text.includes(targetBreed)) {
                            breedSelect.value = breedSelect.options[i].value;
                            foundMatch = true;
                            break;
                        }
                    }

                    const otherContainer = document.getElementById('walkinOtherBreedContainer');
                    const otherInput = document.getElementById('walkinOtherBreedInput');

                    otherContainer.classList.add('d-none');
                    otherInput.value = targetBreed;

                    // 4. Lock fields AFTER all data is populated
                    setTimeout(() => {
                        togglePetFields(true);
                    }, 50);
                }, 600);

            } catch (e) {
                console.error("Auto-fill parsing failed", e);
            }
        } else if (!value) {
            // If cleared, make editable if it's a new pet context
            if (statusNew.checked) togglePetFields(false);
        }
    });
}
    // --- Form Submit Validation ---
    const walkinForm = document.getElementById('walkinForm');
    if (walkinForm) {
        walkinForm.addEventListener('submit', function (e) {
            // Validate Owner
            if (statusExisting.checked && tsOwner && !tsOwner.getValue()) {
                e.preventDefault();
                tsOwner.wrapper.classList.add('border', 'border-danger');
                tsOwner.focus();
                return false;
            }
            // Validate Pet (Existing)
            if (statusExisting.checked && tsPet && !tsPet.getValue()) {
                e.preventDefault();
                tsPet.wrapper.classList.add('border', 'border-danger');
                tsPet.focus();
                return false;
            }
            // Validate Pet (New)
            if (statusNew.checked && !petNameInput.value.trim()) {
                e.preventDefault();
                petNameInput.classList.add('border-danger');
                petNameInput.focus();
                return false;
            }
        });
    }

    // --- Time Slot Logic ---
    const walkinDate = document.getElementById('walkinDate');
    const walkinService = document.getElementById('walkinService');
    const walkinTimeSlot = document.getElementById('walkinTimeSlot');
    const morningSlots = ["08:00", "08:30", "09:00", "09:30", "10:00", "10:30", "11:00", "11:30"];
    const afternoonSlots = ["13:00", "13:30", "14:00", "14:30", "15:00", "15:30", "16:00", "16:30"];
    const allSlots = [...morningSlots, ...afternoonSlots];

    async function updateAvailableSlots() {
        const date = walkinDate.value;
        const service = walkinService.value;
        if (!date) return;

        try {
            const response = await fetch(`/staff/appointments/booked-slots?date=${date}`);
            const bookedSlots = await response.json();
            walkinTimeSlot.innerHTML = '<option value="" selected disabled>Select time...</option>';

            allSlots.forEach((slot, index) => {
                const isBooked = bookedSlots.includes(slot);
                let isDisabled = isBooked;

                if (service === 'kapon') {
                    const nextSlot = allSlots[index + 1];
                    const isNextBooked = nextSlot ? bookedSlots.includes(nextSlot) : true;
                    if (isNextBooked || slot === "11:30" || slot === "16:30") isDisabled = true;
                }

                const option = document.createElement('option');
                option.value = slot;
                option.textContent = formatTimeDisplay(slot) + (isBooked ? ' (Booked)' : '');
                option.disabled = isDisabled;
                walkinTimeSlot.appendChild(option);
            });
        } catch (error) {
            console.error("Error fetching slots:", error);
        }
    }

    function formatTimeDisplay(time) {
        const [h, m] = time.split(':');
        let hour = parseInt(h);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour > 12 ? hour - 12 : (hour === 0 ? 12 : hour);
        let endM = parseInt(m) + 30;
        let endH = hour;
        if (endM === 60) { endM = "00"; endH++; }
        const displayEndH = endH > 12 ? endH - 12 : (endH === 0 ? 12 : endH);
        return `${displayHour}:${m} - ${displayEndH}:${endM.toString().padStart(2, '0')} ${ampm}`;
    }

    walkinDate.addEventListener('change', updateAvailableSlots);
    walkinService.addEventListener('change', updateAvailableSlots);

    // Initial breed logic
    if (typeof initializePetBreedLogic === 'function') {
        initializePetBreedLogic({
            speciesId: 'walkinSpeciesSelect',
            breedId: 'walkinBreedSelect',
            breedContainerId: 'walkinBreedContainer',
            otherContainerId: 'walkinOtherBreedContainer',
            otherInputId: 'walkinOtherBreedInput',
            swapNameOnOther: false
        });
    }
});
</script>
