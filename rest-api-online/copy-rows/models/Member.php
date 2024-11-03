<?php
class Member {
    private $conn;
    private $table_name = "members";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT id, title, image, release_at, summary FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function importCSV($data) {
        $query = "INSERT INTO " . $this->table_name . " (title, image, release_at, summary) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        foreach($data as $row) {
            $release_date = $this->formatDate($row[2]); // Assuming release_at is at index 2
            $stmt->execute([$row[0], $row[1], $release_date, $row[3]]);
        }
        return true;
    }

    private function formatDate($date) {
        // Handle both date formats
        $formats = ['m/d/Y', 'y/m/d'];
        foreach ($formats as $format) {
            $d = DateTime::createFromFormat($format, $date);
            if ($d && $d->format($format) === $date) {
                return $d->format('Y-m-d');
            }
        }
        return null;
    }
}
?>