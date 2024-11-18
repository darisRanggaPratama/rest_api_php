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
    if (!isset($_GET['id'])) {
        throw new Exception('ID is required');
    }

    $database = Database::getInstance();
    $db = $database->getConnection();
    $member = Member::getInstance($db);

    $data = $member->getOne($_GET['id']);
    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
