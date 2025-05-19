<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$env = parse_ini_file(__DIR__ . '/.env');
$spoonacularApiKey = $env["SPOONACULAR_API_KEY"] ?? '';

$recipeId = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$recipeId || $recipeId <= 0) {
  http_response_code(400);
  die(json_encode([
    'error' => 'Valid recipe ID is required',
    'debug' => ['received_id' => $_GET['id'] ?? null]
  ]));
}

if (isset($_SESSION['recipes'][$recipeId])) {
  echo json_encode($_SESSION['recipes'][$recipeId]);
  exit;
}

$url = "https://api.spoonacular.com/recipes/{$recipeId}/information?apiKey={$spoonacularApiKey}&includeNutrition=false";
$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_FAILONERROR => true,
  CURLOPT_TIMEOUT => 15
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
  http_response_code(500);
  die(json_encode([
    'error' => 'API request failed',
    'curl_error' => $curlError,
    'api_url' => $url
  ]));
}

$data = json_decode($response, true);
if (!$data || isset($data['status']) && $data['status'] === 'failure') {
  http_response_code(502);
  die(json_encode([
    'error' => 'Invalid API response',
    'debug' => ['raw_response' => $response]
  ]));
}

$recipe = [
  'id' => $recipeId,
  'title' => $data['title'] ?? 'Unknown Recipe',
  'image' => $data['image'] ?? '',
  'summary' => strip_tags($data['summary'] ?? ''),
  'ingredients' => array_map(fn($i) => $i['original'], $data['extendedIngredients'] ?? []),
  'instructions' => isset($data['analyzedInstructions'][0]['steps'])
    ? array_column($data['analyzedInstructions'][0]['steps'], 'step')
    : ['No instructions available'],
  'readyInMinutes' => $data['readyInMinutes'] ?? 0,
  'servings' => $data['servings'] ?? 1,
  'sourceUrl' => $data['sourceUrl'] ?? ''
];

$_SESSION['recipes'][$recipeId] = $recipe;
echo json_encode($recipe);
