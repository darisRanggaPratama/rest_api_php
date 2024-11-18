<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session untuk CSRF protection
session_start();

// Validasi CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die(json_encode(['error' => 'Invalid CSRF token']));
}

require_once '../config/check-db.php';
require_once '../models/Member.php';

try {
    // Validasi file
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred');
    }

    $file = $_FILES['csv_file'];

    // Validasi tipe file
    $mimeType = mime_content_type($file['tmp_name']);
    if (!in_array($mimeType, ['text/csv', 'text/plain', 'application/vnd.ms-excel'])) {
        throw new Exception('Invalid file type. Only CSV files are allowed.');
    }

    // Baca file CSV
    $handle = fopen($file['tmp_name'], 'r');
    if ($handle === false) {
        throw new Exception('Failed to open CSV file');
    }

    // Inisialisasi database dan member
    $database = Database::getInstance();
    $db = $database->getConnection();
    $member = Member::getInstance($db);

    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    $lineNumber = 0;

    // Skp baris pertama (header)
    fgetcsv($handle, 1000, ";");

    while (($data = fgetcsv($handle, 1000, ";")) !== false) {
        $lineNumber++;

        // Skip jika baris kosong
        if (count($data) !== 4) {
            $errors[] = "Line $lineNumber: Invalid number of columns";
            $errorCount++;
            continue;
        }

        // Parse data
        list($title, $image, $release_at, $summary) = array_map('trim', $data);

        // Validasi title
        if (empty($title)) {
            $errors[] = "Line $lineNumber: Title is required";
            $errorCount++;
            continue;
        }

        // Convert date format
        $parsedDate = false;
        $dateFormats = [
            'Y-m-d',
            'd/m/Y',
            'm/d/Y'
        ];

        foreach ($dateFormats as $format) {
            $date = DateTime::createFromFormat($format, $release_at);
            if ($date !== false) {
                $parsedDate = $date->format('Y-m-d');
                break;
            }
        }

        if ($parsedDate === false) {
            $errors[] = "Line $lineNumber: Invalid date format for '$release_at'";
            $errorCount++;
            continue;
        }

        try {
            // Create member record
            $member->create([
                'title' => $title,
                'image' => $image,
                'release_at' => $parsedDate,
                'summary' => $summary
            ]);
            $successCount++;
        } catch (Exception $e) {
            $errors[] = "Line $lineNumber: " . $e->getMessage();
            $errorCount++;
        }
    }

    fclose($handle);

    // Return response
    echo json_encode([
        'success' => true,
        'message' => "Import completed: $successCount records imported successfully, $errorCount failed",
        'details' => [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'errors' => $errors
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
