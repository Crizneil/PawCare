function initializePetBreedLogic(config, libraryInstance = null) {
    const speciesSelect = document.getElementById(config.speciesId);
    const breedSelect = document.getElementById(config.breedId);
    const otherContainer = document.getElementById(config.otherContainerId);
    const otherInput = document.getElementById(config.otherInputId);

    if (!speciesSelect || !breedSelect) return;

    let breeds = {
        'Dog': ['Aspin', 'Beagle', 'Bulldog', 'Chihuahua', 'Chow Chow', 'Cocker Spaniel', 'Dachshund', 'Dalmatian', 'Doberman Pinscher', 'German Shepherd', 'Golden Retriever', 'Great Dane', 'Jack Russell Terrier', 'Labrador Retriever', 'Maltese', 'Pomeranian', 'Poodle', 'Pug', 'Rottweiler', 'Shih Tzu', 'Siberian Husky', 'Other'],
        'Cat': ['Abyssinian', 'American Shorthair', 'Bengal', 'Birman', 'British Shorthair', 'Exotic Shorthair', 'Maine Coon', 'Persian', 'Puspin', 'Ragdoll', 'Russian Blue', 'Siamese', 'Sphynx', 'Other'],
        'Other': ['Other']
    };

    fetch('/api/breeds')
        .then(response => response.json())
        .then(data => {
            if (data && data.Dog) {
                if (!data.Dog.includes('Other')) data.Dog.push('Other');
                if (data.Cat && !data.Cat.includes('Other')) data.Cat.push('Other');
                breeds = { ...breeds, ...data, 'Other': ['Other'] };
                if (speciesSelect.value) {
                    updateBreeds(speciesSelect.value, config.initialBreed || breedSelect.value);
                }
            }
        })
        .catch(err => console.error("Error fetching breeds:", err));

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
        // Handle cases where 'value' might be an Event object (Choices.js) or a string (TomSelect)
        let actualValue = value;
        if (value && typeof value === 'object' && value.detail && value.detail.value) {
            actualValue = value.detail.value;
        } else if (value && typeof value === 'object' && value.target) {
            actualValue = value.target.value;
        }

        const isOther = actualValue && actualValue.toString().toLowerCase() === 'other';

        if (otherContainer) {
            otherContainer.classList.toggle('d-none', !isOther);
            otherContainer.style.display = isOther ? 'block' : 'none';
            if (otherInput) {
                otherInput.required = isOther;
                if (!isOther) otherInput.value = ''; // Clear if hidden
            }
        }
    };

    // Listeners
    speciesSelect.addEventListener('change', (e) => updateBreeds(e.target.value));

    // Support Library-specific change events (TomSelect, Choices.js)
    if (libraryInstance && typeof libraryInstance.on === 'function') {
        libraryInstance.on('change', (val) => handleOtherVisibility(val));
    }
    
    // Always add a standard listener as a fallback
    breedSelect.addEventListener('change', (e) => handleOtherVisibility(e.target.value));

    if (config.initialSpecies) {
        updateBreeds(config.initialSpecies, config.initialBreed);
    }
}
