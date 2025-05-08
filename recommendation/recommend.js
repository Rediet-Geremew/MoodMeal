async function getRecommendations() {
  const mood = document.getElementById("moodInput").value.trim();
  if (!mood) return alert("Please enter your mood!");

  try {
    const response = await fetch("recommendation.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ mood })
    });

    const raw = await response.text();
    console.log("Raw server response:", raw);

    if (!response.ok) {
      throw new Error(`Server returned ${response.status}: ${response.statusText}`);
    }

    let data;
    try {
      data = JSON.parse(raw);
    } catch (e) {
      console.error("Failed to parse JSON:", e);
      throw new Error("The server returned invalid data. Please try again.");
    }

    if (data.error) {
      alert("Error: " + data.error);
      return;
    }

    if (!data.meals || !Array.isArray(data.meals)) {
      throw new Error("Invalid data format received from server");
    }

    const cardsContainer = document.querySelector(".row.g-4");
    cardsContainer.innerHTML = "";

    data.meals.forEach((mealData) => {
      const meal = mealData.meal || "Unknown Meal";
      const description = mealData.description || "No description available";
      const image = mealData.image || "fallback/fallback.jpg";
      const recipeId = mealData.spoonacular_id || ""; 

      const card = document.createElement("div");
      card.className = "col-md-4";
      card.innerHTML = `
        <div class="card h-100 text-center">
          <div class="bg-light rounded-top" style="height: 200px; 
            background-image: url('${image}');
            background-size: cover;
            background-position: center;">
          </div>
          <div class="card-body">
            <h5 class="card-title">${meal}</h5>
            <p class="card-text text-muted">${description}</p>
            <small class="text-muted d-block mb-2">Prep time may vary</small>
            <a href="recipe.html?id=${recipeId}" 
               class="btn btn-outline-warning">
               View Recipe
            </a>
          </div>
        </div>
      `;
      cardsContainer.appendChild(card);
    });
  } catch (error) {
    if (error instanceof TypeError && error.message.includes("failed to fetch")) {
      alert("Network error: Unable to reach the server. Please check your internet connection or try again later.");
    } else {
      alert("An error occurred: " + error.message);
    }
    console.error("Error details:", error);
  }
}