<div class="modal fade" id="walkInModal" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center">
                    <div class="bg-orange-subtle p-2 rounded-3 me-3">
                        <i data-lucide="footprints" class="text-orange"></i>
                    </div>
                    <h5 class="fw-bold text-dark m-0">New Walk-in Patient</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('staff.appointments.store') }}" method="POST" id="walkinForm" novalidate>
                @csrf
                <div class="modal-body p-4">
                    {{-- 1. Owner Status Selection --}}
                    <div class="mb-4">
                        <label class="small fw-bold text-muted mb-2 d-block">Owner Status</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="owner_status" id="statusExisting" value="existing" checked autocomplete="off">
                            <label class="btn btn-outline-orange rounded-start-pill py-2" for="statusExisting">Existing Owner</label>

                            <input type="radio" class="btn-check" name="owner_status" id="statusNew" value="new" autocomplete="off">
                            <label class="btn btn-outline-orange rounded-end-pill py-2" for="statusNew">New Owner</label>
                        </div>
                    </div>

                    {{-- 2. IF EXISTING: Search Owner --}}
                    <div id="existingOwnerSection" class="mb-4">
                        <label class="small fw-bold text-muted mb-1">Search Owner</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-light border-0"><i data-lucide="search" size="18"></i></span>
                            <select name="user_id" id="ownerSearchSelect" class="form-select bg-light border-0 px-3">
                                <option value="" disabled selected>Type name or email to search...</option>
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
                            <div class="col-5"><input type="text" name="first_name" class="form-control rounded-pill bg-light border-0" placeholder="First Name"></div>
                            <div class="col-5"><input type="text" name="last_name" class="form-control rounded-pill bg-light border-0" placeholder="Last Name"></div>
                            <div class="col-2"><input type="text" name="middle_initial" class="form-control rounded-pill bg-light border-0" placeholder="M.I."></div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <select name="owner_gender" class="form-select rounded-pill bg-light border-0 px-3">
                                    <option value="" selected disabled>Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="col-4"><input type="text" name="phone" class="form-control rounded-pill bg-light border-0" placeholder="Phone"></div>
                            <div class="col-4"><input type="email" name="email" class="form-control rounded-pill bg-light border-0" placeholder="Email"></div>
                        </div>

                        <label class="small fw-bold text-muted mb-1">Address</label>
                        <div class="row g-2 mb-2">
                            <div class="col-4"><input type="text" name="house_no" class="form-control rounded-3 bg-light border-0" placeholder="Unit/House #"></div>
                            <div class="col-8"><input type="text" name="street" class="form-control rounded-3 bg-light border-0" placeholder="Street"></div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-4"><input type="text" name="barangay" class="form-control rounded-3 bg-light border-0" placeholder="Barangay"></div>
                            <div class="col-4"><input type="text" name="city" class="form-control rounded-3 bg-light border-0" value="Meycauayan City"></div>
                            <div class="col-4"><input type="text" name="province" class="form-control rounded-3 bg-light border-0" value="Bulacan"></div>
                        </div>

                        {{-- Account Option --}}
                        <div id="accountOptionSection" class="form-check form-switch mb-4 p-3 bg-orange-subtle rounded-4">
                            <input class="form-check-input ms-0 me-2" type="checkbox" id="createAccountToggle" name="create_online_account" value="1" checked>
                            <label class="form-check-label small fw-bold text-orange" for="createAccountToggle">Create online login account for this owner?</label>
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    {{-- 4. Pet Information --}}
                    <h6 class="fw-bold mb-3">Pet Information</h6>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1 px-2">Pet Name</label>
                        {{-- Dropdown for Existing --}}
                        <div id="petNameSelectContainer">
                            <select id="petNameSelect" class="form-select rounded-pill bg-light border-0 px-3">
                                <option value="">Select pet name...</option>
                            </select>
                        </div>
                        {{-- Input for New --}}
                        <div id="petNameInputContainer" style="display: none;">
                            <input type="text" id="petNameInput" name="pet_name" class="form-control rounded-pill bg-light border-0 px-3" placeholder="Enter pet name...">
                        </div>
                        <small class="text-muted px-2 mt-1 d-block" id="petSelectionHint">Select existing pet from the list</small>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <select name="species" id="walkinSpeciesSelect" class="form-select rounded-pill bg-light border-0 px-3" required>
                                <option value="" selected disabled>Species</option>
                                <option value="Dog">Dog</option>
                                <option value="Cat">Cat</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <select name="gender" id="walkinGenderSelect" class="form-select rounded-pill bg-light border-0 px-3" required>
                                <option value="" selected disabled>Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>

                    <div id="walkinBreedContainer" class="mb-3">
                        <select name="breed" id="walkinBreedSelect" class="form-select rounded-pill bg-light border-0 px-3" required>
                            <option value="" selected disabled>Select breed...</option>
                        </select>
                    </div>
                    <div id="walkinOtherBreedContainer" class="mb-3 d-none">
                        <input type="text" name="other_breed" id="walkinOtherBreedInput" class="form-control rounded-pill bg-light border-0 px-3" placeholder="Specify breed">
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold text-muted px-2">Pet Birthday</label>
                        <input type="date" name="birthday" id="walkinBirthdayInput" max="{{ date('Y-m-d') }}" class="form-control rounded-pill bg-light border-0 px-3" value="{{ date('Y-m-d') }}" required>
                    </div>

                    {{-- 5. Appointment Schedule --}}
                    <h6 class="fw-bold mb-3">Appointment Details</h6>
                    <div class="row g-2 mt-3">
                        <div class="col-6">
                            <label class="small fw-bold text-muted px-2">Schedule Date</label>
                            <input type="date" name="schedule_date" min="{{ date('Y-m-d') }}" id="walkinDate" class="form-control rounded-pill bg-light border-0 px-3" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold text-muted px-2">Service</label>
                                <select name="service_type" id="walkinService" class="form-select bg-light" required>
                                    <option value="">Select Service(s)</option>
                                    <option value="Anti-Rabies">Vaccination</option>
                                    <option value="Deworming">Deworming</option>
                                    <option value="Check-up">Check-up</option>
                                    <option value="Kapon">Kapon</option>
                                </select>
                        </div>
                    </div>

                    <div class="row g-2 mt-2">
                        <div class="col-12">
                            <label class="small fw-bold text-muted px-2">Available Time Slots</label>
                            <select name="schedule_time" id="walkinTimeSlot" class="form-select rounded-pill bg-light border-0 px-3" required>
                                <option value="" selected disabled>Select a date first...</option>
                            </select>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    let tsOwner, tsPet;
    const ownerSearchSelect = document.getElementById('ownerSearchSelect');
    const petNameSelect = document.getElementById('petNameSelect');

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
    const statusExisting = document.getElementById('statusExisting');
    const statusNew = document.getElementById('statusNew');
    const existingSection = document.getElementById('existingOwnerSection');
    const newSection = document.getElementById('newOwnerSection');

    const petNameInputContainer = document.getElementById('petNameInputContainer');
    const petNameSelectContainer = document.getElementById('petNameSelectContainer');
    const petNameInput = document.getElementById('petNameInput');
    const petSelectionHint = document.getElementById('petSelectionHint');

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
            petSelectionHint.textContent = 'Select existing pet from the list';

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
            petSelectionHint.textContent = 'Enter the pet\'s name';

            if (tsOwner) tsOwner.clear();
            if (tsPet) tsPet.clearOptions();

            // Reset Pet Details for New Owner
            petNameInput.value = '';
            speciesSelect.value = '';
            genderSelect.value = '';
            birthdayInput.value = "{{ date('Y-m-d') }}";
            if (typeof breedSelect !== 'undefined' && breedSelect) {
                breedSelect.innerHTML = '<option value="" selected disabled>Select breed...</option>';
            }
            ['first_name', 'last_name', 'phone'].forEach(name => {
                const el = newSection.querySelector(`[name="${name}"]`);
                if (el) el.required = true;
            });
        }
    }

    if (statusExisting && statusNew) {
        statusExisting.addEventListener('change', toggleSections);
        statusNew.addEventListener('change', toggleSections);
        toggleSections();
    }

    // --- Pet Fetching & Auto-fill ---
    const speciesSelect = document.getElementById('walkinSpeciesSelect');
    const breedSelect = document.getElementById('walkinBreedSelect');
    const genderSelect = document.getElementById('walkinGenderSelect');
    const birthdayInput = document.getElementById('walkinBirthdayInput');

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
        const selectedOption = this.options[value];
        if (selectedOption && selectedOption.info) {
            const pet = JSON.parse(selectedOption.info);

            // 1. Set basic fields
            speciesSelect.value = pet.species;
            genderSelect.value = pet.gender;
            birthdayInput.value = pet.birthday;

            // 2. Manually trigger the species change to rebuild the Breed list
            // Assuming initializePetBreedLogic set up a listener on speciesSelect
            speciesSelect.dispatchEvent(new Event('change'));

            // 3. Use a slightly longer timeout or a function to "Wait and Set"
            // This ensures the breed dropdown is populated before we select the value
            setTimeout(() => {
                const bSelect = document.getElementById('walkinBreedSelect');
                const otherContainer = document.getElementById('walkinOtherBreedContainer');
                const otherInput = document.getElementById('walkinOtherBreedInput');

                // Clean the pet breed string
                const targetBreed = pet.breed ? pet.breed.trim() : '';

                // Check if the breed exists in the newly populated dropdown
                let breedExists = false;
                for (let i = 0; i < bSelect.options.length; i++) {
                    if (bSelect.options[i].value === targetBreed) {
                        breedExists = true;
                        break;
                    }
                }

                if (breedExists) {
                    bSelect.value = targetBreed;
                    otherContainer.classList.add('d-none');
                    otherInput.value = '';
                } else if (targetBreed !== '') {
                    // If it's a custom breed not in your standard list
                    bSelect.value = 'Other';
                    otherContainer.classList.remove('d-none');
                    otherInput.value = targetBreed;
                    // Ensure the "Other" input actually has the name attribute if needed
                    otherInput.name = 'other_breed';
                }
            }, 500); // 500ms is usually enough for the DOM to update breeds
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
