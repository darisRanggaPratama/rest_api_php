<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
class Member {
    private $conn;
    private $table_name = "members";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($search = '') {
        try {
            $query = "SELECT * FROM " . $this->table_name;
            if (!empty($search)) {
                $query .= " WHERE title LIKE :search 
                           OR release_at LIKE :search 
                           OR summary LIKE :search";
            }
            $query .= " ORDER BY id DESC";

            $stmt = $this->conn->prepare($query);
            
            if (!empty($search)) {
                $searchTerm = "%{$search}%";
                $stmt->bindParam(":search", $searchTerm);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getOne($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return array(
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'image' => $row['image'],
                    'release_at' => $row['release_at'],
                    'summary' => $row['summary']
                );
            }
            return false;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function create($title, $image, $release_at, $summary) {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                     (title, image, release_at, summary) 
                     VALUES (:title, :image, :release_at, :summary)";

            $stmt = $this->conn->prepare($query);

            // Sanitize inputs
            $title = htmlspecialchars(strip_tags($title));
            $image = htmlspecialchars(strip_tags($image));
            $release_at = htmlspecialchars(strip_tags($release_at));
            $summary = htmlspecialchars(strip_tags($summary));

            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":image", $image);
            $stmt->bindParam(":release_at", $release_at);
            $stmt->bindParam(":summary", $summary);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function update($id, $title, $image, $release_at, $summary) {
        try {
            $query = "UPDATE " . $this->table_name . "
                     SET title = :title, image = :image, 
                         release_at = :release_at, summary = :summary
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            // Sanitize inputs
            $id = htmlspecialchars(strip_tags($id));
            $title = htmlspecialchars(strip_tags($title));
            $image = htmlspecialchars(strip_tags($image));
            $release_at = htmlspecialchars(strip_tags($release_at));
            $summary = htmlspecialchars(strip_tags($summary));

            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":image", $image);
            $stmt->bindParam(":release_at", $release_at);
            $stmt->bindParam(":summary", $summary);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $id = htmlspecialchars(strip_tags($id));
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
?>