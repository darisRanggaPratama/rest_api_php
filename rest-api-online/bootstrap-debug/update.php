<?php
// Pastikan tidak ada output sebelum header
ob_start();

// Set header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-CSRF-Token');

// Turn off error display but keep logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start session for CSRF protection
session_start();

require_once 'check-db.php';
require_once 'Member.php';

// Function to send JSON response
function sendJsonResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    ob_clean(); // Clear any output buffers
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Function to sanitize input
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Function to validate date format
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

try {
    // Verify request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(false, 'Invalid request method. Only POST requests are allowed.', null, 405);
    }

    // Get and decode POST data
    $postData = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        sendJsonResponse(false, 'Invalid JSON data received: ' . json_last_error_msg(), null, 400);
    }

    if (empty($postData)) {
        sendJsonResponse(false, 'No data received.', null, 400);
    }

    // Sanitize input data
    $postData = sanitizeInput($postData);

    // Validate required fields
    $required_fields = ['id', 'title', 'image', 'release_at', 'summary'];
    $missing_fields = array_filter($required_fields, function($field) use ($postData) {
        return !isset($postData[$field]) || trim($postData[$field]) === '';
    });

    if (!empty($missing_fields)) {
        sendJsonResponse(false, 'Missing required fields: ' . implode(', ', $missing_fields), null, 400);
    }

    // Additional validation
    if (!is_numeric($postData['id']) || $postData['id'] <= 0) {
        sendJsonResponse(false, 'Invalid ID format.', null, 400);
    }

    if (strlen($postData['title']) > 255) {
        sendJsonResponse(false, 'Title is too long. Maximum 255 characters allowed.', null, 400);
    }

    if (!filter_var($postData['image'], FILTER_VALIDATE_URL)) {
        sendJsonResponse(false, 'Invalid image URL format.', null, 400);
    }

    if (!validateDate($postData['release_at'])) {
        sendJsonResponse(false, 'Invalid date format. Please use YYYY-MM-DD format.', null, 400);
    }

    // Initialize database connection
    $database = Database::getInstance();
    $db = $database->getConnection();

    if (!$db) {
        sendJsonResponse(false, 'Database connection failed.', null, 500);
    }

    $member = Member::getInstance($db);

    // Start transaction
    $db->beginTransaction();

    try {
        // Check if record exists before update
        $existingMember = $member->getOne($postData['id']);

        if (!$existingMember) {
            $db->rollBack();
            sendJsonResponse(false, 'Member not found.', null, 404);
        }

        // Attempt to update
        $updateResult = $member->update(
            $postData['id'],
            [
                'title' => $postData['title'],
                'image' => $postData['image'],
                'release_at' => $postData['release_at'],
                'summary' => $postData['summary']
            ]
        );

        if (!$updateResult) {
            throw new Exception('Failed to update member.');
        }

        // Commit transaction
        $db->commit();

        // Log successful update
        error_log(sprintf(
            "Member updated successfully - ID: %d, Title: %s, Date: %s",
            $postData['id'],
            $postData['title'],
            date('Y-m-d H:i:s')
        ));

        sendJsonResponse(true, 'Member updated successfully', $postData);

    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    error_log('Update error: ' . $e->getMessage());
    sendJsonResponse(false, $e->getMessage(), null, 500);
} finally {
    // Close database connection if open
    if (isset($database) && $database->isConnected()) {
        $database->closeConnection();
    }
}