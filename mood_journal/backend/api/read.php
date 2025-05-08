<?php
header('Content-Type: application/json');


session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

require_once 'db.php'; 

$user_id = $_SESSION['user_id'];
$limit = 7; 


$sql = "
    SELECT 
      Journal_Entries.journal_id,
      Journal_Entries.user_id,
      Journal_Entries.mood_before,
      Journal_Entries.mood_after,
      Journal_Entries.food_name,
      Journal_Entries.food_type,
      Journal_Entries.image_url,
      Journal_Entries.journal_text,
      Journal_Entries.entry_date,
      Journal_Entries.created_at,
      GROUP_CONCAT(Journal_Tags.tag_name) AS tags
    FROM Journal_Entries

    LEFT JOIN Journal_Tag_Map
      ON Journal_Entries.journal_id = Journal_Tag_Map.journal_id

    LEFT JOIN Journal_Tags
      ON Journal_Tag_Map.tag_id = Journal_Tags.tag_id

    WHERE Journal_Entries.user_id = ?

    GROUP BY Journal_Entries.journal_id

    ORDER BY Journal_Entries.created_at DESC

    LIMIT ?
";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ii", $user_id, $limit);

$stmt->execute();


$result = $stmt->get_result();


$entries = [];

while ($row = $result->fetch_assoc()) {
    $row['tags'] = $row['tags'] ? explode(',', $row['tags']) : [];
    $entries[] = $row;
}


echo json_encode(['success' => true, 'entries' => $entries]);
