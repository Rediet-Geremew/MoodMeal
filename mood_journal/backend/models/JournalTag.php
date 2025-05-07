<?php
class JournalTag {
    public $tag_name;

    public function __construct($tag_name) {
        $this->tag_name = $tag_name;
    }

    public function save($conn) {
        $stmt = $conn->prepare("INSERT INTO Journal_Tags (tag_name) VALUES (?)");

        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            return false;
        }

        $stmt->bind_param("s", $this->tag_name);
        return $stmt->execute();
    }

    public static function exists($conn, $tag_name) {
        $stmt = $conn->prepare("SELECT tag_id FROM Journal_Tags WHERE tag_name = ?");
        if (!$stmt) return false;

        $stmt->bind_param("s", $tag_name);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }
}
?>
