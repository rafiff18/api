<?php

require_once "../database/Database.php";
require_once "../controllers/LikeController.php";

// Membuat instance dari kelas Database
$database = new Database();
$conn = $database->getConnection(); 

// Membuat instance dari LikeController
$controller = new LikeController($conn);

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case "GET":
        if (!empty($_GET["id"])) {
            $id = intval($_GET["id"]);
            $controller->getLikeById($id);
        } else {
            $controller->getAllLikes();
        }
        break;
    case "POST":
        $controller->createLike();
        break;
    case "PUT":
        if (!empty($_GET['id'])) {
            $id = intval($_GET["id"]);
            parse_str(file_get_contents("php://input"), $_PUT);
            $controller->updateLike($id);
        } else {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(array('message' => 'ID is required for PUT request'));
        }
        break;
    case "DELETE":
        $id = intval($_GET["id"]);
        $controller->deleteLike($id);
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}
?>
