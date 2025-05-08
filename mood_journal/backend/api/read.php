<?php
session_start();
require_once __DIR__ . '/../models/db.php';

header('Content-Type: application/json');

try {
    // Validate session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in', 401);
    }

    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("
        SELECT 
            journal_id,
            mood_before,
            mood_after,
            food_name,
            food_type,
            image_url,
            journal_text,
            entry_date,
            created_at
        FROM Journal_entries
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    
    $stmt->execute([$user_id]);
    $entries = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'entries' => $entries
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}