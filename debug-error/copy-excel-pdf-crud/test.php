<?php
require_once 'connect-s.php';
try {
    $result = $connect->query("SELECT 1");
    echo "Koneksi database berhasil!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>