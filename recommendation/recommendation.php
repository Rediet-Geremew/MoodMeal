<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load API key
$env = parse_ini_file(__DIR__ . '/.env');
$apiKey = $env["OPENROUTER_API_KEY"] ?? '';
$spoonacularApiKey = $env["SPOONACULAR_API_KEY"] ?? ''; // Add Spoonacular API Key

if (!$apiKey || !$spoonacularApiKey) {
    echo json_encode(["error" => "API key missing"]);
    exit;
}

// Get input data
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);
$mood = trim($data["mood"] ?? '');

if (!$mood) {
    echo json_encode(["error" => "No mood provided"]);
    exit;
}

// Create OpenRouter prompt
$prompt = <<<EOD
Suggest 6 meals for someone who feels "$mood".
Return a valid JSON object in this format:
{
  "meals": [
    { "meal": "Meal Name 1", "description": "Short description 1" },
    { "meal": "Meal Name 2", "description": "Short description 2" },
    { "meal": "Meal Name 3", "description": "Short description 3" },
    { "meal": "Meal Name 4", "description": "Short description 4" },
    { "meal": "Meal Name 5", "description": "Short description 5" },
    { "meal": "Meal Name 6", "description": "Short description 6" }
  ]
}
Return ONLY the JSON object, no explanation.
EOD;

// Step 1: Fetch suggestions from OpenRouter
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://openrouter.ai/api/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey",
    "HTTP-Referer: http://localhost",
    "X-Title: MoodMeal"
]);

$model = "openai/gpt-3.5-turbo";
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "model" => $model,
    "messages" => [
        ["role" => "system", "content" => "You are a helpful assistant that replies with only valid JSON."],
        ["role" => "user", "content" => $prompt]
    ],
    "temperature" => 0.7
]));

$response = curl_exec($ch);
curl_close($ch);

// Handle OpenRouter response
$result = json_decode($response, true);
if (!isset($result["choices"][0]["message"]["content"])) {
    echo json_encode(["error" => "OpenRouter returned unexpected data"]);
    exit;
}

$mealsJson = $result["choices"][0]["message"]["content"];
$mealsData = json_decode($mealsJson, true);
if (!$mealsData || !isset($mealsData["meals"])) {
    echo json_encode(["error" => "Invalid meal format from OpenRouter"]);
    exit;
}

// Step 2: Use Spoonacular to enrich each meal
$finalMeals = [];
foreach ($mealsData["meals"] as $item) {
    $mealName = urlencode($item["meal"]);
    $description = $item["description"];
    $image = null;
    $recipeLink = null;

    // Search Spoonacular
    $spoonacularUrl = "https://api.spoonacular.com/recipes/complexSearch?query=$mealName&number=1&apiKey=$spoonacularApiKey";
    $spoonacularResponse = @file_get_contents($spoonacularUrl);

    if ($spoonacularResponse !== false) {
        $spoonacularData = json_decode($spoonacularResponse, true);

        if (!empty($spoonacularData["results"])) {
            // Fetch the first result
            $dbMeal = $spoonacularData["results"][0];
            $image = $dbMeal["image"] ?? "https://via.placeholder.com/150";
            $recipeLink = $dbMeal["sourceUrl"] ?? "#";
        }
    }

    // Fallback if no data found
    $finalMeals[] = [
        "meal" => $item["meal"],
        "description" => $description,
        "image" => $image ?? "https://via.placeholder.com/150",
        "recipe_link" => $recipeLink ?? "#"
    ];
}

// Output final JSON
echo json_encode(["meals" => $finalMeals], JSON_PRETTY_PRINT);
