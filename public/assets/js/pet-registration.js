function initializePetBreedLogic(config) {
    const speciesSelect = document.getElementById(config.speciesId);
    const breedSelect = document.getElementById(config.breedId);
    const otherContainer = document.getElementById(config.otherContainerId);
    const otherInput = document.getElementById(config.otherInputId);

    // Safety check: stop if elements aren't found
    if (!speciesSelect || !breedSelect) return;

    const breeds = {
        'Dog': [
            'Aspin', 'Beagle', 'Bulldog (English)', 'Bulldog (French)', 'Bulldog (American)', 
            'Chihuahua', 'Chow Chow', 'Cocker Spaniel', 'Dachshund', 'Dalmatian', 
            'Doberman Pinscher', 'German Shepherd', 'Golden Retriever', 'Great Dane', 
            'Jack Russell Terrier', 'Labrador Retriever', 'Maltese', 'Pomeranian', 
            'Poodle', 'Pug', 'Rottweiler', 'Shih Tzu', 'Siberian Husky', 'Other'
        ],
        'Cat': [
            'Abyssinian', 'American Shorthair', 'Bengal', 'Birman', 'British Shorthair', 
            'Exotic Shorthair', 'Maine Coon', 'Persian', 'Puspin', 'Ragdoll', 
            'Russian Blue', 'Siamese', 'Sphynx', 'Other'
        ],
        'Other': ['Other']
    };

    // Initialize Select2 if available
    const initSelect2 = () => {
        if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            $(breedSelect).select2({
                dropdownParent: $(breedSelect).closest('.modal'),
                width: '100%',
                placeholder: 'Search or select breed...'
            });
        }
    };

    initSelect2();

    // Handle species change
    const updateBreeds = (selected, selectedBreed = null) => {
        if (!selected) return;
        
        // Normalize selected to Title Case for breeds lookup (handles 'dog', 'DOG', etc.)
        const normalizedSelected = selected.charAt(0).toUpperCase() + selected.slice(1).toLowerCase();

        // 1. Reset breed dropdown
        breedSelect.innerHTML = '<option value="" selected disabled>Select Breed</option>';

        // 2. Hide "Other" input and reset it if not "Other"
        if (selectedBreed !== 'Other') {
            if (otherContainer) otherContainer.classList.add('d-none');
            if (otherInput) {
                otherInput.required = false;
                otherInput.value = '';
            }
        }

        // 3. Populate breeds for selected species
        if (breeds[normalizedSelected]) {
            breedSelect.disabled = false;
            breeds[normalizedSelected].forEach(breed => {
                const option = document.createElement('option');
                option.value = breed;
                option.textContent = breed;
                if (breed === selectedBreed) {
                    option.selected = true;
                }
                breedSelect.appendChild(option);
            });
        } else {
            breedSelect.disabled = true;
        }

        // Trigger Select2 update
        if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            $(breedSelect).trigger('change');
        }

        // Auto-select "Other" if the species is "Other"
        if (selected === 'Other') {
            breedSelect.value = 'Other';
            breedSelect.dispatchEvent(new Event('change'));
            if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                $(breedSelect).trigger('change');
            }
        }
    };

    speciesSelect.addEventListener('change', function () {
        updateBreeds(this.value);
    });

    // Handle initial state if pre-selected
    if (config.initialSpecies) {
        speciesSelect.value = config.initialSpecies;
        updateBreeds(config.initialSpecies, config.initialBreed);
        if (config.initialBreed === 'Other' && otherInput) {
            otherInput.value = config.otherBreedValue || '';
        }
    }

    // Handle breed change
    breedSelect.addEventListener('change', function () {
        if (this.value === 'Other') {
            if (otherContainer) {
                otherContainer.classList.remove('d-none');
                otherContainer.style.display = 'block'; // Force display override
            }
            if (otherInput) {
                otherInput.required = true;
                otherInput.disabled = false;    // Strip disabled if cached
                otherInput.readOnly = false;    // Strip readonly if cached
                otherInput.style.pointerEvents = 'auto'; // Force interaction

                // If species is Other, just say "Enter Breed", otherwise say "Enter Dog/Cat Breed"
                otherInput.placeholder = speciesSelect.value === 'Other' ? "Enter Breed" : "Enter " + speciesSelect.value + " Breed";

                // Blast focus directly to the input box to instantly enable typing
                setTimeout(() => {
                    otherInput.focus();
                }, 100);
            }
            // Swap name for submission if needed (specifically for admin view logic)
            if (config.swapNameOnOther) {
                breedSelect.name = 'breed_dropdown';
                otherInput.name = 'breed';
            }
        } else {
            if (otherContainer) {
                otherContainer.classList.add('d-none');
                otherContainer.style.display = ''; // Reset inline style
            }
            if (otherInput) {
                otherInput.required = false;
                otherInput.value = '';
            }
            // Reset names
            if (config.swapNameOnOther) {
                breedSelect.name = 'breed';
                otherInput.name = 'other_breed';
            }
        }
    });

    otherInput.addEventListener('input', function () {
        if (breedSelect.value === 'Other') {
        }
    });
}
