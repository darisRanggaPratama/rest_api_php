<?php
require_once 'config/database.php';
require_once 'models/Member.php';

class MemberController {
    private $member;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->member = new Member($db);
    }

    public function index() {
        include 'views/members/index.php';
    }

    public function getData() {
        $result = $this->member->getAll();
        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
        echo json_encode(['data' => $data]);
    }

    public function importCSV() {
        if ($_FILES['csv_file']) {
            $file = $_FILES['csv_file']['tmp_name'];
            $csv_data = array_map(function($line) {
                return str_getcsv($line, isset($_POST['delimiter']) ? $_POST['delimiter'] : ',');
            }, file($file));
            array_shift($csv_data); // Remove header row

            if ($this->member->importCSV($csv_data)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Import failed']);
            }
        }
    }
}
?>