<?php
// Nonaktifkan output error langsung ke browser
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Fungsi untuk mencatat error ke log
function logError($message) {
    error_log($message);
}

// Set header JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Include database connection
    require_once 'connects.php';

    // Cek koneksi
    if (!isset($connect)) {
        throw new Exception("Koneksi database tidak tersedia");
    }

    // Cek apakah tabel exists
    $tables = $connect->query("SHOW TABLES LIKE 'members'")->fetchAll();
    if (empty($tables)) {
        // Jika tabel tidak ada, buat tabel
        $connect->exec("CREATE TABLE IF NOT EXISTS members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            image VARCHAR(1024),
            release_at DATE,
            summary TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");

        logError("Table 'members' created successfully");
    }

    // Query dengan error handling
    $stmt = $connect->prepare("SELECT * FROM members ORDER BY id DESC");
    if (!$stmt) {
        throw new Exception("Gagal membuat prepared statement");
    }

    if (!$stmt->execute()) {
        throw new Exception("Gagal mengeksekusi query");
    }

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log jumlah data
    logError("Number of records found: " . count($result));

    // Tampilkan response JSON
    echo json_encode([
        'draw' => isset($_GET['draw']) ? intval($_GET['draw']) : 1,
        'recordsTotal' => count($result),
        'recordsFiltered' => count($result),
        'data' => $result,
        'debug' => [
            'table_exists' => !empty($tables),
            'column_count' => count($connect->query("SHOW COLUMNS FROM members")->fetchAll()),
            'row_count' => count($result)
        ]
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    logError("PDO Error in data.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'draw' => 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Database error occurred',
        'debug' => [
            'error_type' => 'PDOException',
            'error_code' => $e->getCode(),
            'error_message' => $e->getMessage()
        ]
    ]);
    exit;
} catch (Exception $e) {
    logError("General Error in data.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'draw' => 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'An error occurred',
        'debug' => [
            'error_type' => 'Exception',
            'error_code' => $e->getCode(),
            'error_message' => $e->getMessage()
        ]
    ]);
    exit;
}