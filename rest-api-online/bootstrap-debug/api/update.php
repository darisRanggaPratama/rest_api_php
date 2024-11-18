<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// CSRF Protection
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || $_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    die(json_encode(['error' => 'Invalid CSRF token']));
}

require_once '../config/check-db.php';
require_once '../models/Member.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $id = $_POST['id'] ?? null;
    if (!$id) {
        throw new Exception('ID is required');
    }

    $database = Database::getInstance();
    $db = $database->getConnection();
    $member = Member::getInstance($db);

        $data = [
        'id' => $id,
        'title' => $_POST['title'],
        'release_at' => $_POST['release_at'],
        'summary' => $_POST['summary'],
        'image' => $_POST['image']
    ];

    $member->update($data);
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}