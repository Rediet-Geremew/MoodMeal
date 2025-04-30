// Get entry ID from URL
const params = new URLSearchParams(window.location.search);
const entryId = params.get("id");

// Elements to display data
const detailContainer = document.getElementById("entryDetails");

if (entryId && localStorage.getItem(entryId)) {
  const data = JSON.parse(localStorage.getItem(entryId));

  detailContainer.innerHTML = `
    <h2>🗓️ ${data.date} - ${data.mealType}</h2>
    <p><strong>Mood:</strong> ${data.moodBefore} → ${data.moodAfter}</p>
    <p><strong>Tags:</strong> ${data.tags.map(tag => `<span class="tag">${tag}</span>`).join(', ')}</p>
    <p><strong>Journal:</strong> ${data.journalText}</p>
  `;
} else {
  detailContainer.innerHTML = `<p>Entry not found.</p>`;
}
