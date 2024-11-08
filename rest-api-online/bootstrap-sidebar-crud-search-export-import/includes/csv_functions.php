<?php
function parseDate($dateString) {
    // Trim any whitespace
    $dateString = trim($dateString);

    $formats = [
        'd/m/Y', // DD/MM/YYYY
        'm/d/Y', // MM/DD/YYYY
        'Y-m-d'  // YYYY-MM-DD
    ];

    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $dateString);
        if ($date && $date->format($format) === $dateString) {
            return $date->format('Y-m-d'); // Convert to MySQL format
        }
    }
    return false;
}

function validateCsvRow($row) {
    $errors = [];

    // Check required fields
    if (empty(trim($row['title']))) {
        $errors[] = "Title is required";
    }

    if (empty(trim($row['release_at']))) {
        $errors[] = "Release date is required";
    } else {
        $validDate = parseDate($row['release_at']);
        if (!$validDate) {
            $errors[] = "Invalid date format for: " . $row['release_at'];
        }
    }

    if (empty(trim($row['summary']))) {
        $errors[] = "Summary is required";
    }

    return $errors;
}

function processCsvImport($conn, $file) {
    $result = [
        'success' => 0,
        'errors' => 0,
        'messages' => [],
        'debug' => []
    ];

    try {
        if (($handle = fopen($file, "r")) !== FALSE) {
            // Read the first few bytes to check for BOM
            $bom = fread($handle, 3);
            if ($bom === chr(0xEF) . chr(0xBB) . chr(0xBF)) {
                // BOM found, file pointer is already past it
                $result['debug'][] = "BOM detected and skipped";
            } else {
                // No BOM found, reset file pointer
                rewind($handle);
            }

            // Read and validate header row
            $header = fgetcsv($handle, 1000, ";");
            if ($header === false) {
                throw new Exception("Could not read CSV header");
            }

            // Clean and normalize header names
            $header = array_map(function($column) {
                // Remove BOM if present at the start of first column
                $column = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $column);
                return strtolower(trim($column));
            }, $header);

            // Debug header values
            $result['debug'][] = "Raw headers: " . print_r($header, true);
            $result['debug'][] = "Header count: " . count($header);

            // Validate required columns exist
            $requiredColumns = ['title', 'image', 'release_at', 'summary'];
            $missingColumns = array_diff($requiredColumns, $header);

            if (!empty($missingColumns)) {
                throw new Exception("Missing required columns: " . implode(", ", $missingColumns));
            }

            // Rest of your existing code...
            $conn->beginTransaction();

            $insertStmt = $conn->prepare("
                INSERT INTO members (title, image, release_at, summary) 
                VALUES (:title, :image, :release_at, :summary)
            ");

            $rowNumber = 1;
            while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $rowNumber++;

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Create associative array from row data
                if (count($header) !== count($row)) {
                    $result['errors']++;
                    $result['messages'][] = "Error on row {$rowNumber}: Column count mismatch. Expected: " . count($header) . ", Got: " . count($row);
                    continue;
                }

                $data = array_combine($header, $row);

                // Trim and clean all values
                $data = array_map(function($value) {
                    return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $value));
                }, $data);

                // Validate row data
                $errors = validateCsvRow($data);

                if (empty($errors)) {
                    // Convert date format
                    $releaseDate = parseDate($data['release_at']);

                    try {
                        $insertStmt->execute([
                            ':title' => $data['title'],
                            ':image' => $data['image'],
                            ':release_at' => $releaseDate,
                            ':summary' => $data['summary']
                        ]);
                        $result['success']++;
                    } catch (PDOException $e) {
                        $result['errors']++;
                        $result['messages'][] = "Database error on row {$rowNumber}: " . $e->getMessage();
                    }
                } else {
                    $result['errors']++;
                    $result['messages'][] = "Error on row {$rowNumber}: " . implode(", ", $errors);
                }
            }

            if ($result['success'] > 0 && $result['errors'] === 0) {
                $conn->commit();
                $result['messages'][] = "Successfully imported {$result['success']} records.";
            } else {
                $conn->rollBack();
                if ($result['errors'] > 0) {
                    $result['messages'][] = "Import failed with {$result['errors']} errors. All changes were rolled back.";
                } else {
                    $result['messages'][] = "No valid records found to import.";
                }
                $result['success'] = 0;
            }

            fclose($handle);
        } else {
            throw new Exception("Could not open CSV file");
        }
    } catch (Exception $e) {
        $result['errors']++;
        $result['messages'][] = "Error: " . $e->getMessage();
        $result['debug'][] = "Exception: " . $e->getMessage();

        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
    }

    return $result;
}
?>