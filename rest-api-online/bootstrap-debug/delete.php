<?php
// Pastikan header JSON dikirim sebelum output apapun
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'check-db.php';
require_once 'Member.php';

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Class untuk custom exception
class DeleteException extends Exception
{
    protected $context;

    public function __construct($message, $code = 0, $context = [])
    {
        parent::__construct($message, $code);
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }
}

try {
    // Validasi request method
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        throw new DeleteException(
            'Invalid request method. Only DELETE is allowed.',
            400,
            ['method' => $_SERVER['REQUEST_METHOD']]
        );
    }

    // Start session jika belum dimulai
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Validasi CSRF token
    $csrfHeader = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '';
    if (empty($csrfHeader) || empty($_SESSION['csrf_token']) || !hash_equals($csrfHeader, $_SESSION['csrf_token'])) {
        throw new DeleteException('Invalid CSRF token', 403);
    }

    // Validasi ID
    if (!isset($_GET['id'])) {
        throw new DeleteException('Member ID is required', 400);
    }

    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        throw new DeleteException(
            'Invalid member ID format',
            400,
            ['provided_id' => $_GET['id']]
        );
    }

    // Database connection
    $database = Database::getInstance();
    $db = $database->getConnection();
    $member = Member::getInstance($db);

    // Set PDO attributes
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Start transaction
    $db->beginTransaction();

    try {
        // Check if member exists
        $existingMember = $member->getOne($id);
        if (!$existingMember) {
            throw new DeleteException(
                'Member not found',
                404,
                ['member_id' => $id]
            );
        }

        // Delete member
        if (!$member->delete($id)) {
            throw new DeleteException(
                'Failed to delete member',
                500,
                ['member_id' => $id]
            );
        }

        $db->commit();

        // Success response
        echo json_encode([
            'success' => true,
            'message' => 'Member successfully deleted',
            'data' => [
                'deletedId' => $id
            ]
        ]);

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

} catch (DeleteException $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'errorCode' => $e->getCode(),
        'context' => $e->getContext()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An unexpected error occurred',
        'errorCode' => 500
    ]);
}