document.addEventListener("DOMContentLoaded", () => {
    const API_BASE = 'http://localhost/MoodMeal/mood_journal/backend/api';
    const dialog = document.getElementById('entryDialog');
    const form = document.getElementById('entryForm');

    // Dialog controls
    document.getElementById('add_entry').addEventListener('click', () => dialog.showModal());
    document.getElementById('closeDialog').addEventListener('click', () => dialog.close());

    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;

        try {
            const formData = new FormData(form);
            const response = await fetch(`${API_BASE}/create.php`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Failed to save entry');
            }

            if (data.success) {
                dialog.close();
                form.reset();
                await loadEntries();
                alert('Entry saved successfully!');
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message);
        } finally {
            submitBtn.disabled = false;
        }
    });

    // Load entries
    async function loadEntries() {
        try {
            const response = await fetch(`${API_BASE}/read.php`);
            
            // Check if response is OK first
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
            }
    
            const data = await response.json();
            
            if (data.success) {
                const container = document.getElementById('entriesContainer');
                container.innerHTML = data.entries.map(entry => `
                    <div class="entryCard">
                        <h3>${entry.food_name} (${entry.food_type})</h3>
                        <p>Date: ${entry.entry_date}</p>
                        <p>Mood Before: ${entry.mood_before}</p>
                        <p>Mood After: ${entry.mood_after}</p>
                        ${entry.image_url ? `<img src="${entry.image_url}" alt="Food image">` : ''}
                        <p>${entry.journal_text}</p>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Error loading entries:', error);
            alert('Failed to load entries: ' + error.message);
        }
    }

    // Initial load
    loadEntries();
});