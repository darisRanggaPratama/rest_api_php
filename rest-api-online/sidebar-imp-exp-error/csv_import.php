<?php
class CSVImporter {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function import($file) {
        $handle = fopen($file, "r");
        $header = fgetcsv($handle); // Skip header row

        $stmt = $this->pdo->prepare("
            INSERT INTO members (title, image, release_at, summary) 
            VALUES (?, ?, ?, ?)
        ");

        while (($data = fgetcsv($handle)) !== FALSE) {
            $date = $this->parseDate($data[2]); // Assuming date is in third column

            $stmt->execute([
                $data[0], // title
                $data[1], // image
                $date,    // release_at
                $data[3]  // summary
            ]);
        }

        fclose($handle);
    }

    private function parseDate($date) {
        // Remove any potential whitespace
        $date = trim($date);

        // Try different date formats
        $formats = [
            'd/m/Y' => '/^\d{2}\/\d{2}\/\d{4}$/',
            'm/d/Y' => '/^\d{2}\/\d{2}\/\d{4}$/',
            'Y-m-d' => '/^\d{4}-\d{2}-\d{2}$/'
        ];

        foreach ($formats as $format => $pattern) {
            if (preg_match($pattern, $date)) {
                $dateObj = DateTime::createFromFormat($format, $date);
                if ($dateObj !== false) {
                    return $dateObj->format('Y-m-d');
                }
            }
        }

        // If no format matches, throw exception
        throw new Exception("Invalid date format: $date");
    }

    public function validateCSV($file) {
        $handle = fopen($file, "r");
        $header = fgetcsv($handle);

        // Validate header structure
        $requiredColumns = ['title', 'image', 'release_at', 'summary'];
        if (count(array_intersect($header, $requiredColumns)) !== count($requiredColumns)) {
            fclose($handle);
            throw new Exception("CSV header does not match required structure");
        }

        $rowNumber = 1;
        while (($data = fgetcsv($handle)) !== FALSE) {
            $rowNumber++;

            // Validate required fields
            if (empty($data[0]) || empty($data[2])) {
                throw new Exception("Row $rowNumber: Title and Release Date are required");
            }

            // Validate date format
            try {
                $this->parseDate($data[2]);
            } catch (Exception $e) {
                throw new Exception("Row $rowNumber: " . $e->getMessage());
            }
        }

        fclose($handle);
        return true;
    }
}
