<?php
// session_start(); // Uncomment if session handling is needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mood Meal | Home</title>
    <link rel="stylesheet" href="styles/home.css">
    <script defer src="scripts/home.js"></script>
</head>
<body>
    <header>
        <div class="logo">
            <img src="assets/images/logo.png" alt="Mood Meal Logo">
        </div>
        <nav>
            <ul>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">Signup</a></li>
                <li><a href="mood-analyzer.php">Mood Analyzer</a></li>
                <li><a href="recipes.php">Recipes</a></li>
                <li><a href="journal.php">Mood Journal</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <h1>"Eat what your heart feels."</h1>
        <h2 class="subtagline">Whether you're happy, tired, or just chill —<br> MoodMeal brings you meals that understand you.</h2>
        <a href="signup.php" class="cta-button">Get Started</a>
    </section>

    <section class="features">
   
        <div class="feature">
            <h2>Smart Mood Analyzer</h2>
            <p>Analyze how you feel and get curated recipes that lift or match your mood.</p>
          
        </div> 
        <div class="feature">
            <h2>Personalized Journal</h2>
            <p>Track your mood and meals daily to discover emotional eating patterns.</p>
        </div>
        <div class="feature">
            <h2>Easy Recipe Filters</h2>
            <p>Filter by mood, dietary needs, ingredients, or cooking time.</p>
        </div>
    </section>

    <footer>
        <p>&copy; 2025 Mood Meal. All rights reserved.</p>
    </footer>
</body>
</html>
