<?php
// Start session
session_start();

// Check if user is logged in
if ($_SESSION['status'] != "sudah_login") {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Verify CSRF token
$headers = getallheaders();
if (!isset($headers['X-CSRF-Token']) || $headers['X-CSRF-Token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !is_numeric($input['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid member ID']);
    exit;
}

require_once '../config/check-db.php';
require_once '../models/Member.php';

try {
    // Get database connection
    $database = Database::getInstance();
    $db = $database->getConnection();

    // Delete member
    $query = "DELETE FROM " . Member::getTableName() . " WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $input['id'], PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Check if any rows were affected
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Member deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Member not found']);
        }
    } else {
        throw new Exception('Failed to delete member');
    }

} catch (Exception $e) {
    error_log('Delete member error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred while deleting member'
    ]);
}