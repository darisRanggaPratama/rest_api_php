<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootPath = dirname(dirname(__FILE__));
require_once $rootPath . '/config/database.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT * FROM members WHERE 
        title LIKE :search OR 
        release_at LIKE :search OR 
        summary LIKE :search 
        ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['search' => "%$search%"]);

while($row = $stmt->fetch()) {
    echo "<tr>";
    echo "<td>".$row['id']."</td>";
    echo "<td>".$row['title']."</td>";
    echo "<td><img src='".$row['image']."' width='50'></td>";
    echo "<td>".$row['release_at']."</td>";
    echo "<td>".$row['summary']."</td>";
    echo "<td>
            <button onclick='editMember(".$row['id'].")' class='btn btn-sm btn-warning'>Edit</button>
            <button onclick='deleteMember(".$row['id'].")' class='btn btn-sm btn-danger'>Delete</button>
          </td>";
    echo "</tr>";
}
?>