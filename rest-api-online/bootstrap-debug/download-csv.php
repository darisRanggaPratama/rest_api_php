<?php
require_once 'check-db.php';
require_once 'Member.php';

class CSVDownloader {
    private $db;
    private $member;
    private const MAX_EXECUTION_TIME = 300; // 5 minutes
    private const MEMORY_LIMIT = '256M';
    private const CHUNK_SIZE = 1000; // Process records in chunks
    private const DEFAULT_FILENAME = 'avengers_members.csv';
    private const CSV_HEADERS = ['id', 'title', 'image', 'release_at', 'summary'];

    public function __construct() {
        try {
            // Set execution time and memory limits for large datasets
            set_time_limit(self::MAX_EXECUTION_TIME);
            ini_set('memory_limit', self::MEMORY_LIMIT);

            $database = Database::getInstance();
            $this->db = $database->getConnection();
            $this->member = Member::getInstance($this->db);

            // Verify database connection
            if (!$this->db || !$this->member) {
                throw new RuntimeException('Failed to initialize database connection');
            }
        } catch (Exception $e) {
            $this->handleError('Initialization error: ' . $e->getMessage());
        }
    }

    public function download() {
        $output = null;

        try {
            // Validate request method and authentication if needed
            $this->validateRequest();

            // Start output buffering to prevent unwanted output
            ob_start();

            // Create temporary file handle
            $output = $this->createTempFile();

            // Process and write data
            $this->processData($output);

            // Clean and close the output
            $this->finalizeDownload($output);

        } catch (Exception $e) {
            $this->handleError($e->getMessage(), $output);
        }
    }

    private function validateRequest() {
        // Validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new RuntimeException('Invalid request method');
        }

        // Add authentication check if needed
        // if (!isset($_SESSION['user_id'])) {
        //     throw new RuntimeException('Unauthorized access');
        // }

        // Validate content type and headers
        if (headers_sent()) {
            throw new RuntimeException('Headers already sent');
        }
    }

    private function createTempFile() {
        $output = fopen('php://temp', 'w+');
        if ($output === false) {
            throw new RuntimeException('Failed to create temporary file');
        }

        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Write headers
        if (fputcsv($output, self::CSV_HEADERS, ';') === false) {
            throw new RuntimeException('Failed to write CSV headers');
        }

        return $output;
    }

    private function processData($output) {
        $offset = 0;

        while (true) {
            // Get data in chunks
            $members = $this->member->getAll('', ($offset / self::CHUNK_SIZE) + 1, self::CHUNK_SIZE);

            if (empty($members['data'])) {
                break;
            }

            foreach ($members['data'] as $row) {
                // Validate and sanitize data
                $processedRow = $this->sanitizeRow($row);

                // Write to CSV
                if (fputcsv($output, $processedRow, ';') === false) {
                    throw new RuntimeException('Failed to write CSV data');
                }
            }

            $offset += self::CHUNK_SIZE;

            // Break if we've processed all records
            if ($offset >= $members['total']) {
                break;
            }
        }
    }

    private function sanitizeRow($row) {
        return [
            'id' => $this->sanitizeField($row['id'] ?? ''),
            'title' => $this->sanitizeField($row['title'] ?? ''),
            'image' => $this->sanitizeField($row['image'] ?? ''),
            'release_at' => $this->sanitizeField($row['release_at'] ?? ''),
            'summary' => $this->sanitizeField($row['summary'] ?? '')
        ];
    }

    private function sanitizeField($value) {
        // Remove any potentially harmful characters
        $value = preg_replace('/[\x00-\x1F\x7F]/', '', (string)$value);
        // Prevent CSV injection
        if (in_array(substr($value, 0, 1), ['=', '+', '-', '@'])) {
            $value = "'" . $value;
        }
        return trim($value);
    }

    private function finalizeDownload($output) {
        // Get file size
        fseek($output, 0, SEEK_END);
        $size = ftell($output);
        rewind($output);

        // Validate file size
        if ($size === 0) {
            throw new RuntimeException('No data available for download');
        }

        // Clear any previous output
        if (ob_get_length()) {
            ob_clean();
        }

        // Set appropriate headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . self::DEFAULT_FILENAME . '"');
        header('Content-Length: ' . $size);
        header('Pragma: no-cache');
        header('Expires: 0');

        // Stream file contents
        fpassthru($output);
        fclose($output);
        exit();
    }

    private function handleError($message, $output = null) {
        // Close file handle if open
        if ($output) {
            fclose($output);
        }

        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Log error
        error_log('CSV Download Error: ' . $message);

        // Send error response
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to generate CSV file. Please try again later.',
            'error' => $message
        ]);
        exit();
    }
}

// Initialize and run download
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $downloader = new CSVDownloader();
        $downloader->download();
    } catch (Throwable $e) {
        // Catch any uncaught errors
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'An unexpected error occurred',
            'error' => $e->getMessage()
        ]);
    }
}