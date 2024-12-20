<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Include database connection
    require_once 'connects.php';

    // Cek koneksi
    if (!isset($connect)) {
        throw new Exception("Koneksi database tidak tersedia");
    }

    // Debug: Cek apakah tabel exists
    $tables = $connect->query("SHOW TABLES LIKE 'members'")->fetchAll();
    if (empty($tables)) {
        throw new Exception("Tabel 'members' tidak ditemukan");
    }

    // Debug: Tampilkan struktur tabel
    $columns = $connect->query("SHOW COLUMNS FROM members")->fetchAll();
    error_log("Table structure: " . print_r($columns, true));

    // Query dengan error handling
    $stmt = $connect->prepare("SELECT * FROM members");
    if (!$stmt) {
        throw new Exception("Gagal membuat prepared statement");
    }

    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: Log jumlah data
    error_log("Number of records found: " . count($result));

    // Tampilkan response
    echo json_encode([
        'draw' => isset($_GET['draw']) ? intval($_GET['draw']) : 1,
        'recordsTotal' => count($result),
        'recordsFiltered' => count($result),
        'data' => $result,
        'debug' => [
            'table_exists' => !empty($tables),
            'column_count' => count($columns),
            'row_count' => count($result)
        ]
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    error_log("PDO Error in data.php: " . $e->getMessage());
    echo json_encode([
        'draw' => 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage(),
        'debug' => [
            'error_type' => 'PDOException',
            'error_code' => $e->getCode(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine()
        ]
    ]);
} catch (Exception $e) {
    error_log("General Error in data.php: " . $e->getMessage());
    echo json_encode([
        'draw' => 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage(),
        'debug' => [
            'error_type' => 'Exception',
            'error_code' => $e->getCode(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine()
        ]
    ]);
}
?>