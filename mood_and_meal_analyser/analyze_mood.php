<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

// Load environment variables from .env file in root
$dotenv = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/Mood_Meal/.env');
$apiKey = $dotenv['MOODANALYSERKEY'] ?? '';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT mood_before, mood_after, food_name, entry_date FROM journal_entries WHERE user_id = :user_id ORDER BY entry_date DESC LIMIT 50");
    $stmt->execute(['user_id' => $userId]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

if (empty($entries)) {
    echo json_encode(['error' => 'No journal entries found']);
    exit;
}
$prompt = "You are a thoughtful food and mood journal analyst. Given the following entries describing the user's mood before and after eating certain foods, please analyze and provide detailed insights:\n";
$prompt .= "- Which foods seem to improve the mood and why.\n";
$prompt .= "- Which foods may not help or might worsen mood.\n";
$prompt .= "- Any patterns or trends between types of food and emotional changes.\n";
$prompt .= "- Suggestions or hypotheses for the user based on this limited data.\n";
$prompt .= "- Do not say 'No analysis available'. Instead, provide your best thoughtful response.\n\n";

foreach ($entries as $entry) {
    $prompt .= sprintf(
        "Date: %s | Food: %s | Mood Before: %s | Mood After: %s\n",
        $entry['entry_date'],
        $entry['food_name'],
        $entry['mood_before'],
        $entry['mood_after']
    );
}

$prompt .= "\nPlease provide a compassionate, insightful analysis now.";


$apiUrl = 'https://openrouter.ai/api/v1/chat/completions';

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey,
    'Referer: http://localhost/',  
    'X-Title: Mood Food Local Test', 
];

$data = [
    'model' => 'openai/gpt-3.5-turbo',  
    'messages' => [
        ['role' => 'system', 'content' => 'You are a helpful assistant that analyzes mood and food data.'],
        ['role' => 'user', 'content' => $prompt]
    ],
    'max_tokens' => 1000,
    'temperature' => 0.7,
];

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERAGENT => 'MoodFoodTracker/1.0 (Local Development; http://localhost)',
    CURLOPT_SSL_VERIFYPEER => false 
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode([
        'error' => 'cURL error: ' . curl_error($ch),
        'curl_info' => curl_getinfo($ch)
    ]);
    exit;
}

curl_close($ch);

$aiResponse = json_decode($response, true);


file_put_contents('api_debug.log', print_r([
    'request' => $data,
    'response' => $aiResponse,
    'timestamp' => date('Y-m-d H:i:s')
], true), FILE_APPEND);

if (isset($aiResponse['error'])) {
    echo json_encode(['error' => 'AI API Error: ' . $aiResponse['error']['message']]);
    exit;
}

$aiInsight = $aiResponse['choices'][0]['message']['content'] ?? 'No analysis generated. Please check your data.';

header('Content-Type: application/json');
echo json_encode([
    'entries' => $entries,
    'ai_insight' => $aiInsight
]);
