<?php
require_once 'config/database.php';

try {
    $query = $connect->query("SELECT * FROM members");
    $data = $query->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['data' => $data]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['data' => []]);
}
?>