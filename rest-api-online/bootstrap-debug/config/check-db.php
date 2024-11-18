<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Database {
    // Konstanta untuk konfigurasi database
    private const CONFIG_FILE = __DIR__ . '/database.php';

    // Properties dengan visibility protected agar bisa diakses class turunan
    protected static $instance = null;
    protected $host;
    protected $database;
    protected $username;
    protected $password;
    protected $conn;
    protected $connected = false;

    // Constructor dengan parameter opsional
    protected function __construct($config = null) {
        if ($config) {
            $this->loadConfig($config);
        } else {
            $this->loadConfigFile();
        }

        // Validasi konfigurasi
        $this->validateConfig();
    }

    // Implementasi Singleton Pattern
    public static function getInstance($config = null) {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    // Metode untuk memuat konfigurasi dari file
    protected function loadConfigFile() {
        if (!file_exists(self::CONFIG_FILE)) {
            throw new Exception("Configuration file not found: " . self::CONFIG_FILE);
        }

        $config = require self::CONFIG_FILE;
        $this->loadConfig($config);
    }

    // Memuat konfigurasi dari array
    protected function loadConfig($config) {
        $this->host = $config['host'] ?? 'localhost';
        $this->database = $config['database'] ?? null;
        $this->username = $config['username'] ?? null;
        $this->password = $config['password'] ?? null;
    }

    // Validasi konfigurasi database
    protected function validateConfig() {
        $required = ['database', 'username', 'password'];
        foreach ($required as $field) {
            if (empty($this->{$field})) {
                throw new Exception("Missing required configuration: {$field}");
            }
        }
    }

    // Method utama untuk mendapatkan koneksi
    public function getConnection() {
        if ($this->connected && $this->conn instanceof PDO) {
            return $this->conn;
        }

        try {
            // Tambahkan opsi koneksi untuk keamanan dan performa
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            $dsn = "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4";

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            $this->connected = true;

            return $this->conn;

        } catch (PDOException $e) {
            // Log error dan throw exception yang lebih deskriptif
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please check your configuration and try again.");
        }
    }

    // Method untuk mengecek status koneksi
    public function isConnected() {
        return $this->connected && $this->conn instanceof PDO;
    }

    // Method untuk menutup koneksi
    public function closeConnection() {
        $this->conn = null;
        $this->connected = false;
    }

    // Mencegah cloning object
    private function __clone() {}

    // Mencegah unserialize
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    // Getter methods untuk mengakses properties dengan aman
    public function getHost() {
        return $this->host;
    }

    public function getDatabase() {
        return $this->database;
    }

    // Method untuk testing koneksi
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            return $conn->query('SELECT 1')->fetch() ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function dumpDie(...$vars)
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var); // Menampilkan informasi tentang variabel
            echo '</pre>';
        }
        die(); // Menghentikan eksekusi skrip
    }

    public function printDie(...$vars)
    {
        foreach ($vars as $var) {
            echo '<pre>';
            print_r($var); // Menampilkan informasi tentang variabel
            echo '</pre>';
        }
        die(); // Menghentikan eksekusi skrip

    }
}

?>