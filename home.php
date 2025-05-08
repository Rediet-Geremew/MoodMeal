<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

include('db.php');

try {
    $stmt = $conn->prepare("SELECT * FROM journal_entries WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5");
    $stmt->execute(['user_id' => $user_id]);
    $journal_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT * FROM favorite_recipes WHERE user_id = :user_id LIMIT 5");
    $stmt->execute(['user_id' => $user_id]);
    $favorite_recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Mood Meal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <div class="header-content">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<div class="container">
    <section>
        <h2>Your Recent Journal Entries</h2>
        <div class="card-container">
            <?php if (count($journal_entries) > 0): ?>
                <?php foreach ($journal_entries as $entry): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($entry['food_title'] ?? 'Untitled'); ?></h3>
                        <p><strong>Recipe:</strong> <?php echo htmlspecialchars($entry['recipe'] ?? 'N/A'); ?></p>
                        <p><strong>Feelings:</strong> <?php echo htmlspecialchars($entry['entry_text']); ?></p>
                        <small><em>Posted on: <?php echo date("F j, Y, g:i a", strtotime($entry['created_at'])); ?></em></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No journal entries found.</p>
            <?php endif; ?>
        </div>
    </section>

    <section>
        <h2>Your Favorite Recipes</h2>
        <div class="card-container">
            <?php if (count($favorite_recipes) > 0): ?>
                <?php foreach ($favorite_recipes as $recipe): ?>
                    <div class="card">
                        <h3><?php echo htmlspecialchars($recipe['recipe_name']); ?></h3>
                        <p><strong>Ingredients:</strong> <?php echo htmlspecialchars($recipe['ingredients'] ?? 'N/A'); ?></p>
                        <p><strong>Instructions:</strong> <?php echo htmlspecialchars($recipe['instructions'] ?? 'N/A'); ?></p>
                        <?php if (!empty($recipe['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="Recipe Image" class="recipe-img">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No favorite recipes found.</p>
            <?php endif; ?>
        </div>
    </section>
</div>

</body>
</html>
