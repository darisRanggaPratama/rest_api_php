<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootPath = dirname(dirname(__FILE__));
require_once $rootPath . '/config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

$sql = "INSERT INTO members (title, image, release_at, summary) VALUES (:title, :image, :release_at, :summary)";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
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