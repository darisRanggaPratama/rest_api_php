<?php
require_once 'controllers/MemberController.php';

$controller = new MemberController();

$action = isset($_GET['action']) ? $_GET['action'] : 'index';

switch($action) {
    case 'getData':
        $controller->getData();
        break;
    case 'importCSV':
        $controller->importCSV();
        break;
    default:
        $controller->index();
        break;
}
?>
