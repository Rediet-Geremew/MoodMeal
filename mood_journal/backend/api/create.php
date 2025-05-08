<?php
session_start();
require_once __DIR__ . '/../models/db.php';
require_once __DIR__ . '/../models/JournalEntry.php';

header('Content-Type: application/json');

try {
    // Validate session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in', 401);
    }

    // Handle file upload
    $image_url = '';
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/MoodMeal/mood_journal/frontend/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image_url = 'uploads/' . $filename;
        }
    }

    // Validate required fields
    $required = ['mood_before', 'mood_after', 'food_name', 'food_type', 'entry_date'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field", 422);
        }
    }

    // Create and save entry
    $entry = new JournalEntry(
        $_SESSION['user_id'],
        $_POST['mood_before'],
        $_POST['mood_after'],
        $_POST['food_name'],
        $_POST['food_type'],
        $image_url,
        $_POST['journal_text'] ?? '',
        $_POST['entry_date']
    );

    if (!$entry->isValid()) {
        throw new Exception('Invalid entry data', 422);
    }

    if ($entry->save($conn)) {
        echo json_encode([
            'success' => true,
            'message' => 'Entry saved successfully',
            'journal_id' => $conn->lastInsertId()
        ]);
    } else {
        throw new Exception('Failed to save entry', 500);
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}