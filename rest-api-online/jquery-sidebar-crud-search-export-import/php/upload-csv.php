<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootPath = dirname(dirname(__FILE__));
require_once $rootPath . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    $tmpName = $file['tmp_name'];
    $handle = fopen($tmpName, 'r');
    $i = 0;

    while (($data = fgetcsv($handle, 1000, ';')) !== false) {
        if ($i > 0) { // Skip header row
            $title = $data[0];
            $image = $data[1];
            $release_at = $data[2];
            $summary = $data[3];

            // Attempt to parse the date in different formats
            $releaseDate = null;
            $dateFormats = ['d/m/Y', 'm/d/Y', 'Y-m-d'];
            foreach ($dateFormats as $format) {
                $releaseDate = DateTime::createFromFormat($format, $release_at);
                if ($releaseDate !== false) {
                    break;
                }
            }

            if ($releaseDate === false) {
                echo json_encode(['success' => false, 'message' => 'Invalid date format for "' . $release_at . '"']);
                fclose($handle);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO members (title, image, release_at, summary) VALUES (:title, :image, :release_at, :summary)");
            $stmt->execute([
                'title' => $title,
                'image' => $image,
                'release_at' => $releaseDate->format('Y-m-d'),
                'summary' => $summary
            ]);
        }
        $i++;
    }

    fclose($handle);
    echo json_encode(['success' => true, 'message' => 'CSV uploaded successfully']);
}
?>