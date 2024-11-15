<?php
// Set strict error reporting
declare(strict_types=1);
// Prevent any unwanted output
ob_start();
// Error handling
error_reporting(E_ALL);
ini_set('display_errors', "1");

// Set headers
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
header('Cache-Control: no-cache, must-revalidate');

// Set timeout limit
set_time_limit(30);

// Define custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $errorResponse = [
        'status' => 'error',
        'message' => 'An internal error occurred',
        'debug' => [
            'type' => $errno,
            'message' => $errstr,
            'file' => basename($errfile),
            'line' => $errline
        ]
    ];

    // Log error for debugging
    error_log(sprintf(
        "Error: [%s] %s in %s on line %d",
        $errno,
        $errstr,
        $errfile,
        $errline
    ));

    http_response_code(500);
    echo json_encode($errorResponse);
    exit;
}

// Set custom error handler
set_error_handler('customErrorHandler');

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Invalid request method. Only GET is allowed.', 405);
    }

    // Check if required files exist
    foreach (['check-db.php', 'Member.php'] as $file) {
        if (!file_exists($file)) {
            throw new Exception("Required file $file not found", 500);
        }
    }

    require_once 'check-db.php';
    require_once 'Member.php';

    // Validate and sanitize input
    if (!isset($_GET['id'])) {
        throw new Exception('ID parameter is required', 400);
    }

    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($id === false || $id < 1) {
        throw new Exception('Invalid ID format. Must be a positive integer.', 400);
    }

    // Memory limit check
    if (memory_get_usage() > 67108864) { // 64MB
        throw new Exception('Memory usage exceeded', 500);
    }

    // Database connection with timeout
    $startTime = microtime(true);
    $database = Database::getInstance();
    $db = $database->getConnection();

    if (microtime(true) - $startTime > 5) {
        throw new Exception('Database connection timeout', 504);
    }

    if (!$db) {
        throw new Exception('Database connection failed', 503);
    }

    // Set connection timeout
    $db->setAttribute(PDO::ATTR_TIMEOUT, 5);

    // Member instance
    $member = Member::getInstance($db);

    if (!$member) {
        throw new Exception('Failed to initialize Member class', 500);
    }

    // Get member data with execution time check
    $startTime = microtime(true);
    $result = $member->getOne($id);

    if (microtime(true) - $startTime > 3) {
        error_log("Warning: Slow query detected for member ID: $id");
    }

    // Validate result
    if ($result === false) {
        throw new Exception('Member not found', 404);
    }

    if (empty($result)) {
        throw new Exception('No data returned for the given ID', 404);
    }

    // Sanitize output
    $sanitizedResult = array_map(function($value) {
        return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
    }, $result);

    // Success response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => $sanitizedResult,
        'timestamp' => time()
    ]);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    http_response_code(503);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection error',
        'code' => $e->getCode()
    ]);
} catch (Exception $e) {
    error_log("Application Error: " . $e->getMessage());
    $httpCode = method_exists($e, 'getCode') && $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($httpCode);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'code' => $httpCode
    ]);
} finally {
    // Clean up
    if (isset($database)) {
        $database->closeConnection();
    }

    // Flush output buffer
    if (ob_get_length()) {
        ob_end_flush();
    }
}