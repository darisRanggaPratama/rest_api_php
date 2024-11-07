<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'constants.php';

$host = 'localhost';
$dbname = 'avengers';
$username = 'rangga';
$password = 'rangga';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>