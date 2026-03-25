function initializePetBreedLogic(config) {
    const speciesSelect = document.getElementById(config.speciesId);
    const breedSelect = document.getElementById(config.breedId);
    const otherContainer = document.getElementById(config.otherContainerId);
    const otherInput = document.getElementById(config.otherInputId);

    // Safety check: stop if elements aren't found
    if (!speciesSelect || !breedSelect) return;

    const breeds = {
        'Dog': [
            'Aspin', 'Beagle', 'Bulldog',
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

    let tsBreed = null;

    // Initialize TomSelect if available
    const initTomSelect = () => {
        if (typeof TomSelect !== 'undefined') {
            tsBreed = new TomSelect(breedSelect, {
                create: false,
                sortField: { field: "text", direction: "asc" },
                placeholder: 'Search or select breed...'
            });
        } else if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
             $(breedSelect).select2({
                dropdownParent: $(breedSelect).closest('.modal'),
                width: '100%',
                placeholder: 'Search or select breed...'
            });
        }
    };

    initTomSelect();

    // Handle species change
    const updateBreeds = (selected, selectedBreed = null) => {
        if (!selected) return;

        // Normalize selected to Title Case for breeds lookup
        const normalizedSelected = selected.charAt(0).toUpperCase() + selected.slice(1).toLowerCase();

        // 1. Hide "Other" input and reset it if not "Other"
        if (selectedBreed !== 'Other') {
            if (otherContainer) otherContainer.classList.add('d-none');
            if (otherInput) {
                otherInput.required = false;
                otherInput.value = '';
            }
        }

        // 2. Populate breeds using TomSelect API if active, otherwise standard DOM
        if (tsBreed) {
            tsBreed.clear();
            tsBreed.clearOptions();
            if (breeds[normalizedSelected]) {
                const newOptions = breeds[normalizedSelected].map(b => ({value: b, text: b}));
                tsBreed.addOptions(newOptions);
                tsBreed.enable();
            } else {
                tsBreed.disable();
            }

            if (selectedBreed) {
                tsBreed.setValue(selectedBreed);
            } else if (normalizedSelected === 'Other') {
                tsBreed.setValue('Other');
            }
            
        } else {
            // Fallback for native/Select2
            breedSelect.innerHTML = '<option value="" selected disabled>Select Breed</option>';
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

            if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                $(breedSelect).trigger('change');
            }

            if (normalizedSelected === 'Other') {
                breedSelect.value = 'Other';
                breedSelect.dispatchEvent(new Event('change'));
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
        // Reserved for future dynamic logic
    });
}
