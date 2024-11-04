<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

function parseDate($dateStr) {
    if (empty($dateStr)) {
        return null;
    }

    // Remove any leading/trailing whitespace
    $dateStr = trim($dateStr);

    // Try YYYY-MM-DD format
    $date = DateTime::createFromFormat('Y-m-d', $dateStr);
    if ($date && $date->format('Y-m-d') === $dateStr) {
        return $dateStr;
    }

    // Try DD/MM/YYYY format
    $date = DateTime::createFromFormat('d/m/Y', $dateStr);
    if ($date) {
        return $date->format('Y-m-d');
    }

    // Try MM/DD/YYYY format
    $date = DateTime::createFromFormat('m/d/Y', $dateStr);
    if ($date) {
        return $date->format('Y-m-d');
    }

    return null;
}

try {
    // Include database connection
    require_once 'connects.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred');
    }

    $file = $_FILES['csvFile']['tmp_name'];

    // Verify file is CSV
    $mimeType = mime_content_type($file);
    if ($mimeType !== 'text/csv' && $mimeType !== 'text/plain') {
        throw new Exception('Invalid file type. Please upload a CSV file');
    }

    // Open file
    $handle = fopen($file, 'r');
    if ($handle === false) {
        throw new Exception('Failed to open file');
    }

    // Start transaction
    $connect->beginTransaction();

    // Skip header row
    fgetcsv($handle, 0, ';');

    // Counter for successful imports
    $importCount = 0;
    $skippedCount = 0;
    $errors = [];

    // Prepare insert statement
    $stmt = $connect->prepare("INSERT INTO members (title, image, release_at, summary) VALUES (?, ?, ?, ?)");

    // Read and import data
    $rowNumber = 1; // Start from 1 as we already skipped header
    while (($data = fgetcsv($handle, 0, ';')) !== false) {
        $rowNumber++;

        // Validate data
        if (count($data) !== 4) {
            $errors[] = "Row $rowNumber: Invalid number of columns";
            $skippedCount++;
            continue;
        }

        // Clean and validate data
        $title = trim($data[0]);
        $image = trim($data[1]);
        $release_at = parseDate(trim($data[2]));
        $summary = trim($data[3]);

        // Skip if required fields are empty
        if (empty($title) || empty($summary)) {
            $errors[] = "Row $rowNumber: Missing required fields (title or summary)";
            $skippedCount++;
            continue;
        }

        // Skip if date is invalid
        if (!empty($data[2]) && $release_at === null) {
            $errors[] = "Row $rowNumber: Invalid date format for '{$data[2]}'";
            $skippedCount++;
            continue;
        }

        try {
            // Execute insert
            $stmt->execute([$title, $image, $release_at, $summary]);
            $importCount++;
        } catch (PDOException $e) {
            $errors[] = "Row $rowNumber: Database error - " . $e->getMessage();
            $skippedCount++;
            continue;
        }
    }

    // Close file
    fclose($handle);

    // If no successful imports but we have errors, rollback and report errors
    if ($importCount === 0 && !empty($errors)) {
        $connect->rollBack();
        throw new Exception("Import failed. No records were imported.\n\n" . implode("\n", $errors));
    }

    // Commit transaction
    $connect->commit();

    $response = [
        'status' => 'success',
        'message' => "Successfully imported $importCount records." .
            ($skippedCount > 0 ? " Skipped $skippedCount records." : ""),
        'importCount' => $importCount,
        'skippedCount' => $skippedCount
    ];

    // Add warnings if there were any skipped records
    if (!empty($errors)) {
        $response['warnings'] = $errors;
    }

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Rollback transaction if active
    if (isset($connect) && $connect->inTransaction()) {
        $connect->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}