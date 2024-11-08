<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="avengers_members_' . date('Y-m-d_His') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Create file pointer connected to PHP output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM to fix CSV encoding in Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Get search parameter if exists
$search = isset($_GET['search']) ? $_GET['search'] : '';

try {
    // Get members data
    $members = getAllMembers($conn, $search);

    // Set column headers
    $headers = ['ID', 'Title', 'Release Date', 'Summary', 'Image URL'];
    fputcsv($output, $headers, ';');

    // Output each row of data
    foreach ($members as $member) {
        $row = [
            $member['id'],
            $member['title'],
            $member['release_at'],
            $member['summary'],
            $member['image']
        ];

        // Clean the data to prevent Excel formula injection
        $row = array_map(function($field) {
            $field = str_replace(['+', '=', '@', "\r", "\n"], ' ', $field);
            // Add single quote to prevent number format issues
            if (is_numeric($field) && strlen($field) > 10) {
                $field = "'" . $field;
            }
            return $field;
        }, $row);

        fputcsv($output, $row, ';');
    }

    // Close the file pointer
    fclose($output);

} catch (Exception $e) {
    // If error occurs, return error message
    header('Content-Type: text/plain');
    echo 'Error exporting data: ' . $e->getMessage();
}

exit();
?>