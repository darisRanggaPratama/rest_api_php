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
            // Handle 'all' case
            if ($limit === PHP_INT_MAX) {
                $offset = 0;
            } else {
                $offset = ($page - 1) * $limit;
            }

            $params = [];
            $conditions = [];

            // Base query
            $query = "SELECT SQL_CALC_FOUND_ROWS * FROM " . self::TABLE_NAME;

            // Add search condition if provided
            if (!empty($search)) {
                $conditions[] = "title LIKE :search_title";
                $conditions[] = "DATE_FORMAT(release_at, '%Y-%m-%d') LIKE :search_date1";
                $conditions[] = "DATE_FORMAT(release_at, '%d-%m-%Y') LIKE :search_date2";
                $conditions[] = "DATE_FORMAT(release_at, '%d/%m/%Y') LIKE :search_date3";
                $conditions[] = "DATE_FORMAT(release_at, '%m/%d/%Y') LIKE :search_date4";
                $conditions[] = "summary LIKE :search_summary";

                $searchTerm = "%$search%";
                $params[':search_title'] = $searchTerm;
                $params[':search_date1'] = $searchTerm;
                $params[':search_date2'] = $searchTerm;
                $params[':search_date3'] = $searchTerm;
                $params[':search_date4'] = $searchTerm;
                $params[':search_summary'] = $searchTerm;
            }

            // Add WHERE close if condition exist
            if (!empty($conditions)) {
                $query .= ' WHERE ' . implode(' OR ', $conditions);
            }

            // Add pagination
            $query .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
            $params['limit'] = (int)$limit;
            $params['offset'] = (int)$offset;

            $stmt = $this->conn->prepare($query);

            // Bind all parameters
            foreach ($params as $key => $value) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get total count
            $totalStmt = $this->conn->query("SELECT FOUND_ROWS()");
            $total = $totalStmt->fetchColumn();

            // Calculate total pages (handle 'all" case
            $totalPages = ($limit === PHP_INT_MAX) ? 1 :  ceil($total / $limit);

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

    public function update(array $data)
    {
        try {
            $id = $this->validateId($data['id']);
            $this->validateData($data);

            $query = "UPDATE " . self::TABLE_NAME . "
                 SET title = :title,
                     image = :image,
                     release_at = :release_at,
                     summary = :summary
                 WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            // Sanitize and bind all parameters
            foreach (['title', 'image', 'release_at', 'summary'] as $field) {
                $value = isset($data[$field]) ?
                    htmlspecialchars(strip_tags($data[$field])) : null;
                $stmt->bindValue(":{$field}", $value);
            }
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);

            $result = $stmt->execute();

            if (!$result) {
                throw new RuntimeException(self::ERROR_MESSAGES['update_failed']);
            }

            return true;

        } catch (PDOException $e) {
            $this->logError('Update failed: ' . $e->getMessage());
            throw new RuntimeException(self::ERROR_MESSAGES['update_failed']);
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
