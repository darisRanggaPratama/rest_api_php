<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Member
{
    // Konstanta untuk nama tabel & pesan error
    protected const TABLE_NAME = "members";
    protected const ERROR_MESSAGES = [
        'connection' => 'Database connection error occurred',
        'not_found' => 'Member not found',
        'invalid_id' => 'Invalid member ID provided',
        'create_failed' => 'Failed to create member',
        'update_failed' => 'Failed to update member',
        'delete_failed' => 'Failed to delete member',
        'invalid_data' => 'Invalid data provided'
    ];

    // Properties dengan protected visibility
    protected $conn;
    protected static $instance = null;
    protected $lastError = null;
    protected $logger;

    // Constructor dengan dependency injection
    public function __construct($db = null)
    {
        if ($db === null) {
            throw new InvalidArgumentException(self::ERROR_MESSAGES['connection']);
        }
        $this->conn = $db;
        $this->initializeLogger();
    }

    // Singleton pattern implementation
    public static function getInstance($db = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($db);
        }
        return self::$instance;
    }

    // Initialize logger
    protected function initializeLogger()
    {
        $this->logger = function ($message, $level = 'error') {
            $logFile = __DIR__ . '/logs/member_' . date('Y-m-d') . '.log';
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[$timestamp][$level] $message" . PHP_EOL;
            error_log($logMessage, 3, $logFile);
        };
    }

    // Validation methods
    protected function validateId($id)
    {
        if (!is_numeric($id) || $id <= 0) {
            throw new InvalidArgumentException(self::ERROR_MESSAGES['invalid_id']);
        }
        return filter_var($id, FILTER_VALIDATE_INT);
    }

    protected function validateData($data)
    {
        $required = ['title', 'release_at'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }

        // Validate date format
        if (!strtotime($data['release_at'])) {
            throw new InvalidArgumentException("Invalid date format for release_at");
        }

        return true;
    }

    // Enhanced getAll method with pagination and better error handling
    public function getAll($search = '', $page = 1, $limit = 10)
    {
        try {
            $offset = ($page - 1) * $limit;

            // Base query
            $query = "SELECT SQL_CALC_FOUND_ROWS * FROM " . self::TABLE_NAME;

            // Add search condition if provided
            if (!empty($search)) {
                $query .= " WHERE title LIKE :search 
                           OR release_at LIKE :search 
                           OR summary LIKE :search";
            }

            // Add pagination
            $query .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query);

            // Bind parameters
            if (!empty($search)) {
                $searchTerm = "%{$search}%";
                $stmt->bindParam(":search", $searchTerm, PDO::PARAM_STR);
            }

            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count
            $totalStmt = $this->conn->query("SELECT FOUND_ROWS()");
            $total = $totalStmt->fetchColumn();

            return [
                'data' => $results,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ];

        } catch (PDOException $e) {
            $this->logError('getAll failed: ' . $e->getMessage());
            throw new RuntimeException(self::ERROR_MESSAGES['connection']);
        }
    }

    // Enhanced getOne method
    public function getOne($id)
    {
        try {
            $id = $this->validateId($id);

            $query = "SELECT * FROM " . self::TABLE_NAME . " WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                throw new RuntimeException(self::ERROR_MESSAGES['not_found']);
            }

            return $result;

        } catch (InvalidArgumentException $e) {
            $this->logError('Invalid ID provided: ' . $e->getMessage());
            throw $e;
        } catch (PDOException $e) {
            $this->logError('getOne failed: ' . $e->getMessage());
            throw new RuntimeException(self::ERROR_MESSAGES['connection']);
        }
    }

    // Enhanced create method
    public function create(array $data)
    {
        try {
            $this->validateData($data);

            $query = "INSERT INTO " . self::TABLE_NAME . "
                     (title, image, release_at, summary) 
                     VALUES (:title, :image, :release_at, :summary)";

            $stmt = $this->conn->prepare($query);

            // Sanitize and bind all parameters
            foreach (['title', 'image', 'release_at', 'summary'] as $field) {
                $value = isset($data[$field]) ?
                    htmlspecialchars(strip_tags($data[$field])) : null;
                $stmt->bindValue(":{$field}", $value);
            }

            $result = $stmt->execute();

            if (!$result) {
                throw new RuntimeException(self::ERROR_MESSAGES['create_failed']);
            }

            return $this->conn->lastInsertId();

        } catch (PDOException $e) {
            $this->logError('Create failed: ' . $e->getMessage());
            throw new RuntimeException(self::ERROR_MESSAGES['create_failed']);
        }
    }


    public function update($id, array $data)
    {
        try {
            $id = $this->validateId($id);

            // Modifikasi validasi data untuk mengizinkan beberapa field opsional
            if (empty($data['title'])) {
                throw new InvalidArgumentException("Title is required");
            }

            if (empty($data['release_at']) || !strtotime($data['release_at'])) {
                throw new InvalidArgumentException("Valid release date is required");
            }

            // Check if record exists
            $existingMember = $this->getOne($id);
            if (!$existingMember) {
                throw new RuntimeException(self::ERROR_MESSAGES['not_found']);
            }

            $query = "UPDATE " . self::TABLE_NAME . "
                 SET title = :title, 
                     image = :image, 
                     release_at = :release_at, 
                     summary = :summary
                    
                 WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            // Sanitize and bind all parameters
            $stmt->bindValue(":title", htmlspecialchars(strip_tags($data['title'])));
            $stmt->bindValue(":image", isset($data['image']) ? htmlspecialchars(strip_tags($data['image'])) : '');
            $stmt->bindValue(":release_at", date('Y-m-d', strtotime($data['release_at'])));
            $stmt->bindValue(":summary", isset($data['summary']) ? htmlspecialchars(strip_tags($data['summary'])) : '');
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                throw new RuntimeException("Database error: " . ($errorInfo[2] ?? self::ERROR_MESSAGES['update_failed']));
            }

            return true;

        } catch (PDOException $e) {
            $this->logError('Update failed: ' . $e->getMessage());
            throw new RuntimeException(self::ERROR_MESSAGES['update_failed'] . ': ' . $e->getMessage());
        } catch (Exception $e) {
            $this->logError('Update failed: ' . $e->getMessage());
            throw new RuntimeException($e->getMessage());
        }
    }

    // Enhanced delete method
    public function delete($id)
    {
        try {
            $id = $this->validateId($id);

            // Check if record exists
            if (!$this->getOne($id)) {
                throw new RuntimeException(self::ERROR_MESSAGES['not_found']);
            }

            $query = "DELETE FROM " . self::TABLE_NAME . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);

            $result = $stmt->execute();

            if (!$result) {
                throw new RuntimeException(self::ERROR_MESSAGES['delete_failed']);
            }

            return true;

        } catch (PDOException $e) {
            $this->logError('Delete failed: ' . $e->getMessage());
            throw new RuntimeException(self::ERROR_MESSAGES['delete_failed']);
        }
    }

    // Error handling methods
    public function logError($message)
    {
        if (is_callable($this->logger)) {
            call_user_func($this->logger, $message);
        }
        $this->lastError = $message;
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    // Getter methods
    public function getConnection()
    {
        return $this->conn;
    }

    public static function getTableName()
    {
        return self::TABLE_NAME;
    }
}
