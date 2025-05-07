<?php
class JournalTagMap {
    public $journal_id;
    public $tag_id;

    public function __construct($journal_id, $tag_id) {
        $this->journal_id = $journal_id;
        $this->tag_id = $tag_id;
    }

    public function save($conn) {
        $stmt = $conn->prepare("INSERT INTO Journal_Tag_Map (journal_id, tag_id) VALUES (?, ?)");

        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }

        $stmt->bind_param("ii", $this->journal_id, $this->tag_id);
        return $stmt->execute();
    }

    public static function addTagsToJournal($conn, $journal_id, $tag_ids) {
        foreach ($tag_ids as $tag_id) {
            $map = new JournalTagMap($journal_id, $tag_id);
            if (!$map->save($conn)) {
                return false;
            }
        }
        return true;
    }
}
?>
