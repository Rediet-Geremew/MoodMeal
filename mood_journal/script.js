const addEntryBtn = document.getElementById('add_entry');
const dialog = document.getElementById('entryDialog');
const closeDialogBtn = document.getElementById('closeDialog');
const entryForm = document.getElementById('entryForm');
const main = document.querySelector('main');

let journalEntries = []; // or pull from localStorage if needed

addEntryBtn.addEventListener('click', () => {
    dialog.showModal();
});

closeDialogBtn.addEventListener('click', () => {
    dialog.close();
});

entryForm.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(entryForm);
    const date = formData.get('date');
    const mealType = formData.get('mealType');
    const moodBefore = formData.get('moodBefore');
    const moodAfter = formData.get('moodAfter');
    const tags = formData.get('tags').split(',').map(tag => tag.trim());
    const journalText = formData.get('journalText');

    const entry = {
        id: Date.now(), // Unique ID for future reference
        date,
        mealType,
        moodBefore,
        moodAfter,
        tags,
        journalText
    };

    journalEntries.push(entry);

    const card = document.createElement('div');
    card.classList.add('journal-card');
    card.innerHTML = `
        <div class="imgContainer">
            <img class="foodImg" src="./assets/default-food.jpg" alt="Default food">
        </div>
        <div class="infoContainer">
            <p>🗓️ Date: ${date}</p>
            <p>🍽️ Meal Type: ${mealType}</p>
            <p>Before: ${moodBefore} → After: ${moodAfter}</p>
            <div class="tags">
                ${tags.map(tag => `<div class="tag">${tag}</div>`).join('')}
            </div>
            <div class="see" data-id="${entry.id}">
                <img class="seeMore" src="./assets/icons8-plus-math.gif">
                <p>see more</p>
            </div>
        </div>
    `;

    main.appendChild(card);
    dialog.close();
    entryForm.reset();
});

// Optional: Add a listener to main to catch all future .see clicks
main.addEventListener('click', (e) => {
    const seeMore = e.target.closest('.see');
    if (seeMore) {
        const entryId = seeMore.dataset.id;
        const entry = journalEntries.find(entry => entry.id == entryId);
        if (entry) {
            alert(`Full journal:\n\n${entry.journalText}`); // Replace this with a proper modal later
        }
    }
});
