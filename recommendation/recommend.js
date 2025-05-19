async function getRecommendations() {
    const mood = document.getElementById("moodInput").value.trim();
    if (!mood) {
        alert("Please enter your mood!");
        return;
    }

    try {
        const cardsContainer = document.querySelector(".row.g-4");
        cardsContainer.innerHTML = '<div class="col-12 text-center"><div class="spinner-border text-warning"></div><p>Finding perfect recipes for your mood...</p></div>';

        const response = await fetch("recommendation.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ mood })
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => null);
            throw new Error(errorData?.error || `Server error: ${response.status}`);
        }

        const data = await response.json();
        if (data.error) throw new Error(data.error);
        if (!data.meals?.length) throw new Error("No recipes found for your mood.");

        cardsContainer.innerHTML = "";
        data.meals.forEach(mealData => {
            const card = document.createElement("div");
            card.className = "col-md-4 mb-4";
            card.innerHTML = `
                <div class="card h-100">
                    <div class="card-img-top" 
                         style="height: 200px; background: url('${mealData.image || 'https://via.placeholder.com/300'}') center/cover no-repeat">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">${mealData.meal}</h5>
                        <p class="card-text">${mealData.description || 'No description available.'}</p>
                        <button class="btn btn-warning view-recipe" 
                                data-id="${mealData.recipe_id}"
                                data-name="${mealData.meal}"
                                data-image="${mealData.image}">
                            View Recipe
                        </button>
                    </div>
                </div>
            `;
            cardsContainer.appendChild(card);
        });

        document.querySelectorAll(".view-recipe").forEach(button => {
            button.addEventListener("click", () => {
                const recipeId = button.dataset.id;
                const recipeName = encodeURIComponent(button.dataset.name);
                const recipeImage = encodeURIComponent(button.dataset.image);
    
                window.location.href = `recipe.html?id=${recipeId}&name=${recipeName}&image=${recipeImage}`;
            });
        });

    } catch (error) {
        console.error("Error:", error);
        document.querySelector(".row.g-4").innerHTML = `
            <div class="col-12 text-center text-danger">
                <p>${error.message}</p>
                <button onclick="getRecommendations()" class="btn btn-warning">Try Again</button>
            </div>
        `;
    }
}