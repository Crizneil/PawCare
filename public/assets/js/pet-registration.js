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
        if (!selected) return;

        const normalizedSelected = selected.charAt(0).toUpperCase() + selected.slice(1).toLowerCase();
        const breedOptions = breeds[normalizedSelected] || [];

        // --- TOMSELECT LOGIC (For Walk-in Modal) ---
        if (libraryInstance && typeof libraryInstance.addOptions === 'function') {
            libraryInstance.clearOptions();
            const tsOptions = breedOptions.map(breed => ({ value: breed, text: breed }));
            libraryInstance.addOptions(tsOptions);
            libraryInstance.setValue(selectedBreed || '');
            libraryInstance.refreshOptions(false);
        }
        // --- CHOICES.JS LOGIC (For Admin Modal) ---
        else if (libraryInstance && typeof libraryInstance.setChoices === 'function') {
            const choicesArray = breedOptions.map(breed => ({
                value: breed,
                label: breed,
                selected: breed === selectedBreed,
            }));
            libraryInstance.enable();
            libraryInstance.clearChoices();
            libraryInstance.setChoices(choicesArray, 'value', 'label', true);
        }
        // --- FALLBACK LOGIC ---
        else {
            breedSelect.innerHTML = '<option value="" disabled selected>Select Breed</option>';
            breedOptions.forEach(breed => {
                const opt = new Option(breed, breed);
                if (breed === selectedBreed) opt.selected = true;
                breedSelect.add(opt);
            });
        }

        handleOtherVisibility(selectedBreed || breedSelect.value);
    };

    const handleOtherVisibility = (value) => {
        const isOther = value === 'Other';
        if (otherContainer) {
            otherContainer.classList.toggle('d-none', !isOther);
            otherContainer.style.display = isOther ? 'block' : 'none';
            if (otherInput) otherInput.required = isOther;
        }
    };

    // Listeners
    speciesSelect.addEventListener('change', (e) => updateBreeds(e.target.value));

    // Support TomSelect change event
    if (libraryInstance && typeof libraryInstance.on === 'function') {
        libraryInstance.on('change', (val) => handleOtherVisibility(val));
    } else {
        breedSelect.addEventListener('change', (e) => handleOtherVisibility(e.target.value));
    }

    if (config.initialSpecies) {
        updateBreeds(config.initialSpecies, config.initialBreed);
    }
}
