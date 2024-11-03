<?php
require_once '../config/Database.php';
require_once '../models/Member.php';

class MemberController {
    private $member;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->member = new Member($db);
    }

    public function getAllMembers() {
        $stmt = $this->member->getAll();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['data' => $members]);
    }

    public function createMember() {
        $this->member->title = $_POST['title'];
        $this->member->image = $_POST['image'];
        $this->member->release_at = $_POST['release_at'];
        $this->member->summary = $_POST['summary'];

        if($this->member->create()) {
            echo json_encode(['status' => 'success', 'message' => 'Member created successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create member']);
        }
    }

    public function updateMember() {
        $this->member->id = $_POST['id'];
        $this->member->title = $_POST['title'];
        $this->member->image = $_POST['image'];
        $this->member->release_at = $_POST['release_at'];
        $this->member->summary = $_POST['summary'];

        if($this->member->update()) {
            echo json_encode(['status' => 'success', 'message' => 'Member updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update member']);
        }
    }

    public function deleteMember() {
        $this->member->id = $_POST['id'];

        if($this->member->delete()) {
            echo json_encode(['status' => 'success', 'message' => 'Member deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete member']);
        }
    }
}

// Handling AJAX requests
if(isset($_POST['action'])) {
    $controller = new MemberController();

    switch($_POST['action']) {
        case 'getAll':
            $controller->getAllMembers();
            break;
        case 'create':
            $controller->createMember();
            break;
        case 'update':
            $controller->updateMember();
            break;
        case 'delete':
            $controller->deleteMember();
            break;
    }
}