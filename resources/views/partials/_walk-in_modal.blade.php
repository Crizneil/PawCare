<div class="modal fade" id="walkInModal" tabindex="-1" role="dialog" aria-labelledby="walkInModalLabel" aria-hidden="true">
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

                            <div class="mb-4">
                                <label class="small fw-bold text-muted mb-2 d-block">Owner Status</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="owner_status" id="statusExisting" value="existing" checked autocomplete="off">
                                    <label class="btn btn-outline-orange rounded-start-pill py-2" for="statusExisting">Existing</label>

                                    <input type="radio" class="btn-check" name="owner_status" id="statusNew" value="new" autocomplete="off">
                                    <label class="btn btn-outline-orange rounded-end-pill py-2" for="statusNew">New</label>
                                </div>
                            </div>

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

                            <div id="newOwnerSection" style="display: none;">
                                {{-- Name Row --}}
                                <div class="row g-2 mb-3">
                                    <div class="col-md-5">
                                        <label class="small fw-bold text-muted mb-1">Last Name</label>
                                        <input type="text" name="last_name" class="form-control rounded-pill bg-white border-1" placeholder="e.g. Dela Cruz">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small fw-bold text-muted mb-1">First Name</label>
                                        <input type="text" name="first_name" class="form-control rounded-pill bg-white border-1" placeholder="e.g. Juan">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small fw-bold text-muted mb-1">M.I. <span class="text-lowercase fw-normal">(optional)</span></label>
                                        <input type="text" name="middle_initial" class="form-control rounded-pill bg-white border-1" placeholder="A." maxlength="2">
                                    </div>
                                </div>

                                {{-- Contact Row --}}
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <label class="small fw-bold text-muted mb-1">Mobile No.</label>
                                        <input type="text" name="phone" class="form-control rounded-pill bg-white border-1" placeholder="09xxxxxxxxx">
                                    </div>
                                    {{-- Email Container: Controlled by JS Toggle --}}
                                    <div class="col-md-6" id="emailContainer">
                                        <label class="small fw-bold text-muted mb-1">Email Address</label>
                                        <input type="email" name="email" id="newOwnerEmail" class="form-control rounded-pill bg-white border-1" placeholder="email@example.com">
                                    </div>
                                </div>

                                {{-- Home Address Section --}}
                                <label class="small fw-bold text-muted mb-2 ps-2">Home Address</label>
                                <div class="row g-2 mb-2">
                                    <div class="col-md-4">
                                        <input type="text" name="house_no" class="form-control rounded-pill bg-white border-1" placeholder="House No.">
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" name="street" class="form-control rounded-pill bg-white border-1" placeholder="Street Name">
                                    </div>
                                </div>
                                <div class="row g-2 mb-4">
                                    <div class="col-md-4">
                                        <input type="text" name="barangay" class="form-control rounded-pill bg-white border-1" placeholder="Barangay">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="city" class="form-control rounded-pill bg-white border-1" placeholder="City" value="Meycauayan City">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="province" class="form-control rounded-pill bg-white border-1" placeholder="Province" value="Bulacan">
                                    </div>
                                </div>

                                <div id="accountOptionSection" class="form-check form-switch p-3 bg-white border rounded-4 mb-3">
                                    <input class="form-check-input ms-1 shadow-none" type="checkbox" id="createAccountToggle" name="create_online_account" value="1" checked>
                                    <label class="form-check-label small fw-bold text-orange ps-3" for="createAccountToggle">Register Online Account?</label>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Pet & Appointment --}}
                        <div class="col-md-7 p-4 bg-white">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-orange-subtle p-2 rounded-circle me-3">
                                    <i data-lucide="dog" class="text-orange" size="20"></i>
                                </div>
                                <h5 class="fw-bold text-dark m-0">Pet & Visit Information</h5>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="small fw-bold text-muted mb-1">Pet Details</label>

                                    {{-- Existing Pets Checkbox Container --}}
                                    <div id="petNameSelectContainer" class="mb-3" style="display:block;">
                                        <div id="petCheckboxesContainer" class="row g-2 border rounded-4 p-3 bg-light" style="max-height: 250px; overflow-y: auto;">
                                            <p class="text-muted small mb-0 text-center py-3 w-100">Please select an owner first...</p>
                                        </div>
                                    </div>

                                    {{-- New Pet Input (Hidden by default) --}}
                                    <div id="petNameInputContainer" class="mb-3" style="display:none;">
                                        <input type="text" id="petNameInput" class="form-control rounded-pill bg-light border-0 px-3" placeholder="Enter new pet name">
                                    </div>
                                </div>

                                {{-- These fields now hide and auto-fill when "Existing" is checked --}}
                                <div id="petExtraDetailsRow" class="row g-2 m-0 p-0">
                                    <div class="col-6">
                                        <label class="small fw-bold text-muted mb-1">Birthday</label>
                                        <input type="date" name="birthday" id="walkinBirthdayInput" max="{{ date('Y-m-d') }}" class="form-control rounded-pill bg-light border-0 px-3" value="{{ date('Y-m-d') }}">
                                    </div>

                                    <div class="col-6">
                                        <label class="small fw-bold text-muted mb-1">Species</label>
                                        <select name="species" id="walkinSpeciesSelect" class="form-select rounded-pill bg-light border-0 px-3">
                                            <option value="" selected disabled>Select</option>
                                            <option value="Dog">Dog</option>
                                            <option value="Cat">Cat</option>
                                        </select>
                                        <input type="text" id="walkinSpeciesDisplay" class="form-control rounded-pill bg-light-subtle text-muted border-0 px-3 d-none" readonly>
                                    </div>

                                    <div class="col-6">
                                        <label class="small fw-bold text-muted mb-1">Gender</label>
                                        <select name="gender" id="walkinGenderSelect" class="form-select rounded-pill bg-light border-0 px-3">
                                            <option value="" selected disabled>Select</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                        <input type="text" id="walkinGenderDisplay" class="form-control rounded-pill bg-light-subtle text-muted border-0 px-3 d-none" readonly>
                                    </div>

                                    <div class="col-6">
                                        <label class="small fw-bold text-muted mb-1">Breed</label>
                                        <div id="walkinBreedContainer">
                                            <select name="breed" id="walkinBreedSelect" class="form-select rounded-pill bg-light border-0 px-3">
                                                <option value="" selected disabled>Select Breed</option>
                                            </select>
                                            <input type="text" id="walkinBreedDisplay" class="form-control rounded-pill bg-light-subtle text-muted border-0 px-3 d-none" readonly>
                                        </div>
                                        <div id="walkinOtherBreedContainer" class="mt-2 d-none">
                                            <input type="text" name="other_breed" id="walkinOtherBreedInput" class="form-control rounded-pill bg-light border-0 px-3" placeholder="Specify breed">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12"><hr class="my-2 opacity-25"></div>

                                {{-- Appointment Details --}}
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
                                <div class="col-12 mb-4">
                                    <label class="small fw-bold text-muted mb-1">Time Slot</label>
                                    <select name="schedule_time" id="walkinTimeSlot" class="form-select rounded-pill bg-light border-0 px-3" required>
                                        <option value="" selected disabled>Select date first...</option>
                                    </select>
                                </div>

                                {{-- Submit Button placed inside the right column row --}}
                                <div class="col-12 pt-2">
                                    <button type="submit" class="btn btn-orange w-100 rounded-pill py-3 shadow-sm fw-bold">CREATE APPOINTMENT</button>
                                </div>
                            </div>
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
    .ts-control {
        border-radius: 50px !important; /* Matches your rounded-pill style */
        padding: 10px 20px !important;
        border: 1px solid #dee2e6 !important;
    }

    .ts-wrapper.single .ts-control {
        background-color: #f8f9fa !important;
    }
    /* Container adjustment */
    #petCheckboxesContainer {
        max-height: 280px;
        overflow-y: auto;
        display: flex;
        flex-wrap: wrap;
        align-content: flex-start;
    }

    /* Card Styling */
    .pet-selection-card {
        background-color: #ffffff;
        cursor: pointer;
        border: 1px solid #e9ecef !important;
        transition: all 0.2s ease;
    }

    .pet-selection-card:hover {
        border-color: #fd7e14 !important;
        background-color: #fffaf5;
    }

    /* Highlight card when checkbox is checked */
    .pet-selection-card:has(.pet-checkbox:checked) {
        border-color: #fd7e14 !important;
        background-color: #fff4e6 !important;
        box-shadow: 0 2px 4px rgba(253, 126, 20, 0.1);
    }

    .cursor-pointer {
        cursor: pointer;
    }

    /* Custom Scrollbar for the pet list */
    #petCheckboxesContainer::-webkit-scrollbar {
        width: 5px;
    }
    #petCheckboxesContainer::-webkit-scrollbar-thumb {
        background: #dee2e6;
        border-radius: 10px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let tsOwner, tsPet, tsBreed; // Added tsBreed
    const ownerSearchSelect = document.getElementById('ownerSearchSelect');
    const petNameSelect = document.getElementById('petNameSelect');

    const speciesSelect = document.getElementById('walkinSpeciesSelect');
    const breedSelect = document.getElementById('walkinBreedSelect');
    const genderSelect = document.getElementById('walkinGenderSelect');
    const birthdayInput = document.getElementById('walkinBirthdayInput');

    const createAccountToggle = document.getElementById('createAccountToggle');
    const emailContainer = document.getElementById('emailContainer');
    const emailInput = document.getElementById('newOwnerEmail');

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

    // 1. Initialize TomSelect for Owner
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


    // 2. Initialize TomSelect for Pet Name
    if (petNameSelect && typeof TomSelect !== "undefined") {
        tsPet = new TomSelect('#petNameSelect', {
            create: false,
            placeholder: "Select pet from list...",
            allowEmptyOption: true,
            searchField: ['text']
        });
    }

    // 3. NEW: Initialize TomSelect for Breed (Searchable for New Owners)
    if (breedSelect && typeof TomSelect !== "undefined") {
        tsBreed = new TomSelect('#walkinBreedSelect', {
            create: true,
            placeholder: "Search or type breed...",
            allowEmptyOption: true,
            dropdownParent: 'body',
            onInitialize: function() {
                // This is the logic from your search owner that allows multiple searches
                this.control.addEventListener('click', () => {
                    if (this.items.length > 0) {
                        this.clear();
                        this.focus();
                    }
                });
            }
        });
    }

    // Modal Accessibility & Cleanup
    const walkInModalEl = document.getElementById('walkInModal');
    if (walkInModalEl) {
        walkInModalEl.addEventListener('show.bs.modal', function () {
            this.removeAttribute('aria-hidden');
        });

        walkInModalEl.addEventListener('hidden.bs.modal', function () {
            if (tsOwner) tsOwner.clear(true);
            if (tsPet) { tsPet.clear(true); tsPet.clearOptions(); }
            if (tsBreed) { tsBreed.clear(true); tsBreed.clearOptions(); }
            document.body.focus();
        });
    }

    function toggleSections() {
        const isExisting = statusExisting && statusExisting.checked;
        const extraDetailsRow = document.getElementById('petExtraDetailsRow');

        if (isExisting) {
            if (existingSection) existingSection.style.display = 'block';
            if (newSection) newSection.style.display = 'none';
            petNameSelectContainer.style.display = 'block';
            petNameInputContainer.style.display = 'none';
            extraDetailsRow.style.display = 'none'; // Hide manual inputs
            togglePetFields(true);
        } else {
            if (existingSection) existingSection.style.display = 'none';
            if (newSection) newSection.style.display = 'block';
            petNameSelectContainer.style.display = 'none';
            petNameInputContainer.style.display = 'block';
            extraDetailsRow.style.display = 'flex'; // Show manual inputs for new pet
            togglePetFields(false);
        }
    }

    function handleEmailVisibility() {
        if (!createAccountToggle || !emailContainer || !emailInput) return;

        // Only show if New Owner is selected AND toggle is checked
        if (statusNew.checked && createAccountToggle.checked) {
            emailContainer.style.setProperty('display', 'block', 'important');
            emailInput.required = true;
        } else {
            emailContainer.style.setProperty('display', 'none', 'important');
            emailInput.required = false;
            // emailInput.value = ''; // Optional: Clear if you want to wipe it when hidden
        }
    }

    // 5. ADD THE EVENT LISTENER
    if (createAccountToggle) {
        createAccountToggle.addEventListener('change', handleEmailVisibility);
    }

    function togglePetFields(isReadonly) {
        const fieldPairs = [
            { select: speciesSelect, display: speciesDisplay },
            { select: genderSelect, display: genderDisplay },
            { select: breedSelect, display: breedDisplay }
        ];

        fieldPairs.forEach(pair => {
            const parent = pair.select ? pair.select.parentElement : null;
            if (isReadonly) {
                if (pair.select) {
                    pair.select.style.setProperty('display', 'none', 'important');
                    if (parent) parent.querySelectorAll('.ts-wrapper').forEach(w => w.style.setProperty('display', 'none', 'important'));
                }
                if (pair.display) {
                    pair.display.style.setProperty('display', 'block', 'important');
                    pair.display.classList.remove('d-none');
                    if (pair.select) {
                        pair.display.value = pair.select.options[pair.select.selectedIndex]?.text || pair.select.value || '';
                    }
                }
            } else {
                if (pair.select) {
                    pair.select.style.setProperty('display', 'block', 'important');
                    if (parent) parent.querySelectorAll('.ts-wrapper').forEach(w => w.style.setProperty('display', 'block', 'important'));
                }
                if (pair.display) {
                    pair.display.style.setProperty('display', 'none', 'important');
                    pair.display.classList.add('d-none');
                }
            }
        });

        const otherContainer = document.getElementById('walkinOtherBreedContainer');
        if (otherContainer && isReadonly) otherContainer.style.setProperty('display', 'none', 'important');

        if (birthdayInput) {
            birthdayInput.classList.toggle('bg-light-subtle', isReadonly);
            birthdayInput.classList.toggle('text-muted', isReadonly);
            birthdayInput.style.pointerEvents = isReadonly ? 'none' : 'auto';
            isReadonly ? birthdayInput.setAttribute('tabindex', '-1') : birthdayInput.removeAttribute('tabindex');
        }
    }

    if (statusExisting && statusNew) {
        statusExisting.addEventListener('change', toggleSections);
        statusNew.addEventListener('change', toggleSections);
        toggleSections();
    }

    if (tsOwner) {
        tsOwner.on('change', async function(userId) {
            const checkboxContainer = document.getElementById('petCheckboxesContainer');
            const extraDetailsRow = document.getElementById('petExtraDetailsRow');

            if (!userId || statusNew.checked) return;

            checkboxContainer.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-orange" role="status"></div><span class="ms-2 small text-muted">Fetching pets...</span></div>';

            try {
                const response = await fetch(`/staff/owner/${userId}/pets`);
                const pets = await response.json();

                if (pets.length > 0) {
                    checkboxContainer.innerHTML = '';
                    checkboxContainer.className = "row g-2 border rounded-4 p-3 bg-light";
                    // Hide the manual detail inputs for existing pets as they are now in the checkbox
                    extraDetailsRow.style.display = 'none';

                    pets.forEach(pet => {
                        const col = document.createElement('div');
                        col.className = 'col-md-6 mb-2'; // Two columns to save space
                        col.innerHTML = `
                            <div class="pet-selection-card position-relative border rounded-3 p-2 h-100 transition-all">
                                <input class="form-check-input pet-checkbox position-absolute top-50 start-0 ms-2 translate-middle-y shadow-none"
                                    type="checkbox"
                                    name="pet_ids[]"
                                    value="${pet.id}"
                                    id="pet_${pet.id}"
                                    data-species="${pet.species}"
                                    data-gender="${pet.gender}"
                                    data-breed="${pet.breed}"
                                    data-birthday="${pet.birthday || ''}">

                                <label class="form-check-label ms-4 ps-2 d-block cursor-pointer" for="pet_${pet.id}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-dark small">${pet.name}</span>
                                        <span class="badge bg-orange-subtle text-orange px-2 py-1" style="font-size: 10px;">${pet.species}</span>
                                    </div>
                                    <div class="text-muted" style="font-size: 11px;">
                                        <span class="text-truncate d-block">${pet.breed}</span>
                                        <span class="d-block">${pet.gender} • ${pet.birthday ? pet.birthday : 'No DOB'}</span>
                                    </div>
                                </label>
                            </div>
                        `;
                        checkboxContainer.appendChild(col);
                    });

                    // Refresh Lucide icons for the new elements
                    if(typeof lucide !== 'undefined') lucide.createIcons();

                    // Logic to sync hidden fields when a checkbox is clicked
                    checkboxContainer.querySelectorAll('.pet-checkbox').forEach(cb => {
                        cb.addEventListener('change', function() {
                            if(this.checked) {
                                speciesSelect.value = this.dataset.species;
                                genderSelect.value = this.dataset.gender;
                                birthdayInput.value = this.dataset.birthday;
                                // Set display values for the hidden select elements
                                speciesDisplay.value = this.dataset.species;
                                genderDisplay.value = this.dataset.gender;
                                breedDisplay.value = this.dataset.breed;
                            }
                        });
                    });

                } else {
                    checkboxContainer.innerHTML = '<p class="text-danger small text-center py-3">No registered pets found for this owner.</p>';
                    extraDetailsRow.style.display = 'flex'; // Show if no pets found so they can add one
                }
            } catch (error) {
                checkboxContainer.innerHTML = '<p class="text-danger small text-center py-3">Error loading pets.</p>';
            }
        });
    }

    if (tsPet) {
        tsPet.on('change', function(value) {
            const petData = tsPet.options[value];
            if (petData && petData.info) {
                try {
                    const pet = JSON.parse(petData.info);
                    speciesSelect.value = pet.species;
                    genderSelect.value = pet.gender;
                    if (pet.birthday) birthdayInput.value = pet.birthday.split(' ')[0];

                    speciesSelect.dispatchEvent(new Event('change'));

                    setTimeout(() => {
                        const targetBreed = pet.breed ? pet.breed.trim() : '';
                        const otherContainer = document.getElementById('walkinOtherBreedContainer');
                        const otherInput = document.getElementById('walkinOtherBreedInput');

                        if (tsBreed) {
                            // Add the breed as an option if it's custom, then set it
                            tsBreed.addOption({value: targetBreed, text: targetBreed});
                            tsBreed.setValue(targetBreed);
                        } else {
                            breedSelect.value = targetBreed;
                        }

                        // MANUALLY handle visibility here to prevent the external logic from breaking it
                        const isOther = targetBreed.toLowerCase() === 'other';
                        if (otherContainer) {
                            if (isOther) {
                                otherContainer.style.setProperty('display', 'block', 'important');
                                otherContainer.classList.remove('d-none');
                                // If the pet has a specific 'other_breed' value saved, put it in the input
                                if (otherInput) otherInput.value = pet.other_breed || '';
                            } else {
                                otherContainer.style.setProperty('display', 'none', 'important');
                                otherContainer.classList.add('d-none');
                            }
                        }

                        setTimeout(() => togglePetFields(true), 50);
                    }, 600);
                } catch (e) { console.error("Auto-fill failed", e); }
            } else if (!value && statusNew.checked) {
                togglePetFields(false);
            }
        });
    }

    // Form Submit Validation
    const walkinForm = document.getElementById('walkinForm');
    if (walkinForm) {
        walkinForm.addEventListener('submit', function (e) {
            if (statusExisting.checked) {
                if (tsOwner && !tsOwner.getValue()) { e.preventDefault(); tsOwner.focus(); return; }
                if (tsPet && !tsPet.getValue()) { e.preventDefault(); tsPet.focus(); return; }
            }
            if (statusNew.checked && !petNameInput.value.trim()) {
                e.preventDefault(); petNameInput.focus(); return;
            }
        });
    }

    // Time Slot Logic
    const walkinDate = document.getElementById('walkinDate');
    const walkinService = document.getElementById('walkinService');
    const walkinTimeSlot = document.getElementById('walkinTimeSlot');
    const allSlots = ["08:00", "08:30", "09:00", "09:30", "10:00", "10:30", "11:00", "11:30", "13:00", "13:30", "14:00", "14:30", "15:00", "15:30", "16:00", "16:30"];

    async function updateAvailableSlots() {
        const date = walkinDate.value;
        const service = walkinService.value;
        if (!date) return;
            try {
            const response = await fetch(`/staff/appointments/booked-slots?date=${date}`);
            const bookedSlots = await response.json();

            walkinTimeSlot.innerHTML = '<option value="" selected disabled>Select available time...</option>';

            allSlots.forEach((slot, index) => {
                const isBooked = bookedSlots.includes(slot);
                let isExcludedByKapon = false;

                if (service === 'kapon') {
                    const isNextBooked = allSlots[index + 1] ? bookedSlots.includes(allSlots[index + 1]) : true;
                    if (isNextBooked || slot === "11:30" || slot === "16:30") isExcludedByKapon = true;
                }

                // HIDE if booked or if Kapon logic excludes it
                if (!isBooked && !isExcludedByKapon) {
                    const option = document.createElement('option');
                    option.value = slot;
                    option.textContent = formatTimeDisplay(slot);
                    walkinTimeSlot.appendChild(option);
                }
            });
        } catch (error) { console.error("Error fetching slots:", error); }
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

    if (typeof initializePetBreedLogic === 'function') {
        initializePetBreedLogic({
            speciesId: 'walkinSpeciesSelect',
            breedId: 'walkinBreedSelect',
            breedContainerId: 'walkinBreedContainer',
            otherContainerId: 'walkinOtherBreedContainer',
            otherInputId: 'walkinOtherBreedInput',
            swapNameOnOther: false
        }, tsBreed);
    }
});
</script>
