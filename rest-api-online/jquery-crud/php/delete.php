<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootPath = dirname(dirname(__FILE__));
require_once $rootPath . '/config/database.php';

$id = $_POST['id'];

$sql = "DELETE FROM members WHERE id = :id";
$stmt = $pdo->prepare($sql);

try {
    $stmt->execute(['id' => $id]);
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>