<?php
// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Konfigurasi Database
$host = 'localhost';
$username = 'rangga';
$password = 'rangga';
$database = 'avengers';

try {
    // Membuat koneksi PDO dengan opsi tambahan
    $connect = new PDO(
        "mysql:host=$host;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        )
    );

} catch(PDOException $e) {
    // Log error ke file
    error_log("Database Connection Error: " . $e->getMessage());
    die("Koneksi database gagal: " . $e->getMessage());
}
?>