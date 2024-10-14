<?php

require_once "../database/Database.php";
require_once "../controllers/CommentController.php";

// Membuat instance dari kelas Database
$database = new Database();
$conn = $database->getConnection(); 

// Membuat instance dari CommentController
$controller = new CommentController($conn);

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case "GET":
        if (!empty($_GET["id"])) {
            $id = intval($_GET["id"]);
            $controller->getCommentById($id);
        } else {
            $controller->getAllComments();
        }
        break;
    case "POST":
        $controller->createComment();
        break;
    case "PUT":
        if (!empty($_GET['id'])) {
            $id = intval($_GET["id"]);
            parse_str(file_get_contents("php://input"), $_PUT);
            $controller->updateComment($id);
        } else {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(array('message' => 'ID is required for PUT request'));
        }
        break;
    case "DELETE":
        if (!empty($_GET["id"])) {
            $id = intval($_GET["id"]);
            $controller->deleteComment($id);
        } else {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(array('message' => 'ID is required for DELETE request'));
        }
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        echo json_encode(array('message' => 'Method Not Allowed'));
        break;
}
?>
