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

            // Remove header row and normalize it
            $header = array_shift($csv);
            $header = array_map(function($column) {
                // Convert to lowercase and remove special characters
                $column = strtolower(trim($column));
                $column = preg_replace('/[^a-z0-9_]/', '', $column);
                return $column;
            }, $header);

            // Map common variations of column names
            $header_mapping = [
                'title' => ['title', 'name', 'membertitle', 'membername'],
                'image' => ['image', 'img', 'picture', 'photo', 'imageurl'],
                'release_at' => ['release_at', 'releasedate', 'date', 'release'],
                'summary' => ['summary', 'description', 'desc', 'content']
            ];

            // Find column indexes for required fields
            $column_indexes = [];
            foreach($header_mapping as $required_column => $variations) {
                $found = false;
                foreach($variations as $variation) {
                    $index = array_search($variation, $header);
                    if($index !== false) {
                        $column_indexes[$required_column] = $index;
                        $found = true;
                        break;
                    }
                }
                if(!$found) {
                    throw new Exception("Required column '$required_column' not found in CSV. Acceptable headers: " . implode(', ', $variations));
                }
            }

            $stmt = $pdo->prepare("INSERT INTO members (title, image, release_at, summary) VALUES (?, ?, ?, ?)");

            foreach($csv as $row_index => $row) {
                // Skip empty rows
                if(empty(array_filter($row))) {
                    continue;
                }

                // Trim whitespace from all fields
                $row = array_map('trim', $row);

                // Get values using mapped indexes
                $title = $row[$column_indexes['title']] ?? '';
                $image = $row[$column_indexes['image']] ?? '';
                $release_at = $row[$column_indexes['release_at']] ?? '';
                $summary = $row[$column_indexes['summary']] ?? '';

                // Validate title length
                if(strlen($title) > 255) {
                    throw new Exception("Row " . ($row_index + 2) . ": Title exceeds 255 characters");
                }

                // Parse and validate date
                $date = trim($release_at);
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
                    throw new Exception("Row " . ($row_index + 2) . ": Invalid date format in '$date'. Use DD/MM/YYYY, MM/DD/YYYY, or YYYY-MM-DD");
                }

                $stmt->execute([
                    $title,
                    $image,
                    $parsed_date->format('Y-m-d'),
                    $summary
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