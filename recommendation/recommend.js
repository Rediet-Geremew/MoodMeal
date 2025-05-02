function getRecommendations() {
    const mood = document.getElementById('moodInput').value.trim();
    if (mood) {
      alert(`Feeling "${mood}"? Here’s a dish for you!`);
    } else {
      alert("Please enter your mood to get a recommendation.");
    }
  }
  