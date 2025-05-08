<?php
session_start();
header("Content-Type: application/json");


require_once "db.php"; 
require_once "JournalEntry.php";
require_once "JournalTag.php";
require_once "JournalTagMap.php";


if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "User not logged in."]);
    exit;
}

$user_id = $_SESSION['user_id'];


$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON input."]);
    exit;
}

// Extract fields
$mood_before   = trim($data['mood_before'] ?? '');
$mood_after    = trim($data['mood_after'] ?? '');
$food_name     = trim($data['food_name'] ?? '');
$food_type     = trim($data['food_type'] ?? '');
$image_url     = trim($data['image_url'] ?? '');
$journal_text  = trim($data['journal_text'] ?? '');
$entry_date    = trim($data['entry_date'] ?? '');
$tags          = $data['tags'] ?? []; 

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


$tag_ids = [];

foreach ($tags as $tag_name) {
    $tag_name = trim($tag_name);
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


if (!JournalTagMap::addTagsToJournal($conn, $journal_id, $tag_ids)) {
    http_response_code(500);
    echo json_encode(["error" => "Journal saved, but failed to assign tags."]);
    exit;
}

echo json_encode(["success" => true, "journal_id" => $journal_id]);
?>
