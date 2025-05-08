<?php
session_start();
header("Content-Type: application/json");

require_once "db.php"; 
require_once "JournalEntry.php";
require_once "JournalTag.php";
require_once "JournalTagMap.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "User not logged in."]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Extract POST fields
$mood_before   = trim($_POST['mood_before'] ?? '');
$mood_after    = trim($_POST['mood_after'] ?? '');
$food_name     = trim($_POST['food_name'] ?? '');
$food_type     = trim($_POST['food_type'] ?? '');
$image_url     = ''; // default to empty
$journal_text  = trim($_POST['journal_text'] ?? '');
$entry_date    = trim($_POST['entry_date'] ?? '');

// Handle tags as a comma-separated string
$tags_raw = trim($_POST['tags'] ?? '');
$tags = array_filter(array_map('trim', explode(',', $tags_raw)));

// Handle image upload if provided
if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    $imageTmpName = $_FILES['image']['tmp_name'];
    $imageName = basename($_FILES['image']['name']);

    $uploadDir = __DIR__ . "/uploads/"; // Make sure this directory exists and is writable
    $relativePath = "uploads/" . uniqid() . "_" . $imageName;
    $targetPath = __DIR__ . "/" . $relativePath;

    if (move_uploaded_file($imageTmpName, $targetPath)) {
        $image_url = $relativePath; // Save relative URL for DB
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to upload image."]);
        exit;
    }
}

// Create and validate journal entry
$entry = new JournalEntry($user_id, $mood_before, $mood_after, $food_name, $food_type, $image_url, $journal_text, $entry_date);

if (!$entry->isValid()) {
    http_response_code(422);
    echo json_encode(["error" => "Missing required fields."]);
    exit;
}

if (!$entry->save($conn)) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to save journal entry."]);
    exit;
}

$journal_id = $conn->insert_id;

// Handle tags
$tag_ids = [];

foreach ($tags as $tag_name) {
    if ($tag_name === '') continue;

    if (!JournalTag::exists($conn, $tag_name)) {
        $tag = new JournalTag($tag_name);
        $tag->save($conn);
    }

    $stmt = $conn->prepare("SELECT tag_id FROM Journal_Tags WHERE tag_name = ?");
    $stmt->bind_param("s", $tag_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $tag_ids[] = $row['tag_id'];
    }
}

// Map tags to the journal entry
if (!JournalTagMap::addTagsToJournal($conn, $journal_id, $tag_ids)) {
    http_response_code(500);
    echo json_encode(["error" => "Journal saved, but failed to assign tags."]);
    exit;
}

// Success
echo json_encode([
    "success" => true,
    "journal_id" => $journal_id
]);
?>
