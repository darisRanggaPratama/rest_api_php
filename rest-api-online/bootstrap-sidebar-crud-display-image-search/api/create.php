<?php
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../models/Member.php';

$database = new Database();
$db = $database->getConnection();
$member = new Member($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $image = $_POST['image'] ?? '';
    $release_at = $_POST['release_at'] ?? '';
    $summary = $_POST['summary'] ?? '';

    if ($member->create($title, $image, $release_at, $summary)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>