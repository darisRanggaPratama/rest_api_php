<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Prevent any output buffering
if (ob_get_level()) ob_end_clean();

$rootPath = dirname(dirname(__FILE__));
require_once $rootPath . '/config/database.php';

try {
    // Get data from database
    $stmt = $pdo->query("SELECT title, image, release_at, summary FROM members");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($results)) {
        throw new Exception("No data found in members table");
    }

    // Disable cache
    header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    header('Pragma: no-cache');

    // Set response headers for CSV download
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="avengers_members_' . date('Y-m-d_His') . '.csv"');

    // Open output stream directly
    $f = fopen('php://output', 'w');

    // Write UTF-8 BOM
    fwrite($f, "\xEF\xBB\xBF");

    // Write headers
    $headers = ['Title', 'Image', 'Release Date', 'Summary'];
    fputcsv($f, $headers, ';', '"', "\\");

    // Write data
    foreach ($results as $row) {
        // Format date
        if (isset($row['release_at'])) {
            $row['release_at'] = date('Y-m-d', strtotime($row['release_at']));
        }

        // Escape any existing semicolons in the data
        foreach ($row as &$field) {
            // Replace semicolons with commas in the actual data
            $field = str_replace(';', ',', $field);
            // Ensure proper encoding
            $field = mb_convert_encoding($field, 'UTF-8', 'auto');
        }

        // Write the row
        fputcsv($f, array_values($row), ';', '"', "\\");
    }

    // Close the file
    fclose($f);
    exit();

} catch (Exception $e) {
    header('Content-Type: text/plain; charset=UTF-8');
    die('Error generating CSV file: ' . $e->getMessage());
}