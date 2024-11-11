<?php
require_once '../config/database.php';
require_once '../models/Member.php';

class CSVDownloader {
    private $db;
    private $member;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->member = new Member($this->db);
    }

    public function download() {
        try {
            $members = $this->member->getAll();
            
            // Set headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="avengers_members.csv"');
            
            // Create output stream
            $output = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Add headers
            fputcsv($output, ['id', 'title', 'image', 'release_at', 'summary'], ';');
            
            // Add data
            foreach ($members as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['title'],
                    $row['image'],
                    $row['release_at'],
                    $row['summary']
                ], ';');
            }
            
            fclose($output);
            exit();

        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $downloader = new CSVDownloader();
    $downloader->download();
}