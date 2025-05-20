<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

$env = parse_ini_file(__DIR__ . '/.env');
$apiKey = $env["OPENROUTER_API_KEY"] ?? '';
$spoonacularApiKey = $env["SPOONACULAR_API_KEY"] ?? '';

if (!$apiKey || !$spoonacularApiKey) {
    echo json_encode(["error" => "API key missing"]);
    exit;
}

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);
$mood = trim($data["mood"] ?? '');

if (!$mood) {
    echo json_encode(["error" => "No mood provided"]);
    exit;
}

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

// Get AI recommendations
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

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "model" => "openai/gpt-3.5-turbo",
    "messages" => [
        ["role" => "system", "content" => "You are a helpful assistant that replies with only valid JSON."],
        ["role" => "user", "content" => $prompt]
    ],
    "temperature" => 0.7
]));

$response = curl_exec($ch);
curl_close($ch);

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

// Verify meals with Spoonacular
$validMeals = [];
foreach ($mealsData["meals"] as $item) {
    $mealName = $item["meal"];
    $description = $item["description"];

    // Search Spoonacular for this meal
    $spoonacularUrl = "https://api.spoonacular.com/recipes/complexSearch?" .
        http_build_query([
            'apiKey' => $spoonacularApiKey,
            'query' => $mealName,
            'number' => 1
        ]);

    $spoonacularResponse = @file_get_contents($spoonacularUrl);

    if ($spoonacularResponse !== false) {
        $spoonacularData = json_decode($spoonacularResponse, true);

        if (!empty($spoonacularData["results"])) {
            $result = $spoonacularData["results"][0];
            $validMeals[] = [
                "meal" => $mealName,
                "description" => $description,
                "image" => $result["image"] ?? "https://via.placeholder.com/300",
                "spoonacular_id" => $result["id"],
                "readyInMinutes" => 0 // Will be filled later
            ];

            // Stop if we have enough valid meals
            if (count($validMeals) >= 3) break;
        }
    }
}

// If no valid meals found, return error
if (empty($validMeals)) {
    echo json_encode(["error" => "No valid recipes found for your mood in our database"]);
    exit;
}

// Get additional details for valid meals
foreach ($validMeals as &$meal) {
    $detailsUrl = "https://api.spoonacular.com/recipes/{$meal['spoonacular_id']}/information?" .
        http_build_query(['apiKey' => $spoonacularApiKey]);

    $detailsResponse = @file_get_contents($detailsUrl);

    if ($detailsResponse !== false) {
        $details = json_decode($detailsResponse, true);
        $meal["readyInMinutes"] = $details["readyInMinutes"] ?? 0;
        $meal["image"] = $details["image"] ?? $meal["image"];
    }
}

echo json_encode(["meals" => $validMeals]);
