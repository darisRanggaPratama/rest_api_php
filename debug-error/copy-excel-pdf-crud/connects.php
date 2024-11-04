<?php
// Nonaktifkan display error ke browser
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Fungsi untuk logging error
function logDatabaseError($message) {
    error_log("Database Error: " . $message);
}

try {
    // Konfigurasi database
    $host = 'localhost';
    $dbname = 'crud_db'; // Sesuaikan dengan nama database Anda
    $username = 'root'; // Sesuaikan dengan username database Anda
    $password = ''; // Sesuaikan dengan password database Anda
    $charset = 'utf8mb4';

    // Set DSN dengan charset yang benar
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

    // Set opsi PDO
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];

    // Buat koneksi PDO
    $connect = new PDO($dsn, $username, $password, $options);

    // Log sukses
    error_log("Database connection established successfully");

} catch (PDOException $e) {
    // Log error
    logDatabaseError($e->getMessage());

    // Response error dalam format JSON
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed',
        'debug' => [
            'error_type' => 'PDOException',
            'error_message' => $e->getMessage()
        ]
    ]);
    exit;
}