function initializePetBreedLogic(config, libraryInstance = null) {
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
            // Disable if no species is selected
            if (libraryInstance && typeof libraryInstance.disable === 'function') libraryInstance.disable();
            breedSelect.disabled = true;
            return;
        }

        const normalizedSelected = selected.charAt(0).toUpperCase() + selected.slice(1).toLowerCase();
        const breedOptions = breeds[normalizedSelected] || [];

        // --- TOMSELECT LOGIC ---
        if (libraryInstance && typeof libraryInstance.addOptions === 'function') {
            libraryInstance.clearOptions();
            const tsOptions = breedOptions.map(breed => ({ value: breed, text: breed }));
            libraryInstance.addOptions(tsOptions);
            libraryInstance.setValue(selectedBreed || '');
            libraryInstance.refreshOptions(false);
            libraryInstance.enable();
        }
        // --- CHOICES.JS LOGIC ---
        else if (libraryInstance && typeof libraryInstance.setChoices === 'function') {
            libraryInstance.clearChoices();
            const choicesArray = breedOptions.map(breed => ({
                value: breed,
                label: breed,
                selected: breed === selectedBreed,
            }));
            libraryInstance.setChoices(choicesArray, 'value', 'label', true);
            libraryInstance.enable();
        }
        // --- FALLBACK (Standard Select) ---
        else {
            breedSelect.innerHTML = '<option value="" disabled selected>Select Breed</option>';
            breedOptions.forEach(breed => {
                const opt = new Option(breed, breed);
                if (breed === selectedBreed) opt.selected = true;
                breedSelect.add(opt);
            });
            breedSelect.disabled = false;
        }

        // Determine current value for "Other" visibility
        let currentValue = selectedBreed;
        if (!currentValue) {
            if (libraryInstance && typeof libraryInstance.getValue === 'function') {
                // TomSelect or Choices.js getValue
                currentValue = libraryInstance.getValue(true);
            } else {
                currentValue = breedSelect.value;
            }
        }
        handleOtherVisibility(currentValue);
    };

    const handleOtherVisibility = (value) => {
        const isOther = value === 'Other';
        if (otherContainer) {
            otherContainer.classList.toggle('d-none', !isOther);
            // Fallback display style for browsers that don't support d-none well
            otherContainer.style.display = isOther ? 'block' : 'none';
            if (otherInput) otherInput.required = isOther;
        }
    };

    // --- EVENT LISTENERS ---

    // 1. Species Change
    speciesSelect.addEventListener('change', (e) => {
        updateBreeds(e.target.value);
    });

    // 2. Breed Change (to toggle "Other" input)
    if (libraryInstance && typeof libraryInstance.on === 'function') {
        // TomSelect uses .on('change')
        libraryInstance.on('change', (val) => handleOtherVisibility(val));
    } else {
        // Choices.js and Standard Select use standard listener
        breedSelect.addEventListener('change', (e) => handleOtherVisibility(e.target.value));
    }

    // 3. Auto-Capitalize "Other" Breed Input
    if (otherInput) {
        otherInput.addEventListener('blur', function() {
            this.value = this.value.toLowerCase().replace(/\b\w/g, s => s.toUpperCase());
        });
    }

    // Initial Load
    if (config.initialSpecies) {
        updateBreeds(config.initialSpecies, config.initialBreed);
    }
}
