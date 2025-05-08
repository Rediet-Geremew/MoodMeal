document.addEventListener("DOMContentLoaded", function() {
    const addEntryButton = document.getElementById("add_entry");
    const entryDialog = document.getElementById("entryDialog");
    const closeDialogButton = document.getElementById("closeDialog");
    const entryForm = document.getElementById("entryForm");

    // Open the dialog when the "Add New Entry" button is clicked
    addEntryButton.addEventListener("click", function() {
        entryDialog.showModal();
    });

    // Close the dialog when the "Cancel" button is clicked
    closeDialogButton.addEventListener("click", function() {
        entryDialog.close();
    });

    // Handle form submission
    entryForm.addEventListener("submit", function(event) {
        event.preventDefault();

        const formData = new FormData(entryForm);

        // Use fetch API to submit the form data to the PHP backend
        fetch("add_journal_entry.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Journal entry added successfully!");
                entryDialog.close();
                loadEntries();
            } else {
                alert("Error: " + data.error);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred while adding the entry.");
        });
    });

    // Function to load journal entries
    function loadEntries() {
        fetch("get_journal_entries.php")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const entriesContainer = document.getElementById("entriesContainer");
                entriesContainer.innerHTML = "";

                data.entries.forEach(entry => {
                    const entryCard = document.createElement("div");
                    entryCard.classList.add("entryCard");

                    // Add content to the entry card (e.g., mood, food, etc.)
                    entryCard.innerHTML = `
                        <h3>${entry.food_name} (${entry.food_type})</h3>
                        <p>Mood Before: ${entry.mood_before}</p>
                        <p>Mood After: ${entry.mood_after}</p>
                        <p>${entry.journal_text}</p>
                        <p>Tags: ${entry.tags.join(", ")}</p>
                        <p>Date: ${entry.entry_date}</p>
                        ${entry.image_url ? `<img src="${entry.image_url}" alt="Image for ${entry.food_name}" />` : ""}
                    `;
                    entriesContainer.appendChild(entryCard);
                });
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred while loading entries.");
        });
    }

    // Load entries when the page loads
    loadEntries();
});
