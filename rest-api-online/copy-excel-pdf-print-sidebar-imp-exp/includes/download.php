<?php
require_once '../config/database.php';

try {
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=avengers_data.csv');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for proper Excel handling
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add CSV header
    fputcsv($output, ['Title', 'Image', 'Release Date', 'Summary'], ';');
    
    // Fetch data from database
    $query = $connect->query("SELECT title, image, release_at, summary FROM members ORDER BY release_at");
    
    // Write data rows
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['title'],
            $row['image'],
            $row['release_at'],
            $row['summary']
        ], ';');
    }
    
    fclose($output);
    
} catch (PDOException $e) {
    // Log error and return error response
    error_log($e->getMessage());
    http_response_code(500);
    echo "Error generating CSV file";
}
?>