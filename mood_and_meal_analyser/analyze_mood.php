<?php

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
