<div class="modal fade" id="walkInModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center">
                    <div class="bg-orange-subtle p-2 rounded-3 me-3">
                        <i data-lucide="walking" class="text-orange"></i>
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

                        <div id="petSearchSection" style="display: none;">
                            <label class="small fw-bold text-muted mb-1">Select Registered Pet</label>
                            <select id="petSelect" class="form-select rounded-pill bg-light border-0 px-3">
                                <option value="">-- Choose Pet --</option>
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

                        {{-- THIS WAS MISSING: Account Option --}}
                        <div id="accountOptionSection" class="form-check form-switch mb-4 p-3 bg-orange-subtle rounded-4">
                            <input class="form-check-input ms-0 me-2" type="checkbox" id="createAccountToggle" name="create_online_account" value="1" checked>
                            <label class="form-check-label small fw-bold text-orange" for="createAccountToggle">Create online login account for this owner?</label>
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    {{-- 4. Pet Information (Remains constant) --}}
                    <h6 class="fw-bold mb-3">Pet Information</h6>
                    <div class="mb-3">
                        <input type="text" name="pet_name" class="form-control rounded-pill bg-light border-0 px-3" placeholder="Pet Name" required>
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
                            <select name="gender" class="form-select rounded-pill bg-light border-0 px-3" required>
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
                        <input type="date" name="birthday" max="{{ date('Y-m-d') }}" class="form-control rounded-pill bg-light border-0 px-3" value="{{ date('Y-m-d') }}" required>
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
                            <select name="service_type" id="walkinService" class="form-select rounded-pill bg-light border-0 px-3" required>
                                <option value="" selected disabled>Select service...</option>
                                <option value="vaccination">Vaccination</option>
                                <option value="check-up">Check-up</option>
                                <option value="deworming">Deworming</option>
                                <option value="kapon">Kapon</option>
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
    const ownerSearchSelect = document.getElementById('ownerSearchSelect');
    let tsControl;

    if (ownerSearchSelect && typeof TomSelect !== "undefined") {
        tsControl = new TomSelect('#ownerSearchSelect', {
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
            this.input.required = false;
            this.control_input.style.opacity = "1";
            this.wrapper.style.background = "transparent";
            this.wrapper.classList.add('border-0', 'shadow-none');
            this.control.style.display = "flex";
            this.control.style.alignItems = "center";
            this.control.style.minHeight = "42px";

            // Click-to-re-search logic:
            this.control.addEventListener('click', () => {
                if (this.items.length > 0) {
                    this.clear(); // Clears the current owner
                    this.focus(); // Opens the search dropdown immediately
                }
            });
        },

        onItemRemove: function() {
            this.refreshOptions(false);
        }
    });

        // FIX: Bootstrap focus trap vs TomSelect search
        const walkInModalEl = document.getElementById('walkInModal');
        walkInModalEl.addEventListener('shown.bs.modal', function () {
            tsControl.focus();
            tsControl.refreshOptions(false);
        });

        // Allow clicking inside the dropdown
        document.addEventListener('focusin', (e) => {
            if (e.target.closest('.ts-wrapper') || e.target.closest('.ts-dropdown')) {
                e.stopImmediatePropagation();
            }
        }, true);
    }

    // --- Toggle Logic for New vs Existing ---
    const statusExisting = document.getElementById('statusExisting');
    const statusNew = document.getElementById('statusNew');
    const existingSection = document.getElementById('existingOwnerSection');
    const newSection = document.getElementById('newOwnerSection');

    function toggleSections() {
        if (statusExisting.checked) {
            existingSection.style.setProperty('display', 'block', 'important');
            newSection.style.setProperty('display', 'none', 'important');

            // We use our custom validation on submit instead of the browser's bubble
            newSection.querySelectorAll('input, select').forEach(i => i.required = false);
        } else {
            existingSection.style.setProperty('display', 'none', 'important');
            newSection.style.setProperty('display', 'block', 'important');

            if (tsControl) tsControl.clear();

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

    // --- Form Submit Validation ---
    const walkinForm = document.getElementById('walkinForm');
    if (walkinForm) {
        walkinForm.addEventListener('submit', function (e) {
            if (statusExisting.checked && tsControl) {
                if (!tsControl.getValue()) {
                    e.preventDefault();
                    tsControl.wrapper.classList.add('border', 'border-danger');
                    tsControl.focus();
                    return false;
                }
            }
        });
    }
    const createAccountToggle = document.getElementById('createAccountToggle');
    const emailInput = document.querySelector('input[name="email"]');
    const emailCol = emailInput.closest('.col-4'); // Container of the email input

    function handleAccountToggle() {
        if (createAccountToggle.checked) {
            // Show email input
            emailCol.style.setProperty('display', 'block', 'important');
            emailInput.required = true;
        } else {
            // Hide email input
            emailCol.style.setProperty('display', 'none', 'important');

            // CRITICAL: Remove required so the browser doesn't try to validate a hidden field
            emailInput.required = false;

            // Clear value and reset validation state
            emailInput.value = '';
            emailInput.setCustomValidity('');
        }
    }

    createAccountToggle.addEventListener('change', handleAccountToggle);
    // Initialize on load
    handleAccountToggle();

    // --- Breed Logic ---
    const walkinSpecies = document.getElementById('walkinSpeciesSelect');
    const walkinBreed = document.getElementById('walkinBreedSelect');
    const walkinOtherContainer = document.getElementById('walkinOtherBreedContainer');
    const breeds = {
        'Dog': ['Aspin', 'Shih Tzu', 'Pomeranian', 'Pug', 'Chihuahua',
            'Golden Retriever', 'Labrador Retriever', 'Siberian Husky',
            'German Shepherd', 'Poodle', 'Beagle', 'Bulldog', 'Rottweiler',
            'Dachshund', 'Yorkshire Terrier', 'Boxer', 'Doberman Pinscher',
            'Corgi', 'Maltese', 'Bichon Frise', 'Chow Chow', 'Dalmatian', 'Other'],

        'Cat': ['Puspin', 'Persian', 'Siamese', 'Maine Coon', 'Ragdoll',
            'British Shorthair', 'Sphynx', 'Abyssinian', 'Scottish Fold',
            'Russian Blue', 'Bengal', 'American Shorthair', 'Himalayan',
            'Norwegian Forest Cat', 'Oriental Shorthair', 'Other']
    };

    if (walkinSpecies) {
        walkinSpecies.addEventListener('change', function() {
            const selected = this.value;
            walkinBreed.innerHTML = '<option value="" selected disabled>Select breed...</option>';
            walkinOtherContainer.classList.add('d-none');
            if (breeds[selected]) {
                breeds[selected].forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = b;
                    opt.textContent = b;
                    walkinBreed.appendChild(opt);
                });
            }
        });

        walkinBreed.addEventListener('change', function() {
            if (this.value === 'Other') {
                walkinOtherContainer.classList.remove('d-none');
                document.getElementById('walkinOtherBreedInput').required = true;
            } else {
                walkinOtherContainer.classList.add('d-none');
                document.getElementById('walkinOtherBreedInput').required = false;
            }
        });
    }
});
// --- Time Slot Logic for Kapon and 30-min services ---
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
        // Replace this URL with your actual route that checks the database
        const response = await fetch(`/staff/appointments/booked-slots?date=${date}`);
        const bookedSlots = await response.json();

        walkinTimeSlot.innerHTML = '<option value="" selected disabled>Select time...</option>';

        allSlots.forEach((slot, index) => {
            const isBooked = bookedSlots.includes(slot);
            let isDisabled = isBooked;

            // Kapon Logic: Check if the current AND next slot are available
            if (service === 'kapon') {
                const nextSlot = allSlots[index + 1];
                const isNextBooked = nextSlot ? bookedSlots.includes(nextSlot) : true;

                // Prevent booking at 11:30 AM (crosses into lunch) or 4:30 PM (end of day)
                const isEndOfSession = (slot === "11:30" || slot === "16:30");

                if (isNextBooked || isEndOfSession) {
                    isDisabled = true;
                }
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

    // Calculate the end time for the display label (e.g., 8:00 - 8:30)
    let endM = parseInt(m) + 30;
    let endH = hour;
    if (endM === 60) { endM = "00"; endH++; }
    const displayEndH = endH > 12 ? endH - 12 : (endH === 0 ? 12 : endH);

    const suffix = (time === "11:30") ? " (Morning Cut-off)" : "";
    return `${displayHour}:${m} - ${displayEndH}:${endM.toString().padStart(2, '0')} ${ampm}${suffix}`;
}

// Trigger update when date or service changes
walkinDate.addEventListener('change', updateAvailableSlots);
walkinService.addEventListener('change', updateAvailableSlots);

// --- Existing Owner Pet Fetching & Auto-fill ---
const petSearchSection = document.getElementById('petSearchSection');
const petSelect = document.getElementById('petSelect');

// Inputs to be auto-filled
const petNameInput = document.querySelector('input[name="pet_name"]');
const speciesSelect = document.getElementById('walkinSpeciesSelect');
const breedSelect = document.getElementById('walkinBreedSelect');
const genderSelect = document.querySelector('select[name="gender"]');
const birthdayInput = document.querySelector('input[name="birthday"]');

// 1. When Owner is selected via TomSelect
if (ownerSearchSelect && tsControl) {
    tsControl.on('change', async function(userId) {
        if (!userId) {
            petSearchSection.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`/staff/get-owner-pets/${userId}`);
            const pets = await response.json();

            petSelect.innerHTML = '<option value="">-- Choose Pet --</option>';

            if (pets.length > 0) {
                pets.forEach(pet => {
                    const opt = document.createElement('option');
                    opt.value = pet.id;
                    opt.textContent = pet.name;
                    // Store pet data in data attributes for easy access
                    opt.dataset.info = JSON.stringify(pet);
                    petSelect.appendChild(opt);
                });
                petSearchSection.style.display = 'block';
            } else {
                petSearchSection.style.display = 'none';
            }
        } catch (error) {
            console.error("Error fetching pets:", error);
        }
    });
}

// 2. When Pet is selected, auto-fill the fields
petSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (!selectedOption.value) return;

    const pet = JSON.parse(selectedOption.dataset.info);

    // Auto-fill fields
    petNameInput.value = pet.name;
    speciesSelect.value = pet.species;

    // Trigger species change manually to populate breed list
    speciesSelect.dispatchEvent(new Event('change'));

    // Set breed (delay slightly to ensure breed list is populated by your existing script)
    setTimeout(() => {
        breedSelect.value = pet.breed;
        // Handle "Other" breed if necessary
        if (pet.breed === 'Other') {
            document.getElementById('walkinOtherBreedContainer').classList.remove('d-none');
            document.getElementById('walkinOtherBreedInput').value = pet.other_breed || '';
        }
    }, 50);

    genderSelect.value = pet.gender;
    birthdayInput.value = pet.birthday;
});
</script>
