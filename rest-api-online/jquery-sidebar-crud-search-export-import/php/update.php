<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootPath = dirname(dirname(__FILE__));
require_once $rootPath . '/config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

$sql = "UPDATE members SET title = :title, image = :image, release_at = :release_at, summary = :summary WHERE id = :id";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
        'id' => $data['id'],
        'title' => $data['title'],
        'image' => $data['image'],
        'release_at' => $data['release_at'],
        'summary' => $data['summary']
    ]);
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>