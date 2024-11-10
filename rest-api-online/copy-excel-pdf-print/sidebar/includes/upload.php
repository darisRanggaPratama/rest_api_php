<?php
require_once '../config/database.php';

function parseDate($date) {
    // Array of possible date formats
    $formats = [
        'Y-m-d',     // YYYY-MM-DD
        'd/m/Y',     // DD/MM/YYYY
        'm/d/Y'      // MM/DD/YYYY
    ];
    
    foreach ($formats as $format) {
        $dateObj = DateTime::createFromFormat($format, $date);
        if ($dateObj && $dateObj->format($format) == $date) {
            return $dateObj->format('Y-m-d'); // Convert to MySQL format
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvFile'])) {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $file = $_FILES['csvFile'];
        
        // Validate file type
        $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
        if ($fileType != 'csv') {
            throw new Exception('Please upload a CSV file');
        }

        // Read CSV file
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            throw new Exception('Error opening file');
        }

        // Begin transaction
        $connect->beginTransaction();
        
        // Prepare insert statement
        $stmt = $connect->prepare("INSERT INTO members (title, image, release_at, summary) VALUES (?, ?, ?, ?)");
        
        // Skip header row
        fgetcsv($handle, 0, ';');
        
        $rowCount = 0;
        $errors = [];

        while (($data = fgetcsv($handle, 0, ';')) !== FALSE) {
            $rowCount++;
            
            // Validate row data
            if (count($data) != 4) {
                $errors[] = "Row $rowCount: Invalid number of columns";
                continue;
            }
            
            // Parse and validate date
            $parsedDate = parseDate($data[2]);
            if (!$parsedDate) {
                $errors[] = "Row $rowCount: Invalid date format in '{$data[2]}'";
                continue;
            }
            
            // Try to insert the row
            try {
                $stmt->execute([
                    $data[0],        // title
                    $data[1],        // image
                    $parsedDate,     // release_at
                    $data[3]         // summary
                ]);
            } catch (PDOException $e) {
                $errors[] = "Row $rowCount: " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        // If there were any errors, rollback and return error messages
        if (!empty($errors)) {
            $connect->rollBack();
            throw new Exception("Upload completed with errors:\n" . implode("\n", $errors));
        }
        
        // If all went well, commit the transaction
        $connect->commit();
        $response = [
            'success' => true,
            'message' => "Successfully uploaded $rowCount rows"
        ];
        
    } catch (Exception $e) {
        if (isset($connect) && $connect->inTransaction()) {
            $connect->rollBack();
        }
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>