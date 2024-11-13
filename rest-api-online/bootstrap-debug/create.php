<?php
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'check-db.php';
require_once 'Member.php';

$database = Database::getInstance();
$db = $database->getConnection();
$member = Member::getInstance($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $image = isset($_POST['image']) ? trim($_POST['image']) : '';
    $release_at = isset($_POST['release_at']) ? trim($_POST['release_at']) : '';
    $summary = isset($_POST['summary']) ? trim($_POST['summary']) : '';

    // Validasi Input
    if (empty($title) || empty($release_at)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }

    // Penyaringan input
    $title = htmlspecialchars($title);
    $image = htmlspecialchars($image);
    $release_at = htmlspecialchars($release_at);
    $summary = htmlspecialchars($summary);

    try {
        $newMemberId = $member->create([
            'title' => $title,
            'image' => $image,
            'release_at' => $release_at,
            'summary' => $summary]);
        if ($newMemberId) {
            echo json_encode(['success' => true, 'info' => ['id' => $newMemberId]]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to create member']);
        }
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        $member->logError($errorMessage);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $errorMessage]);
    }
}
?>