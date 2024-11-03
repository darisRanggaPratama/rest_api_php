<?php
require_once 'config.php';

$action = $_GET['action'] ?? '';

switch($action) {
    case 'list':
        $stmt = $pdo->query("SELECT * FROM members");
        echo json_encode($stmt->fetchAll());
        break;

    case 'create':
        try {
            $title = $_POST['title'] ?? '';
            $release_at = $_POST['release_at'] ?? '';
            $summary = $_POST['summary'] ?? '';

            if (strlen($title) > 255) {
                throw new Exception("Title exceeds maximum length of 255 characters");
            }

            // Handle image upload
            $image = '';
            if(isset($_FILES['image'])) {
                $target_dir = "uploads/";
                $image = $target_dir . basename($_FILES["image"]["name"]);
                move_uploaded_file($_FILES["image"]["tmp_name"], $image);
            }

            $stmt = $pdo->prepare("INSERT INTO members (title, image, release_at, summary) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $image, $release_at, $summary]);
            echo "success";
        } catch(Exception $e) {
            http_response_code(500);
            echo $e->getMessage();
        }
        break;

    case 'import':
        try {
            if(!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("No CSV file uploaded or upload error");
            }

            $file = $_FILES['csv']['tmp_name'];
            if(!is_readable($file)) {
                throw new Exception("Unable to read CSV file");
            }

            // Read first line to detect delimiter
            $firstLine = fgets(fopen($file, 'r'));
            $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';

            // Read CSV file with detected delimiter
            $csv = array_map(function($line) use ($delimiter) {
                return str_getcsv($line, $delimiter);
            }, file($file));

            if(empty($csv)) {
                throw new Exception("Empty CSV file");
            }

            // Remove header row
            $header = array_shift($csv);

            // Validate header structure
            $required_columns = ['title', 'image', 'release_at', 'summary'];
            $header = array_map('trim', $header); // Trim whitespace from headers
            if(count(array_intersect($header, $required_columns)) !== count($required_columns)) {
                throw new Exception("CSV header must contain: title, image, release_at, summary");
            }

            $stmt = $pdo->prepare("INSERT INTO members (title, image, release_at, summary) VALUES (?, ?, ?, ?)");

            foreach($csv as $row_index => $row) {
                // Skip empty rows
                if(empty(array_filter($row))) {
                    continue;
                }

                // Trim whitespace from all fields
                $row = array_map('trim', $row);

                // Validate required fields
                if(!isset($row[0]) || !isset($row[2])) {
                    throw new Exception("Row " . ($row_index + 2) . ": Missing required fields");
                }

                // Validate title length
                if(strlen($row[0]) > 255) {
                    throw new Exception("Row " . ($row_index + 2) . ": Title exceeds 255 characters");
                }

                // Parse and validate date
                $date = trim($row[2]);
                $parsed_date = null;

                // Try different date formats
                $formats = [
                    'd/m/Y',
                    'm/d/Y',
                    'Y-m-d'
                ];

                foreach($formats as $format) {
                    $parsed_date = DateTime::createFromFormat($format, $date);
                    if($parsed_date !== false) {
                        break;
                    }
                }

                if($parsed_date === false) {
                    throw new Exception("Row " . ($row_index + 2) . ": Invalid date format. Use DD/MM/YYYY, MM/DD/YYYY, or YYYY-MM-DD");
                }

                $stmt->execute([
                    $row[0], // title
                    $row[1] ?? '', // image
                    $parsed_date->format('Y-m-d'), // formatted release_at
                    $row[3] ?? ''  // summary
                ]);
            }
            echo "success";
        } catch(Exception $e) {
            http_response_code(500);
            echo $e->getMessage();
        }
        break;

    case 'update':
        try {
            $id = $_POST['id'];
            $title = $_POST['title'];
            $release_at = $_POST['release_at'];
            $summary = $_POST['summary'];

            if (strlen($title) > 255) {
                throw new Exception("Title exceeds maximum length of 255 characters");
            }

            $image_sql = "";
            $params = [$title, $release_at, $summary, $id];

            if(isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
                $target_dir = "uploads/";
                $image = $target_dir . basename($_FILES["image"]["name"]);
                move_uploaded_file($_FILES["image"]["tmp_name"], $image);
                $image_sql = ", image = ?";
                array_splice($params, -1, 0, [$image]);
            }

            $stmt = $pdo->prepare("UPDATE members SET title = ?, release_at = ?, summary = ?" . $image_sql . " WHERE id = ?");
            $stmt->execute($params);
            echo "success";
        } catch(Exception $e) {
            http_response_code(500);
            echo $e->getMessage();
        }
        break;

    case 'delete':
        try {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
            $stmt->execute([$id]);
            echo "success";
        } catch(Exception $e) {
            http_response_code(500);
            echo $e->getMessage();
        }
        break;

    case 'get':
        $id = $_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch());
        break;
}
?>