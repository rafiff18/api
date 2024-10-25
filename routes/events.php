<?php

require_once "../helpers/HeaderAccessControl.php";
require_once "../database/Database.php";
require_once "../controllers/EventController.php";
// Membuat instance dari kelas Database
$database = new Database();
$conn = $database->getConnection(); 

// Membuat instance dari EventController
$controller = new EventController($conn);

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case "GET":
        if (!empty($_GET["id"])) {
            // Ambil event berdasarkan ID
            $id = intval($_GET["id"]);
            $controller->getEventById($id);
        } elseif (!empty($_GET["keyword"])) {
            // Cari event berdasarkan keyword
            $keyword = $_GET["keyword"];
            $controller->searchEvent($keyword);
        } elseif (!empty($_GET["filter"])) {
            // Filter event berdasarkan tanggal
            $filter = $_GET["filter"];
            $controller->filterEventsByDate($filter);
        } else {
            // Ambil semua event
            $controller->getAllEvent();
        }
        break;
    
    case "POST":
        $controller->createEvent();
        break;
    case "PUT":
        if (!empty($_GET['id'])) {
            $id = intval($_GET["id"]);
            parse_str(file_get_contents("php://input"), $_PUT);
            $controller->updateEvent($id);
        } else {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(array('message' => 'ID is required for PUT request'));
        }
        break;
    case "DELETE":
        $id = intval($_GET["id"]);
        $controller->deleteEvent($id);
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}
?>
