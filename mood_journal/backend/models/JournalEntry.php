<?php
class JournalEntry {
    private $user_id;
    private $mood_before;
    private $mood_after;
    private $food_name;
    private $food_type;
    private $image_url;
    private $journal_text;
    private $entry_date;

    public function __construct($user_id, $mood_before, $mood_after, $food_name, $food_type, $image_url, $journal_text, $entry_date) {
        $this->user_id = $user_id;
        $this->mood_before = $mood_before;
        $this->mood_after = $mood_after;
        $this->food_name = $food_name;
        $this->food_type = $food_type;
        $this->image_url = $image_url;
        $this->journal_text = $journal_text;
        $this->entry_date = $entry_date;
    }

    public function isValid() {
        return !empty($this->mood_before) && 
               !empty($this->mood_after) && 
               !empty($this->food_name) && 
               !empty($this->food_type) && 
               !empty($this->entry_date);
    }

    public function save($conn) {
        $stmt = $conn->prepare("INSERT INTO Journal_Entries 
            (user_id, mood_before, mood_after, food_name, food_type, image_url, journal_text, entry_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
        return $stmt->execute([
            $this->user_id,
            $this->mood_before,
            $this->mood_after,
            $this->food_name,
            $this->food_type,
            $this->image_url,
            $this->journal_text,
            $this->entry_date
        ]);
    }
}