<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'check-db.php';
require_once 'Member.php';

class CSVUploader {
    private $db;
    private $member;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->member = Member::getInstance($this->db);
    }

    public function upload() {
        try {
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No file uploaded or upload error');
            }

            $file = $_FILES['csv_file']['tmp_name'];
            if (!is_uploaded_file($file)) {
                throw new Exception('Invalid file upload');
            }

            $handle = fopen($file, 'r');
            if (!$handle) {
                throw new Exception('Could not open file');
            }

            // Skip header row
            fgetcsv($handle, 0, ';');

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            while (($data = fgetcsv($handle, 0, ';')) !== FALSE) {
                if (count($data) != 4) {
                    $errorCount++;
                    continue;
                }

                list($title, $image, $release_at, $summary) = $data;

                // Format date
                $release_at = $this->formatDate($release_at);
                if (!$release_at) {
                    $errorCount++;
                    $errors[] = "Invalid date format for title: $title";
                    continue;
                }

                if ($this->member->create($title, $image, $release_at, $summary)) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = "Failed to insert record for title: $title";
                }
            }

            fclose($handle);

            return [
                'success' => true,
                'message' => "Upload completed. Success: $successCount, Errors: $errorCount",
                'errors' => $errors
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function formatDate($date) {
        // Try YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        // Try DD/MM/YYYY
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
            return "$matches[3]-$matches[2]-$matches[1]";
        }

        // Try MM/DD/YYYY
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
            return "$matches[3]-$matches[1]-$matches[2]";
        }

        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploader = new CSVUploader();
    $result = $uploader->upload();
    echo json_encode($result);
}