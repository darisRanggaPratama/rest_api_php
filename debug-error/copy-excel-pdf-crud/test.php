<?php
require_once 'connects.php';
try {
    $result = $connect->query("SELECT 1");
    echo "Koneksi database berhasil!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>