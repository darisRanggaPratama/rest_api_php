<?php
// Set header sebelum output apapun
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// Prevent PHP errors from breaking JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../config/database.php';
require_once '../models/Member.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $member = new Member($db);

    // Get POST data
    $postData = json_decode(file_get_contents('php://input'), true);
    if (!$postData) {
        $postData = $_POST; // Fallback to $_POST if JSON parsing fails
    }

    // Validate required fields
    $required_fields = ['id', 'title', 'image', 'release_at', 'summary'];
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (!isset($postData[$field]) || empty($postData[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields: ' . implode(', ', $missing_fields)]);
        exit;
    }

    // Attempt to update
    if ($member->update(
        $postData['id'],
        $postData['title'],
        $postData['image'],
        $postData['release_at'],
        $postData['summary']
    )) {
        http_response_code(200);
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update member']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>