function initializePetBreedLogic(config, choicesInstance = null) {
    const speciesSelect = document.getElementById(config.speciesId);
    const breedSelect = document.getElementById(config.breedId);
    const otherContainer = document.getElementById(config.otherContainerId);
    const otherInput = document.getElementById(config.otherInputId);

    if (!speciesSelect || !breedSelect) return;

    const breeds = {
        'Dog': ['Aspin', 'Beagle', 'Bulldog', 'Chihuahua', 'Chow Chow', 'Cocker Spaniel', 'Dachshund', 'Dalmatian', 'Doberman Pinscher', 'German Shepherd', 'Golden Retriever', 'Great Dane', 'Jack Russell Terrier', 'Labrador Retriever', 'Maltese', 'Pomeranian', 'Poodle', 'Pug', 'Rottweiler', 'Shih Tzu', 'Siberian Husky', 'Other'],
        'Cat': ['Abyssinian', 'American Shorthair', 'Bengal', 'Birman', 'British Shorthair', 'Exotic Shorthair', 'Maine Coon', 'Persian', 'Puspin', 'Ragdoll', 'Russian Blue', 'Siamese', 'Sphynx', 'Other'],
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
        if (!selected) {
            if (choicesInstance) choicesInstance.disable();
            return;
        }

        // Normalize selected to Title Case for breeds lookup
        const normalizedSelected = selected.charAt(0).toUpperCase() + selected.slice(1).toLowerCase();
        const breedOptions = breeds[normalizedSelected] || [];

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

        handleOtherVisibility(selectedBreed || breedSelect.value);
    };

    const handleOtherVisibility = (value) => {
        const isOther = value === 'Other';
        if (otherContainer) {
            otherContainer.classList.toggle('d-none', !isOther);
            if (isOther) {
                otherContainer.style.display = 'block';
                if (otherInput) {
                    otherInput.required = true;
                    setTimeout(() => otherInput.focus(), 100);
                }
            } else {
                otherContainer.style.display = '';
                if (otherInput) {
                    otherInput.required = false;
                    otherInput.value = '';
                }
            }
        }

        // Handle Name Swapping for Form Submission
        if (config.swapNameOnOther) {
            if (isOther) {
                breedSelect.name = 'breed_dropdown';
                if (otherInput) otherInput.name = 'breed';
            } else {
                breedSelect.name = 'breed';
                if (otherInput) otherInput.name = 'other_breed';
            }
        }
    };

    otherInput.addEventListener('input', function () {
        // Reserved for future dynamic logic
    });
}
