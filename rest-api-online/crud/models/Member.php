<?php
class Member {
    private $conn;
    private $table_name = "members";

    public $id;
    public $title;
    public $image;
    public $release_at;
    public $summary;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all members
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create member
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET title=:title, image=:image, release_at=:release_at, summary=:summary";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":release_at", $this->release_at);
        $stmt->bindParam(":summary", $this->summary);

        return $stmt->execute();
    }

    // Update member
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET title=:title, image=:image, release_at=:release_at, summary=:summary 
                WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":image", $this->image);
        $stmt->bindParam(":release_at", $this->release_at);
        $stmt->bindParam(":summary", $this->summary);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Delete member
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
}