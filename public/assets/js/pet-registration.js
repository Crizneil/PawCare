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

    const updateBreeds = (selected, selectedBreed = null) => {
        if (!selected) {
            if (choicesInstance) choicesInstance.disable();
            return;
        }

        const normalizedSelected = selected.charAt(0).toUpperCase() + selected.slice(1).toLowerCase();
        const breedOptions = breeds[normalizedSelected] || [];

        // --- CHOICES.JS LOGIC ---
        if (choicesInstance) {
            const choicesArray = breedOptions.map(breed => ({
                value: breed,
                label: breed,
                selected: breed === selectedBreed,
                disabled: false
            }));
            choicesInstance.enable();
            choicesInstance.clearChoices();
            choicesInstance.setChoices(choicesArray, 'value', 'label', true);
        }
        // --- FALLBACK LOGIC (For Walk-in Modal / Standard Selects) ---
        else {
            breedSelect.innerHTML = '<option value="" disabled selected>Select Breed</option>';
            breedOptions.forEach(breed => {
                const opt = new Option(breed, breed);
                if (breed === selectedBreed) opt.selected = true;
                breedSelect.add(opt);
            });
            // Support for Select2 if your Walk-in uses it
            if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                $(breedSelect).trigger('change');
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

    // Listeners
    speciesSelect.addEventListener('change', (e) => updateBreeds(e.target.value));
    breedSelect.addEventListener('change', (e) => handleOtherVisibility(e.target.value));

    // Initial State
    if (config.initialSpecies) {
        updateBreeds(config.initialSpecies, config.initialBreed);
    }
}
